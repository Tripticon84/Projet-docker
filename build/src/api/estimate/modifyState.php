<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/estimate.php";

header("Content-Type: application/json");

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

$data = getBody();

acceptedTokens(true, true, false, false);

if (!isset($data['devis_id'])) {
    returnError(400, 'Missing id');
    return;
}

if (!isset($data['statut'])) {
    returnError(400, 'Missing state');
    return;
}

$estimate = getEstimateById($data['devis_id']);
if (!$estimate) {
    returnError(404, 'Estimate not found');
    return;
}

if (!isValidEstimateStatus($data['statut'])) {
    returnError(400, 'Invalid status provided: ' . $data['statut']);
    return;
}

if ($data['statut'] == $estimate['statut']) {
    returnError(400, 'State is already set to ' . $data['statut']);
    return;
}

$modified = modifyEstimateState($data['devis_id'], $data['statut']);

if ($modified) {
    echo json_encode(['success' => 'Estimate State Modified']);
    return http_response_code(200);
} else {
    returnError(500, 'Failed to modify Estimate State');
}
