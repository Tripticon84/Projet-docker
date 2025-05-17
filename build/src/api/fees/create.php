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

if (!validateMandatoryParams($data, ['nom', 'montant'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$nom = $data['nom'];
$montant = floatval($data['montant']);
$description = isset($data['description']) ? $data['description'] : '';
$est_abonnement = isset($data['est_abonnement']) ? intval($data['est_abonnement']) : 0;

$frais_id = createFrais($nom, $montant, $description, $est_abonnement);

if (!$frais_id) {
    returnError(500, 'Internal Server Error');
    return;
} else {
    echo json_encode(['frais_id' => $frais_id]);
    http_response_code(201);
    exit();
}
