<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

if (!isset($_GET['id'])) {
    returnError(400, 'Missing required parameter: id');
    return;
}

$activityId = $_GET['id'];
$activity = getActivityById($activityId);

if (!$activity) {
    returnError(404, 'Activity not found');
    return;
}

// Format the response to match what the frontend expects
$response = [
    "activite_id" => $activity['activite_id'],
    "nom" => $activity['nom'],
    "name" => $activity['nom'], // Add this for compatibility
    "date" => $activity['date'],
    "type" => $activity['type'],
    "id_lieu" => $activity['id_lieu']
];

// Add address information if available
if (!empty($activity['adresse'])) {
    $response["adresse"] = $activity['adresse'];
    $response["ville"] = $activity['ville'];
    $response["code_postal"] = $activity['code_postal'];
}

echo json_encode($response);
?>
