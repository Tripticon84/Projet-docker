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

$frais = getFraisById($data['frais_id']);
if (!$frais) {
    returnError(404, 'Frais not found');
    return;
}

// Supprimer d'abord toutes les relations du frais avec les devis
removeAllFraisRelations($data['frais_id']);

// Ensuite supprimer le frais lui-même
$deleted = deleteFrais($data['frais_id']);

if ($deleted) {
    echo json_encode(['message' => 'Frais deleted successfully']);
    return http_response_code(200);
} else {
    returnError(500, 'Failed to delete frais');
}
