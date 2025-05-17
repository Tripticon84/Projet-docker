<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/invoice.php";

header("Content-Type: application/json");

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

$data = getBody();

acceptedTokens(true, true, false, false);


if (!isset($data['facture_id'])) {
    returnError(400, 'Missing id');
    return;
}

if (!isset($data['statut'])) {
    returnError(400, 'Missing state');
    return;
}

$invoice = getInvoiceById($data['facture_id']);
if (!$invoice) {
    returnError(404, 'Invoice not found');
    return;
}

if (!isValidInvoiceStatus($data['statut'])) {
    returnError(400, 'Invalid status provided: ' . $data['statut']);
    return;
}

if ($data['statut'] == $invoice['statut']) {
    returnError(400, 'State is already set to ' . $data['statut']);
    return;
}

$modified = modifyInvoiceState($data['facture_id'], $data['statut']);

if ($modified) {
    echo json_encode(['success' => true, 'message' => 'Invoice State Modified']);
    return http_response_code(200);
} else {
    returnError(500, 'Failed to modified Invoice State');
}
