<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/place.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


// Vérification de l'ID du lieu
if (!isset($_GET['lieu_id'])) {
    returnError(400, 'lieu ID not provided');
    return;
}

$placeId = intval($_GET['lieu_id']);
$place = getPlaceById($placeId);

if (!$place) {
    returnError(404, 'Place not found');
    return;
}

$result = [
    "lieu_id" => $place['lieu_id'],
    "adresse" => $place['adresse'],
    "ville" => $place['ville'],
    "code_postal" => $place['code_postal']
];

echo json_encode($result);
http_response_code(200);
