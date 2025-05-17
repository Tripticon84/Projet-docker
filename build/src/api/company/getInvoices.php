<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

// Vérifier que l'ID de la société est fourni
if (!isset($_GET['societe_id'])) {
    returnError(400, 'Company ID is required');
    return;
}

$societe_id = intval($_GET['societe_id']);
$limit = null;
$offset = null;

// Récupérer les paramètres de filtrage
$statut = isset($_GET['statut']) ? $_GET['statut'] : null;
$date_emission = isset($_GET['date_emission']) ? $_GET['date_emission'] : null;
$date_echeance = isset($_GET['date_echeance']) ? $_GET['date_echeance'] : null;

if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'Limit must be a positive and non zero number');
        return;
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'Offset must be a positive number');
        return;
    }
}

$invoices = getCompanyInvoices($societe_id, $limit, $offset, $statut, $date_emission, $date_echeance);

if (!$invoices) {
    returnError(404, 'No invoices found');
    return;
}

$result = []; // Initialiser le tableau de résultats
foreach ($invoices as $invoice) {
    $result[] = [
        "facture_id" => $invoice['facture_id'],
        "date_emission" => $invoice['date_emission'],
        "date_echeance" => $invoice['date_echeance'],
        "montant" => $invoice['montant'],
        "montant_tva" => $invoice['montant_tva'],
        "montant_ht" => $invoice['montant_ht'],
        "statut" => $invoice['statut'],
        "methode_paiement" => $invoice['methode_paiement'],
        "id_devis" => $invoice['id_devis'],
        "id_prestataire" => $invoice['id_prestataire']
    ];
}

echo json_encode($result);
