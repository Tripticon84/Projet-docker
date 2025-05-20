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

if (!isset($data['frais_id'])) {
    returnError(400, 'Missing frais_id');
    return;
}

$frais_id = $data['frais_id'];
$frais = getFraisById($frais_id);

if (!$frais) {
    returnError(404, 'Frais not found');
    return;
}

$nom = isset($data['nom']) ? $data['nom'] : null;
$montant = isset($data['montant']) ? floatval($data['montant']) : null;
$description = isset($data['description']) ? $data['description'] : null;
$est_abonnement = isset($data['est_abonnement']) ? intval($data['est_abonnement']) : null;

$updated = updateFrais($frais_id, $nom, $montant, $description, $est_abonnement);

if ($updated === 0) {
    returnError(400, 'No changes to update');
    return;
} elseif ($updated === null) {
    returnError(500, 'Failed to update frais');
    return;
} else {
    echo json_encode(['message' => 'Frais updated successfully']);
    return;
}
