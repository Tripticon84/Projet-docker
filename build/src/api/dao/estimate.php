<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

function createEstimate($date_debut, $date_fin, string $statut, float $montant_ht, $is_contract, int $id_societe, $company_name)
{
    $db = getDatabaseConnection();
    $sql = "INSERT INTO devis (date_debut, date_fin, statut, montant_ht, montant_tva, montant, is_contract, fichier, id_societe) VALUES (:date_debut, :date_fin, :statut, :montant_ht, :montant_tva, :montant, :is_contract, :fichier, :id_societe)";
    $stmt = $db->prepare($sql);
    if ($is_contract == 1) {
        $fichier = '/contract/' . $company_name . '/' . $date_debut . '_' . $date_fin . '/';
    } else {
        $fichier = '/estimate/' . $company_name . '/' . $date_debut . '_' . $date_fin . '/';
    }

    // Calcul du montant TVA sur le montant HT original
    $montant_tva = $montant_ht * 0.2; // TVA à 20%
    // Montant TTC sans marge
    $montant_sans_marge = $montant_ht + $montant_tva;
    // Application de la marge de 15% sur le montant TTC
    $montant = $montant_sans_marge * 1.15; // 15% de marge sur le TTC

    $res = $stmt->execute([
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'statut' => $statut,
        'montant_ht' => $montant_ht,
        'montant_tva' => $montant_tva,
        'montant' => $montant, // Montant TTC avec marge
        'is_contract' => $is_contract,
        'id_societe' => $id_societe,
        'fichier' => $fichier
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function updateEstimate($date_debut, $date_fin, $statut, $montant_ht, $is_contract, $id_societe, $fichier, $devis_id) {
    $db = getDatabaseConnection();

    $sql = "UPDATE devis SET ";
    $params = [];

    if ($date_debut !== null) {
        $sql .= "date_debut = :date_debut, ";
        $params[':date_debut'] = $date_debut;
    }

    if ($date_fin !== null) {
        $sql .= "date_fin = :date_fin, ";
        $params[':date_fin'] = $date_fin;
    }

    if ($statut !== null) {
        $sql .= "statut = :statut, ";
        $params[':statut'] = $statut;
    }

    // Si montant_ht est fourni, recalculer montant_tva et montant
    if ($montant_ht !== null) {
        $sql .= "montant_ht = :montant_ht, ";
        $params[':montant_ht'] = $montant_ht;

        // Calcul de la TVA sur le montant HT original
        $montant_tva = $montant_ht * 0.2; // TVA à 20%
        // Montant TTC sans marge
        $montant_sans_marge = $montant_ht + $montant_tva;
        // Application de la marge de 15% sur le montant TTC
        $montant = $montant_sans_marge * 1.15; // 15% de marge sur le TTC

        $sql .= "montant_tva = :montant_tva, montant = :montant, ";
        $params[':montant_tva'] = $montant_tva;
        $params[':montant'] = $montant;
    }

    if ($is_contract !== null) {
        $sql .= "is_contract = :is_contract, ";
        $params[':is_contract'] = $is_contract;
    }

    if ($id_societe !== null) {
        $sql .= "id_societe = :id_societe, ";
        $params[':id_societe'] = $id_societe;
    }

    if ($fichier !== null) {
        $sql .= "fichier = :fichier, ";
        $params[':fichier'] = $fichier;
    }

    // Enlever la virgule et l'espace à la fin
    $sql = rtrim($sql, ", ");

    $sql .= " WHERE devis_id = :devis_id";
    $params[':devis_id'] = $devis_id;

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $result = $stmt->execute($params);

    return $result;
}

function deleteEstimate(int $id)
{
    $db = getDatabaseConnection();
    $sql = "DELETE FROM devis WHERE devis_id = :devis_id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        "devis_id" => $id
    ]);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function getEstimateById($id)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, fichier, id_societe FROM devis WHERE devis_id = :id ";
    $query = $connection->prepare($sql);
    $res = $query->execute(['id' => $id]);
    if ($res) {
        $estimate = $query->fetch(PDO::FETCH_ASSOC);
        if ($estimate) {
            $estimate['frais'] = getFraisByEstimateId($id);
        }
        return $estimate;
    }
    return null;
}

/**
 *tout les params sont optionnels : limite de résultats et le dernier pour définir où on commence (utile pour la pagination)
 */
function getAllEstimate(int $limit = null, int $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, fichier, id_societe FROM devis Where is_contract = 0";
    $params = [];

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
/**
 * tout les params sont optionnels: le premier pour définir la limite de résultats,
 * le deuxième pour définir où on commence (utile pour la pagination)
 * et le troisième pour filtrer par prestataire
 */
function getAllContract(int $limit = null, int $offset = null, int $provider_id = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, fichier, id_societe FROM devis Where is_contract = 1 AND date_fin > NOW()";
    $params = [];

    // Filtrage par prestataire
    if ($provider_id !== null) {
        $sql .= " AND id_societe = :provider_id";
        $params[':provider_id'] = $provider_id;
    }

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getAllContractExpired(int $limit = null, int $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, fichier, id_societe FROM devis WHERE is_contract = 1 AND date_fin < NOW()";
    $params = [];

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getContractStats()
{
    $db = getDatabaseConnection();

    // Nombre total de devis (incluant contrats et non-contrats)
    $sqlTotalEstimates = "SELECT COUNT(devis_id) as total FROM devis";
    $stmtTotalEstimates = $db->prepare($sqlTotalEstimates);
    $stmtTotalEstimates->execute();
    $totalEstimates = $stmtTotalEstimates->fetch(PDO::FETCH_ASSOC)['total'];

    // Nombre de contrats actifs (dont la date de fin est postérieure à la date actuelle)
    $sqlActiveContracts = "SELECT COUNT(devis_id) as total FROM devis WHERE is_contract = 1 AND date_fin > NOW()";
    $stmtActiveContracts = $db->prepare($sqlActiveContracts);
    $stmtActiveContracts->execute();
    $activeContracts = $stmtActiveContracts->fetch(PDO::FETCH_ASSOC)['total'];

    // Montant total des contrats du mois en cours
    $currentMonth = date('Y-m');
    $sqlMonthlyContractsAmount = "SELECT SUM(montant) as total FROM devis WHERE is_contract = 1 AND DATE_FORMAT(date_debut, '%Y-%m') = :currentMonth";
    $stmtMonthlyContractsAmount = $db->prepare($sqlMonthlyContractsAmount);
    $stmtMonthlyContractsAmount->execute(['currentMonth' => $currentMonth]);
    $monthlyContractsAmount = $stmtMonthlyContractsAmount->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

    // Nombre de contrats (pour calculer le taux de conversion)
    $sqlContracts = "SELECT COUNT(devis_id) as total FROM devis WHERE is_contract = 1";
    $stmtContracts = $db->prepare($sqlContracts);
    $stmtContracts->execute();
    $totalContracts = $stmtContracts->fetch(PDO::FETCH_ASSOC)['total'];

    // Calcul du taux de conversion
    $conversionRate = 0;
    if ($totalEstimates > 0) {
        $conversionRate = ($totalContracts / $totalEstimates) * 100;
    }

    return [
        'devis_totaux' => $totalEstimates,
        'contrats_actifs' => $activeContracts,
        'montant_total_contrats_mois' => round($monthlyContractsAmount, 2),
        'taux_conversion' => round($conversionRate, 2)
    ];
}

function modifyEstimateState($id, $state)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE devis SET statut = :statut WHERE devis_id = :id";
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

function isValidEstimateStatus($status)
{
    if ($status === null) {
        return false;
    }
    $validStatuses = ['brouillon', 'envoyé', 'accepté', 'refusé'];
    return in_array($status, $validStatuses);
}

function convertToContract($devis_id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE devis SET is_contract = 1, statut = 'accepté' WHERE devis_id = :devis_id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'devis_id' => $devis_id
    ]);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function getContractByProvider(int $provider_id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT DISTINCT d.devis_id, d.date_debut, d.date_fin, d.statut, d.montant, d.montant_ht, d.montant_tva,
                   d.is_contract, d.fichier, d.id_societe
            FROM devis d
            INNER JOIN facture f ON d.devis_id = f.id_devis
            WHERE d.is_contract = 1 AND f.id_prestataire = :provider_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'provider_id' => $provider_id
    ]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getCompanyByEstimate($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT c.societe_id, c.nom, c.siret, c.email, c.adresse, c.date_creation, c.contact_person, c.telephone
            FROM societe c
            JOIN devis d ON c.societe_id = d.id_societe
            WHERE d.devis_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getContractDetailsById($id) {
    $db = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, fichier, id_societe FROM devis WHERE devis_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Modifie la fonction generatePDFForCompany pour inclure les frais
function generatePDFForCompany($devisId)
{
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $estimate = getEstimateById($devisId);
    if ($estimate === null) {
        returnError(404, 'Estimate or contract not found');
    }

    $company = getCompanyByEstimate($devisId);
    if ($company === null) {
        returnError(404, 'Company not found for this estimate');
    }

    $documentType = $estimate['is_contract'] == 1 ? 'Contrat' : 'Devis';
    $documentTitle = $estimate['is_contract'] == 1 ? 'Contrat #' . $devisId : 'Devis #' . $devisId;

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
            <h1>' . $documentTitle . '</h1>
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

    // Ajout des frais associés au devis
    $frais = getFraisByEstimateId($devisId);
    if (!empty($frais)) {
        $html .= '<h2>Frais associés</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Montant</th>
                <th>Type</th>
            </tr>';

        $totalFrais = 0;
        foreach ($frais as $f) {
            $totalFrais += $f['montant'];
            $typeAbonnement = $f['est_abonnement'] ? 'Abonnement' : 'Frais ponctuel';
            $html .= '<tr>
                <td>' . htmlspecialchars($f['nom']) . '</td>
                <td>' . htmlspecialchars($f['description']) . '</td>
                <td>' . htmlspecialchars($f['montant']) . ' €</td>
                <td>' . $typeAbonnement . '</td>
            </tr>';
        }

        $html .= '<tr>
                <td colspan="2"><strong>Total des frais</strong></td>
                <td><strong>' . $totalFrais . ' €</strong></td>
                <td></td>
            </tr>';
        $html .= '</table>';
    }

    // Formater les dates de début et fin
    $dateDebut = $estimate['date_debut'] ? date_create_from_format('Y-m-d', $estimate['date_debut']) : null;
    $dateFin = $estimate['date_fin'] ? date_create_from_format('Y-m-d', $estimate['date_fin']) : null;

    $html .= '
        <h2>Détails du ' . $documentType . '</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>ID</td>
                <td>' . htmlspecialchars($estimate['devis_id']) . '</td>
            </tr>
            <tr>
                <td>Date de début</td>
                <td>' . ($dateDebut ? date_format($dateDebut, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Date de fin</td>
                <td>' . ($dateFin ? date_format($dateFin, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Montant TTC</td>
                <td>' . htmlspecialchars($estimate['montant']) . ' €</td>
            </tr>';

    if ($estimate['montant_tva'] !== null) {
        $html .= '<tr>
                <td>Montant TVA</td>
                <td>' . htmlspecialchars($estimate['montant_tva']) . ' €</td>
            </tr>';
    }

    if ($estimate['montant_ht'] !== null) {
        $html .= '<tr>
                <td>Montant HT</td>
                <td>' . htmlspecialchars($estimate['montant_ht']) . ' €</td>
            </tr>';
    }

    $html .= '<tr>
                <td>Statut</td>
                <td>' . htmlspecialchars($estimate['statut']) . '</td>
            </tr>
            <tr>
                <td>Type de document</td>
                <td>' . $documentType . '</td>
            </tr>
        </table>

        <div class="footer">';

    if ($estimate['is_contract'] == 1) {
        $html .= '<p>Ce document fait office de contrat. Merci pour votre confiance.</p>';
    } else {
        $html .= '<p>Ce document fait office de devis. Valable pendant 30 jours à compter de la date d\'émission.</p>';
    }

    $html .= '</div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Générer le PDF
    $docType = $estimate['is_contract'] == 1 ? 'contrat' : 'devis';
    $dompdf->stream($docType . "_" . $devisId . ".pdf", ["Attachment" => true]);
    exit;
}

/**
 * Récupère les frais associés à un devis
 */
function getFraisByEstimateId($estimateId) {
    $db = getDatabaseConnection();
    $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description, f.est_abonnement
            FROM frais f
            JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
            WHERE ifd.id_devis = :estimate_id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['estimate_id' => $estimateId]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}


/**
 * Associe des frais à un devis
 */
function attachFraisToEstimate($estimateId, $fraisIds) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/fees.php";

    $db = getDatabaseConnection();

    try {
        // Supprime d'abord les associations existantes
        $sql = "DELETE FROM INCLUT_FRAIS_DEVIS WHERE id_devis = :estimate_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['estimate_id' => $estimateId]);

        // Ajoute les nouvelles associations en utilisant la fonction linkFraisToDevis
        if (!empty($fraisIds)) {
            error_log(print_r($fraisIds, true));

            foreach ($fraisIds as $fraisId) {
                $result = linkFraisToDevis($fraisId, $estimateId);
                if ($result === null) {
                    // Si une erreur se produit, lancer une exception
                    throw new Exception("Impossible d'associer le frais ID {$fraisId} au devis ID {$estimateId}");
                }
                error_log("Attached frais_id {$fraisId} to estimate_id {$estimateId}");
            }
        }

        // Met à jour le montant total du devis
        updateEstimateTotalAmount($estimateId);

        return true;
    } catch (Exception $e) {
        error_log("Error attaching frais to estimate: " . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour le montant total d'un devis en fonction des frais associés
 */
function updateEstimateTotalAmount($estimateId) {
    $db = getDatabaseConnection();

    // Récupérer le montant de base du devis (sans les frais)
    $sql = "SELECT montant_ht FROM devis WHERE devis_id = :estimate_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['estimate_id' => $estimateId]);
    $estimate = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculer le total des frais
    $sql = "SELECT SUM(f.montant) as total_frais
            FROM frais f
            JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
            WHERE ifd.id_devis = :estimate_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['estimate_id' => $estimateId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalFrais = $result['total_frais'] ?? 0;

    // Calculer les nouveaux montants
    $montantHT = ($estimate['montant_ht'] ?? 0) + $totalFrais;
    // Calcul de la TVA sur le montant HT
    $montantTVA = $montantHT * 0.2; // TVA à 20%
    // Montant TTC sans marge
    $montantSansMarge = $montantHT + $montantTVA;
    // Application de la marge de 15% sur le montant TTC
    $montantTotal = $montantSansMarge * 1.15; // 15% de marge sur le TTC

    // Mettre à jour le devis
    $sql = "UPDATE devis SET
            montant_ht = :montant_ht,
            montant_tva = :montant_tva,
            montant = :montant
            WHERE devis_id = :estimate_id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([
        'montant_ht' => $montantHT,
        'montant_tva' => $montantTVA,
        'montant' => $montantTotal,
        'estimate_id' => $estimateId
    ]);
}

function getEstimateBySocietyId($societyId)
{
    $db = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, fichier, id_societe FROM devis WHERE id_societe = :societyId";
    $stmt = $db->prepare($sql);
    $stmt->execute(['societyId' => $societyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Génère et sauvegarde le PDF d'un devis/contrat dans un fichier
 * @param int $devisId ID du devis
 * @return string|bool Chemin du fichier généré ou false en cas d'erreur
 */
function generateAndSavePDF($devisId) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $estimate = getEstimateById($devisId);
    if ($estimate === null) {
        return false;
    }

    $company = getCompanyByEstimate($devisId);
    if ($company === null) {
        return false;
    }

    $documentType = $estimate['is_contract'] == 1 ? 'Contrat' : 'Devis';
    $documentTitle = $estimate['is_contract'] == 1 ? 'Contrat #' . $devisId : 'Devis #' . $devisId;

    // Générer le HTML du PDF (même code que dans generatePDFForCompany)
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
            <h1>' . $documentTitle . '</h1>
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

    // Ajout des frais associés au devis
    $frais = getFraisByEstimateId($devisId);
    if (!empty($frais)) {
        $html .= '<h2>Frais associés</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Montant</th>
                <th>Type</th>
            </tr>';

        $totalFrais = 0;
        foreach ($frais as $f) {
            $totalFrais += $f['montant'];
            $typeAbonnement = $f['est_abonnement'] ? 'Abonnement' : 'Frais ponctuel';
            $html .= '<tr>
                <td>' . htmlspecialchars($f['nom']) . '</td>
                <td>' . htmlspecialchars($f['description']) . '</td>
                <td>' . htmlspecialchars($f['montant']) . ' €</td>
                <td>' . $typeAbonnement . '</td>
            </tr>';
        }

        $html .= '<tr>
                <td colspan="2"><strong>Total des frais</strong></td>
                <td><strong>' . $totalFrais . ' €</strong></td>
                <td></td>
            </tr>';
        $html .= '</table>';
    }

    // Formater les dates de début et fin
    $dateDebut = $estimate['date_debut'] ? date_create_from_format('Y-m-d', $estimate['date_debut']) : null;
    $dateFin = $estimate['date_fin'] ? date_create_from_format('Y-m-d', $estimate['date_fin']) : null;

    $html .= '
        <h2>Détails du ' . $documentType . '</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>ID</td>
                <td>' . htmlspecialchars($estimate['devis_id']) . '</td>
            </tr>
            <tr>
                <td>Date de début</td>
                <td>' . ($dateDebut ? date_format($dateDebut, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Date de fin</td>
                <td>' . ($dateFin ? date_format($dateFin, 'd/m/Y') : '') . '</td>
            </tr>
            <tr>
                <td>Montant TTC</td>
                <td>' . htmlspecialchars($estimate['montant']) . ' €</td>
            </tr>';

    if ($estimate['montant_tva'] !== null) {
        $html .= '<tr>
                <td>Montant TVA</td>
                <td>' . htmlspecialchars($estimate['montant_tva']) . ' €</td>
            </tr>';
    }

    if ($estimate['montant_ht'] !== null) {
        $html .= '<tr>
                <td>Montant HT</td>
                <td>' . htmlspecialchars($estimate['montant_ht']) . ' €</td>
            </tr>';
    }

    $html .= '<tr>
                <td>Statut</td>
                <td>' . htmlspecialchars($estimate['statut']) . '</td>
            </tr>
            <tr>
                <td>Type de document</td>
                <td>' . $documentType . '</td>
            </tr>
        </table>

        <div class="footer">';

    if ($estimate['is_contract'] == 1) {
        $html .= '<p>Ce document fait office de contrat. Merci pour votre confiance.</p>';
    } else {
        $html .= '<p>Ce document fait office de devis. Valable pendant 30 jours à compter de la date d\'émission.</p>';
    }

    $html .= '</div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Création du dossier de destination
    $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $company['nom']); // Nettoyer le nom de l'entreprise
    $currentDate = date('d-m-Y');
    $directory = $_SERVER['DOCUMENT_ROOT'] . '/data/estimate/' . $companyName;

    // Créer le répertoire s'il n'existe pas
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Définir le nom du fichier
    $filename = $currentDate . '_' . $devisId . '.pdf';
    $filePath = $directory . '/' . $filename;
    $relativePath = '/data/estimate/' . $companyName . '/' . $filename;

    // Enregistrer le PDF
    file_put_contents($filePath, $dompdf->output());

    // Mettre à jour le chemin du fichier dans la base de données
    $db = getDatabaseConnection();
    $sql = "UPDATE devis SET fichier = :fichier WHERE devis_id = :devis_id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'fichier' => $relativePath,
        'devis_id' => $devisId
    ]);

    return $relativePath;
}
