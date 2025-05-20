<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// acceptedTokens(true, true, false, false);

$devisId = $_GET['devis_id'];

if (empty($devisId)) {
    returnError(400, 'Mandatory parameter : devis_id');
}

if (!is_numeric($devisId)) {
    returnError(400, 'devis_id must be a number');
}

if ($devisId < 0) {
    returnError(400, 'devis_id must be a positive number');
}

if (getEstimateById($devisId) == null) {
    returnError(404, 'Estimate or contract not found');
}

if ($devisId) {
    // Générer et sauvegarder le PDF
    $pdfPath = generateAndSavePDF($devisId);

    $pdf = generatePDFForCompany($devisId);

    if ($pdfPath) {
        http_response_code(200);
        echo json_encode(['success' => true, 'file_path' => $pdfPath]);
    } else {
        returnError(500, 'Failed to generate PDF');
    }
} else {
    returnError(500, 'No estimate or contract found');
}
