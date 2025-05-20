<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/activity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();

if (!isset($data['activite_id'])) {
    returnError(400, 'Missing id');
    return;
}

$activity = getActivityById($data['activite_id']);
if (!$activity) {
    returnError(404, 'Activity not found');
    return;
}

$activated = activateActivity($data['activite_id']);

if ($activated) {
    echo json_encode(['message' => 'Activity activated']);
    return http_response_code(200);
} else {
    returnError(500, 'Failed to delete activity');
}
