<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// acceptedTokens(true, true, true, false);

if (!isset($_GET['username']) || !isset($_GET['password'])) {
    returnError(400, 'Username and password are required');
    return;
}

$username = $_GET['username'];
$password = $_GET['password'];


$employee = findEmployeeByCredentials($username, $password);

if (!$employee) {
    returnError(404, 'Employee not found');
    return;
}

$result = [
    "collaborateur_id" => $employee['collaborateur_id'],
    "nom" => $employee['nom'],
    "prenom" => $employee['prenom'],
    "username" => $employee['username'],
    "role" => $employee['role'],
    "email" => $employee['email'],
    "date_creation" => $employee['date_creation'],
    "date_activite" => $employee['date_activite'],
    "telephone" => $employee['telephone'],
    "id_societe" => $employee['id_societe'],
    "desactivate" => $employee['desactivate'],
];

echo json_encode($result);
http_response_code(200);
