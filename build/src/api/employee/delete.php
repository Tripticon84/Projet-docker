<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('delete')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

$data = getBody();

if (!isset($data['id'])) {
    returnError(400, 'Missing id');
    return;
}

// verify if the employee exists
$employee = getEmployee($data['id']);
if (!$employee) {
    returnError(404, 'Employee not found');
    return;
}

$deleted = deleteEmployee($data['id']);

if ($deleted) {
    echo json_encode(['message' => 'Employee desactivated']);
    return http_response_code(200);
} else {
    returnError(500, 'Failed to delete employee');
}
