<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/place.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


$adress = '';
$limit = null;
$offset = null;

if (isset($_GET['adresse'])) {
    $adress = trim($_GET['adresse']); // Fix the parameter name
}
if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'Limit must be a positive and non zero number');
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'Offset must be a positive number');
    }
}

$places = getAllPlace($adress, $limit, $offset);

$result = []; // Initialize the result array

foreach ($places as $place) {
    $result[] = [
        "lieu_id" => $place['lieu_id'],
        "adresse" => $place['adresse'],
        "ville" => $place['ville'],
        "code_postal" => $place['code_postal']
    ];
}

echo json_encode($result);
