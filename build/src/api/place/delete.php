<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/place.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('delete')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();


if (!isset($data['lieu_id'])) {
    returnError(400, 'Missing id');
    return;
}

//verify if the admin exists
$place = getPlaceById($data['lieu_id']);
if (!$place) {
    returnError(404, 'Place not found');
    return;
}

$deleted = deletePlace($data['lieu_id']);

if ($deleted) {
    echo json_encode(['message' => 'place deleted']);
    return http_response_code(200);
} else {
    returnError(500, 'Failed to delete admin');
}
