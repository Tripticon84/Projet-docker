<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/association.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

$data = getBody();

acceptedTokens(true, false, false, false);


if (!isset($data['association_id'])) {
    returnError(400, 'Missing id');
    return;
}

//verify if the admin exists
$association = getAssociationById($data['association_id']);

if (!$association) {
    returnError(404, 'Association not found');
    return;
}

$deleted = deleteAssociation($data['association_id']);

if ($deleted) {
    echo json_encode(['message' => 'Association deleted']);
    return http_response_code(200);
} else {
    returnError(500, 'association already deleted or error in the database');
}

