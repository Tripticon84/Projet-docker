<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/fees.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();

if (!validateMandatoryParams($data, ['frais_id', 'devis_id'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$fraisId = intval($data['frais_id']);
$devisId = intval($data['devis_id']);

$result = linkFraisToDevis($fraisId, $devisId);

if ($result === null) {
    returnError(500, 'Failed to link frais to devis');
    return;
} elseif ($result === 0) {
    returnError(409, 'Relation already exists');
    return;
} else {
    echo json_encode(['message' => 'Frais ' . $fraisId . ' linked to devis ' . $devisId .  ' successfully']);
    http_response_code(201);
    exit();
}
