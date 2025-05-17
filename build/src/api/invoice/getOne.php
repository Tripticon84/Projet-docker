<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

// Vérification de l'ID du lieu
if (!isset($_GET['facture_id'])) {
    returnError(400, 'facture_id not provided');
    return;
}

$invoiceId = intval($_GET['facture_id']);
$invoice = getInvoiceById($invoiceId);

if (!$invoice) {
    returnError(404, 'invoice not found');
    return;
}

$result = [
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

echo json_encode($result);
http_response_code(200);
