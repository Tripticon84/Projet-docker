<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// acceptedTokens(true, false, false, true);


$factureId = $_GET['facture_id'];

if (empty($factureId)) {
    returnError(400, 'Mandatory parameter : facture_id');
}

if (!is_numeric($factureId)) {
    returnError(400, 'facture_id must be a number');
}

if ($factureId < 0) {
    returnError(400, 'facture_id must be a positive number');
}

if ($factureId) {
    // Générer et sauvegarder le PDF
    $pdfPath = generateAndSaveProviderInvoicePDF($factureId);

    $pdf = generatePDFForProvider($factureId);

    if ($pdf) {
        http_response_code(200);
        echo $pdf;
    } else {
        returnError(500, 'Failed to generate PDF');
    }
} else {
    returnError(500, 'No invoice found');
}
