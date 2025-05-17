<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);


$id_prestataire = null;
$limit = null;
$offset = null;

if (isset($_GET['id_prestataire'])) {
    $id_prestataire = intval($_GET['id_prestataire']);
    if ($id_prestataire < 1) {
        returnError(400, 'Id must be a positive number');
    }
}


if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'Limit must be a positive and non zero number');
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'Offset must be a positive number');
    }
}

$invoices = getAllInvoice($id_prestataire, $limit, $offset);
if($invoices === null){
    returnError(500, 'Internal Server Error');
    return;
}


$result = []; // Initialize the result array

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
if (empty($result)) {
    returnError(404, 'No invoice found');
    return;
}

echo json_encode($result);
