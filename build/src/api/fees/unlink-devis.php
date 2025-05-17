<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/fees.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
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

$result = unlinkFraisFromDevis($fraisId, $devisId);

if ($result === null) {
    returnError(500, 'Failed to unlink frais from devis');
    return;
} elseif ($result === 0) {
    returnError(404, 'Relation not found');
    return;
} else {
    echo json_encode(['message' => 'Frais ' . $fraisId . ' unlinked from devis ' . $devisId .  ' successfully']);
    return;
}


returnError(400, 'Invalid request');
