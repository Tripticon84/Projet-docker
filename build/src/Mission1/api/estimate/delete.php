<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';


header('Content-Type: application/json');

if (!methodIsAllowed('delete')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

$data = getBody();
$id = $data['devis_id'];

if (validateMandatoryParams($data, ['devis_id'])) {

    // Vérifier l'id existe
    $estimate = getEstimateById($id);
    if (empty($estimate)) {
        returnError(400, 'Estimate does not exist');
        return;
    }


    $res = deleteEstimate($id);

    if (!$res) {
        returnError(500, 'Could not delete the Estimate');
        return;
    }

    echo json_encode(['id' => $id]);
    http_response_code(200);
    exit;
} else {
    returnError(412, 'Mandatory parameters: id');
}
