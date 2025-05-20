<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

// Vérification de l'ID de la facture
if (!isset($_GET['facture_id'])) {
    returnError(400, 'facture_id not provided');
    return;
}

$invoiceId = intval($_GET['facture_id']);
$fees = getFeesByInvoiceID($invoiceId);

if (!$fees) {
    returnError(404, 'no additional fees found for this invoice');
    return;
}

echo json_encode($fees);
http_response_code(200);
