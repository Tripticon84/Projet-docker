<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;


function createInvoice($date_emission,$date_echeance,$montant,$montant_tva,$montant_ht,$statut,$methode_paiement,$id_devis,$id_prestataire = null)
{
    $db = getDatabaseConnection();
    $sql = "INSERT INTO facture (date_emission, date_echeance, montant, montant_tva, montant_ht,statut, methode_paiement,id_devis,id_prestataire) VALUES (:date_emission, :date_echeance, :montant, :montant_tva, :montant_ht,
    :statut, :methode_paiement, :id_devis, :id_prestataire);";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'date_emission' => $date_emission,
        'date_echeance' => $date_echeance,
        'montant' => $montant,
        'montant_tva' => $montant_tva,
        'montant_ht' => $montant_ht,
        'statut' => $statut,
        'methode_paiement' => $methode_paiement,
        'id_devis' => $id_devis,
        'id_prestataire' => $id_prestataire
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}


function getAllInvoice( $id_prestataire = null,  $limit = null,  $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT facture_id, date_emission, date_echeance, montant, montant_tva, montant_ht, statut, methode_paiement, id_devis, id_prestataire FROM facture";
    $params = [];

    if (!empty($id_prestataire)) {
        $sql .= " WHERE id_prestataire LIKE :id_prestataire";
        $params['id_prestataire'] = "%" . $id_prestataire . "%";
    }

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);  // Seuls les paramètres username seront utilisés

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getAllInvoiceByState($state, $id_prestataire = "", int $limit = null, int $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT facture_id, date_emission, date_echeance, montant, montant_tva, montant_ht, statut, methode_paiement, id_devis, id_prestataire FROM facture WHERE statut = :statut";
    $params = ['statut' => $state];

    if (!empty($id_prestataire)) {
        $sql .= " AND id_prestataire LIKE :id_prestataire";
        $params['id_prestataire'] = "%" . $id_prestataire . "%";
    }

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);  // Seuls les paramètres username seront utilisés

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getInvoiceByProvider($providerId)
{
    $db = getDatabaseConnection();
    $sql = "SELECT f.facture_id, f.date_emission, f.date_echeance, f.montant, f.montant_tva, f.montant_ht,
           f.statut, f.methode_paiement, f.id_devis, f.id_prestataire,
           p.nom, p.prenom, p.email, p.type
           FROM facture f
           JOIN prestataire p ON f.id_prestataire = p.prestataire_id
           WHERE f.id_prestataire = :providerId";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['providerId' => $providerId]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getProviderByInvoice($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT p.prestataire_id, p.nom, p.prenom, p.email, p.type, p.tarif, p.date_debut_disponibilite, p.date_fin_disponibilite, p.description FROM prestataire p JOIN facture f ON p.prestataire_id = f.id_prestataire WHERE f.facture_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() == 0) {
        return null; // Aucun prestataire trouvé
    }
    if ($stmt->rowCount() > 1) {
        returnError(500, 'Multiple providers found for the same invoice');
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCompanyByInvoice($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT c.societe_id, c.nom, c.siret, c.email, c.adresse, c.date_creation, c.contact_person, c.telephone
            FROM societe c
            JOIN devis d ON c.societe_id = d.id_societe
            JOIN facture f ON d.devis_id = f.id_devis
            WHERE f.facture_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getInvoiceById($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT facture_id, date_emission, date_echeance, montant, montant_tva, montant_ht, statut, methode_paiement, id_devis, id_prestataire FROM facture WHERE facture_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function getInvoiceByProviderId($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT facture_id, date_emission, date_echeance, montant, montant_tva, montant_ht, statut, methode_paiement, id_devis, id_prestataire FROM facture WHERE id_prestataire = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetchAll();
}

function getInvoiceByEstimateId($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT facture_id, date_emission, date_echeance, montant, montant_tva, montant_ht, statut, methode_paiement, id_devis, id_prestataire FROM facture WHERE id_devis = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetchAll();
}

function getFeesByInvoiceID($id)
{
    $db = getDatabaseConnection();
    // Récupérer l'ID du devis associé à cette facture
    $sqlDevis = "SELECT id_devis FROM facture WHERE facture_id = :id";
    $stmtDevis = $db->prepare($sqlDevis);
    $stmtDevis->execute(['id' => $id]);
    $devis = $stmtDevis->fetch(PDO::FETCH_ASSOC);

    if (!$devis || !$devis['id_devis']) {
        return null; // Aucun devis associé à cette facture
    }

    // Récupérer les frais associés à ce devis
    $sql = "SELECT f.frais_id, f.montant, f.nom, f.description, f.est_abonnement
            FROM frais f
            JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
            WHERE ifd.id_devis = :id_devis AND f.est_abonnement = 0";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id_devis' => $devis['id_devis']]);

    if ($stmt->rowCount() == 0) {
        return null; // Aucun frais trouvé
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function modifyInvoiceState($id,$state)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE facture SET statut = :statut WHERE facture_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'id' => $id,
        'statut' => $state
    ]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function updateInvoice($id,  $date_emission = null,  $date_echeance = null, $montant = null,  $montant_tva = null,  $montant_ht = null,  $statut = null,  $methode_paiement = null,  $id_devis = null,  $id_prestataire = null)
{
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($date_emission !== null) {
        $setFields[] = "date_emission = :date_emission";
        $params['date_emission'] = $date_emission;
    }

    if ($date_echeance !== null) {
        $setFields[] = "date_echeance = :date_echeance";
        $params['date_echeance'] = $date_echeance;
    }

    if ($montant !== null) {
        $setFields[] = "montant = :montant";
        $params['montant'] = $montant;
    }

    if ($montant_tva !== null) {
        $setFields[] = "montant_tva = :montant_tva";
        $params['montant_tva'] = $montant_tva;
    }

    if ($montant_ht !== null) {
        $setFields[] = "montant_ht = :montant_ht";
        $params['montant_ht'] = $montant_ht;
    }

    if ($statut !== null) {
        $setFields[] = "statut = :statut";
        $params['statut'] = $statut;
    }

    if ($methode_paiement !== null) {
        $setFields[] = "methode_paiement = :methode_paiement";
        $params['methode_paiement'] = $methode_paiement;
    }

    if ($id_devis !== null) {
        $setFields[] = "id_devis = :id_devis";
        $params['id_devis'] = $id_devis;
    }

    if ($id_prestataire !== null) {
        $setFields[] = "id_prestataire = :id_prestataire";
        $params['id_prestataire'] = $id_prestataire;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE facture SET " . implode(", ", $setFields) . " WHERE facture_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function isValidInvoiceStatus($status)
{
    if ($status === null) {
        return false;
    }
    if ($status === "Attente" || $status === "Payee" || $status === "Annulee") {
        return true;
    }
    return false;
}


function generatePDFForProvider($factureId){
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $provider = getProviderByInvoice($factureId);
    if ($provider === null) {
        returnError(404, 'No provider found for this invoice');
    }

    $infos = getInvoiceByProvider($provider['prestataire_id']);
    if ($infos === null) {
        returnError(404, 'Invoice not found');
    }

    // Récupérer les autres frais
    $autresFrais = getFeesByInvoiceID($factureId);
    if ($autresFrais === null) {
        $autresFrais = []; // Aucun autre frais trouvé
    }


    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ccc;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            h1, h2, h3 {
                text-align: center;
                color: #444;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                margin-top: 30px;
                color: #666;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Provider Information</h1>
            <p>Generated on: ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <h2>Provider Details</h2>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>';

    foreach ($provider as $key => $value) {
        if ($value !== null) {
            // Formater les dates si la clé contient "date"
            if (strpos($key, 'date') !== false && $value != '') {
                $date = date_create_from_format('Y-m-d', $value);
                if ($date) {
                    $value = date_format($date, 'd/m/Y');
                }
            }
            $html .= '<tr>
                <td>' . htmlspecialchars($key) . '</td>
                <td>' . htmlspecialchars($value) . '</td>
            </tr>';
        }
    }

    $html .= '</table>
        <div class="page-break"></div>
        <h2>Invoices</h2>';

    foreach ($infos as $info) {
        // Formater les dates d'émission et d'échéance
        $dateEmission = $info['date_emission'] ? date_create_from_format('Y-m-d', $info['date_emission']) : null;
        $dateEcheance = $info['date_echeance'] ? date_create_from_format('Y-m-d', $info['date_echeance']) : null;

        $html .= '<table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Facture ID</td>
                <td>' . htmlspecialchars($info['facture_id']) . '</td>
            </tr>
            <tr>
                <td>Date Emission</td>
                <td>' . ($dateEmission ? date_format($dateEmission, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Date Echeance</td>
                <td>' . ($dateEcheance ? date_format($dateEcheance, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Montant</td>
                <td>' . htmlspecialchars($info['montant']) . ' €</td>
            </tr>
            <tr>
                <td>Montant TVA</td>
                <td>' . htmlspecialchars($info['montant_tva']) . ' €</td>
            </tr>
            <tr>
                <td>Montant HT</td>
                <td>' . htmlspecialchars($info['montant_ht']) . ' €</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>' . htmlspecialchars($info['statut']) . '</td>
            </tr>
            <tr>
                <td>Methode Paiement</td>
                <td>' . htmlspecialchars($info['methode_paiement']) . '</td>
            </tr>
        </table>';

    }

    if (!empty($autresFrais)) {
        // Toujours ajouter un saut de page après chaque facture
        $html .= '<div class="page-break"></div>';
        $html .= '<h2>Autres Frais</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Montant</th>
            </tr>';
        foreach ($autresFrais as $frais) {
            $html .= '<tr>
                <td>' . htmlspecialchars($frais['nom']) . '</td>
                <td>' . htmlspecialchars($frais['montant']) . ' €</td>
            </tr>';
        }
        $html .= '</table></div>';
    }

    $html .= '
        <div class="footer">
            <p>Document généré automatiquement. Ce document est confidentiel.</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Supprimer les echo qui polluent le PDF
    $dompdf->stream("provider_invoices.pdf", ["Attachment" => true]);
    exit;
}

function generatePDFForCompany($factureId){
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $company = getCompanyByInvoice($factureId);
    if ($company === null) {
        returnError(404, 'Company not found');
    }

    $provider = getProviderByInvoice($factureId);
    if ($provider !== null) {
        returnError(404,'This invoice is not for a company');
    }

    $infos = getInvoiceById($factureId);
    if ($infos === null) {
        returnError(404, 'Invoice not found');
    }

    // Récupérer les autres frais
    $autresFrais = getFeesByInvoiceID($factureId);
    if ($autresFrais === null) {
        $autresFrais = []; // Aucun autre frais trouvé
    }

    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ccc;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            h1, h2, h3 {
                text-align: center;
                color: #444;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                margin-top: 30px;
                color: #666;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Facture #' . htmlspecialchars($infos['facture_id']) . '</h1>
            <p>Généré le : ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <h2>Informations Société</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>';

    foreach ($company as $key => $value) {
        if ($value !== null) {
            // Formater les dates si la clé contient "date"
            if (strpos($key, 'date') !== false && $value != '') {
                $date = date_create_from_format('Y-m-d', $value);
                if ($date) {
                    $value = date_format($date, 'd/m/Y');
                }
            }
            $html .= '<tr>
                <td>' . htmlspecialchars($key) . '</td>
                <td>' . htmlspecialchars($value) . '</td>
            </tr>';
        }
    }
    $html .= '</table>';

    $html .= '<div class="page-break"></div>';

    // Formater les dates d'émission et d'échéance
    $dateEmission = $infos['date_emission'] ? date_create_from_format('Y-m-d', $infos['date_emission']) : null;
    $dateEcheance = $infos['date_echeance'] ? date_create_from_format('Y-m-d', $infos['date_echeance']) : null;

    $html .= '
        <h2>Détails de la Facture</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Facture ID</td>
                <td>' . htmlspecialchars($infos['facture_id']) . '</td>
            </tr>
            <tr>
                <td>Date Émission</td>
                <td>' . ($dateEmission ? date_format($dateEmission, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Date Échéance</td>
                <td>' . ($dateEcheance ? date_format($dateEcheance, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Montant TTC</td>
                <td>' . htmlspecialchars($infos['montant']) . ' €</td>
            </tr>
            <tr>
                <td>Montant TVA</td>
                <td>' . htmlspecialchars($infos['montant_tva']) . ' €</td>
            </tr>
            <tr>
                <td>Montant HT</td>
                <td>' . htmlspecialchars($infos['montant_ht']) . ' €</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>' . htmlspecialchars($infos['statut']) . '</td>
            </tr>
            <tr>
                <td>Méthode Paiement</td>
                <td>' . htmlspecialchars($infos['methode_paiement']) . '</td>
            </tr>
            <tr>
                <td>ID Devis</td>
                <td>' . htmlspecialchars($infos['id_devis']) . '</td>
            </tr>
                    <tr>
                        <td>ID Prestataire</td>
                        <td>' . htmlspecialchars($infos['id_prestataire']) . '</td>
                    </tr>
                </table>';

        if (!empty($autresFrais)) {
            $html .= '<div class="page-break"></div>
            <h2>Autres Frais</h2>
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Montant</th>
                </tr>';
            foreach ($autresFrais as $frais) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($frais['nom']) . '</td>
                    <td>' . htmlspecialchars($frais['montant']) . ' €</td>
                </tr>';
            }
            $html .= '</table>';
        }

        $html .= '
                <div class="footer">
                    <p>Ce document fait office de facture. Merci pour votre confiance.</p>
                </div>
            </body>
            </html>';

        $html .= '
        <div class="footer">
            <p>Ce document fait office de facture. Merci pour votre confiance.</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Supprimer les echo qui polluent le PDF
    $dompdf->stream("facture_" . $factureId . ".pdf", ["Attachment" => true]);
    exit;

}

/**
 * Génère et sauvegarde le PDF d'une facture pour une société dans un fichier
 * @param int $factureId ID de la facture
 * @return string|bool Chemin du fichier généré ou false en cas d'erreur
 */
function generateAndSaveCompanyInvoicePDF($factureId) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $company = getCompanyByInvoice($factureId);
    if ($company === null) {
        return false;
    }

    $infos = getInvoiceById($factureId);
    if ($infos === null) {
        return false;
    }

    // Récupérer les autres frais
    $autresFrais = getFeesByInvoiceID($factureId);
    if ($autresFrais === null) {
        $autresFrais = []; // Aucun autre frais trouvé
    }

    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ccc;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            h1, h2, h3 {
                text-align: center;
                color: #444;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                margin-top: 30px;
                color: #666;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Facture #' . htmlspecialchars($infos['facture_id']) . '</h1>
            <p>Généré le : ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <h2>Informations Société</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</h>
            </tr>';

    foreach ($company as $key => $value) {
        if ($value !== null) {
            // Formater les dates si la clé contient "date"
            if (strpos($key, 'date') !== false && $value != '') {
                $date = date_create_from_format('Y-m-d H:i:s', $value);
                if ($date) {
                    $value = date_format($date, 'd/m/Y');
                }
            }
            $html .= '<tr>
                <td>' . htmlspecialchars($key) . '</td>
                <td>' . htmlspecialchars($value) . '</td>
            </tr>';
        }
    }
    $html .= '</table>';

    $html .= '<div class="page-break"></div>';

    // Formater les dates d'émission et d'échéance
    $dateEmission = $infos['date_emission'] ? date_create_from_format('Y-m-d', $infos['date_emission']) : null;
    $dateEcheance = $infos['date_echeance'] ? date_create_from_format('Y-m-d', $infos['date_echeance']) : null;

    $html .= '
        <h2>Détails de la Facture</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Facture ID</td>
                <td>' . htmlspecialchars($infos['facture_id']) . '</td>
            </tr>
            <tr>
                <td>Date Émission</td>
                <td>' . ($dateEmission ? date_format($dateEmission, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Date Échéance</td>
                <td>' . ($dateEcheance ? date_format($dateEcheance, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Montant TTC</td>
                <td>' . htmlspecialchars($infos['montant']) . ' €</td>
            </tr>
            <tr>
                <td>Montant TVA</td>
                <td>' . htmlspecialchars($infos['montant_tva']) . ' €</td>
            </tr>
            <tr>
                <td>Montant HT</td>
                <td>' . htmlspecialchars($infos['montant_ht']) . ' €</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>' . htmlspecialchars($infos['statut']) . '</td>
            </tr>
            <tr>
                <td>Méthode Paiement</td>
                <td>' . htmlspecialchars($infos['methode_paiement']) . '</td>
            </tr>
            <tr>
                <td>ID Devis</td>
                <td>' . htmlspecialchars($infos['id_devis']) . '</td>
            </tr>
            <tr>
                <td>ID Prestataire</td>
                <td>' . htmlspecialchars($infos['id_prestataire']) . '</td>
            </tr>
        </table>';

        if (!empty($autresFrais)) {
        $html .= '<div class="page-break"></div>';
        $html .= '<h2>Autres Frais</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Montant</th>
            </tr>';
        foreach ($autresFrais as $frais) {
            $html .= '<tr>
                <td>' . htmlspecialchars($frais['nom']) . '</td>
                <td>' . htmlspecialchars($frais['montant']) . ' €</td>
            </tr>';
        }
        $html .= '</table>';
    }

    $html .= '
        <div class="footer">
            <p>Ce document fait office de facture. Merci pour votre confiance.</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Création du dossier de destination
    $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $company['nom']); // Nettoyer le nom de l'entreprise
    $currentDate = date('d-m-Y');
    $directory = $_SERVER['DOCUMENT_ROOT'] . '/data/invoice/' . $companyName;

    // Créer le répertoire s'il n'existe pas
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Définir le nom du fichier
    $filename = $currentDate . '_' . $factureId . '.pdf';
    $filePath = $directory . '/' . $filename;
    $relativePath = '/data/invoice/' . $companyName . '/' . $filename;

    // Enregistrer le PDF
    file_put_contents($filePath, $dompdf->output());

    return $relativePath;
}

/**
 * Génère et sauvegarde le PDF d'une facture pour un prestataire dans un fichier
 * @param int $factureId ID de la facture
 * @return string|bool Chemin du fichier généré ou false en cas d'erreur
 */
function generateAndSaveProviderInvoicePDF($factureId) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $provider = getProviderByInvoice($factureId);
    if ($provider === null) {
        return false;
    }

    $infos = getInvoiceByProvider($provider['prestataire_id']);
    if ($infos === null) {
        return false;
    }

    // Récupérer les autres frais
    $autresFrais = getFeesByInvoiceID($factureId);
    if ($autresFrais === null) {
        $autresFrais = []; // Aucun autre frais trouvé
    }

    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ccc;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            h1, h2, h3 {
                text-align: center;
                color: #444;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                margin-top: 30px;
                color: #666;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Facture Prestataire #' . htmlspecialchars($factureId) . '</h1>
            <p>Generated on: ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <h2>Provider Details</h2>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>';

    foreach ($provider as $key => $value) {
        if ($value !== null) {
            // Formater les dates si la clé contient "date"
            if (strpos($key, 'date') !== false && $value != '') {
                $date = date_create_from_format('Y-m-d', $value);
                if ($date) {
                    $value = date_format($date, 'd/m/Y');
                }
            }
            $html .= '<tr>
                <td>' . htmlspecialchars($key) . '</td>
                <td>' . htmlspecialchars($value) . '</td>
            </tr>';
        }
    }

    $html .= '</table>

        <div class="page-break"></div>
        <h2>Invoices</h2>';

    foreach ($infos as $info) {
        // Formater les dates d'émission et d'échéance
        $dateEmission = $info['date_emission'] ? date_create_from_format('Y-m-d', $info['date_emission']) : null;
        $dateEcheance = $info['date_echeance'] ? date_create_from_format('Y-m-d', $info['date_echeance']) : null;

        $html .= '<table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Facture ID</td>
                <td>' . htmlspecialchars($info['facture_id']) . '</td>
            </tr>
            <tr>
                <td>Date Emission</td>
                <td>' . ($dateEmission ? date_format($dateEmission, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Date Echeance</td>
                <td>' . ($dateEcheance ? date_format($dateEcheance, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Montant</td>
                <td>' . htmlspecialchars($info['montant']) . ' €</td>
            </tr>
            <tr>
                <td>Montant TVA</td>
                <td>' . htmlspecialchars($info['montant_tva']) . ' €</td>
            </tr>
            <tr>
                <td>Montant HT</td>
                <td>' . htmlspecialchars($info['montant_ht']) . ' €</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>' . htmlspecialchars($info['statut']) . '</td>
            </tr>
            <tr>
                <td>Methode Paiement</td>
                <td>' . htmlspecialchars($info['methode_paiement']) . '</td>
            </tr>
        </table>';

        // Toujours ajouter un saut de page après chaque table de facture
    }

    if (!empty($autresFrais)) {
        $html .= '<div class="page-break"></div>';
        $html .= '<h2>Autres Frais</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Montant</th>
            </tr>';
        foreach ($autresFrais as $frais) {
            $html .= '<tr>
                <td>' . htmlspecialchars($frais['nom']) . '</td>
                <td>' . htmlspecialchars($frais['montant']) . ' €</td>
            </tr>';
        }
        $html .= '</table>';
    }

    $html .= '
        <div class="footer">
            <p>Document généré automatiquement. Ce document est confidentiel.</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Création du dossier de destination
    $providerName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $provider['nom'] . '_' . $provider['prenom']); // Nettoyer le nom du prestataire
    $currentDate = date('d-m-Y');
    $directory = $_SERVER['DOCUMENT_ROOT'] . '/data/provider_invoice/' . $providerName;

    // Créer le répertoire s'il n'existe pas
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Définir le nom du fichier
    $filename = $currentDate . '_' . $factureId . '.pdf';
    $filePath = $directory . '/' . $filename;
    $relativePath = '/data/provider_invoice/' . $providerName . '/' . $filename;

    // Enregistrer le PDF
    file_put_contents($filePath, $dompdf->output());

    return $relativePath;
}