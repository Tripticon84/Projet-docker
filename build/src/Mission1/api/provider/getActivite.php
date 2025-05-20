<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);

$limit = null;
$offset = null;
$providerId = $_GET['id'];

if (!isset($providerId)) {
    returnError(400, 'Missing id');
    return;
}

$provider = getProviderById($providerId);
if (!$provider) {
    returnError(404, 'Provider not found');
    return;
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

$activities = getAllActivities($limit, $offset, $providerId);

$result = []; // Initialize the result array

foreach ($activities as $activity) {
    // Construire l'adresse complète
    $fullAddress = "";
    if (!empty($activity['adresse'])) {
        $fullAddress .= $activity['adresse'];
    }
    if (!empty($activity['ville'])) {
        $fullAddress .= (!empty($fullAddress) ? ", " : "") . $activity['ville'];
    }
    if (!empty($activity['code_postal'])) {
        $fullAddress .= (!empty($fullAddress) ? " " : "") . $activity['code_postal'];
    }
    
    $result[] = [
        "activite_id" => $activity['activite_id'],
        "name" => $activity['nom'],
        "date" => $activity['date'],
        "place" => !empty($fullAddress) ? $fullAddress : "Lieu non spécifié",
        "place_id" => $activity['id_lieu'],
        "address" => $activity['adresse'] ?? null,
        "city" => $activity['ville'] ?? null,
        "postal_code" => $activity['code_postal'] ?? null,
        "type" => $activity['type'],
        "id_estimate" => $activity['id_devis'],
        "refused" => isset($activity['refusee']) ? (bool)$activity['refusee'] : false
    ];
}

echo json_encode($result);
