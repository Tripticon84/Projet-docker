<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/activity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


$activityId = $_GET['activite_id'];

if (!isset($activityId)) {
    returnError(400, 'Missing id');
    return;
}

if (!is_numeric($activityId)) {
    returnError(400, 'Invalid parameter type. activite_id must be an integer.');
    return;
}

$activity = getActivityById($activityId);

if (!$activity) {
    returnError(404, 'Activity not found');
    return;
}

$result = [
    "id" => $activity['activite_id'],
    "nom" => $activity['nom'],
    "type" => $activity['type'],
    "date" => $activity['date'],
    "id_lieu" => $activity['id_lieu'],
    "id_devis" => $activity['id_devis'],
    "id_prestataire" => $activity['id_prestataire']
];

echo json_encode($result);
