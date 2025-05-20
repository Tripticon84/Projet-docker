<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, false);


// Vérification de l'ID de l'employee
if (!isset($_GET['id'])) {
    returnError(400, 'Employee ID not provided');
    return;
}

$employeeId = intval($_GET['id']);
$employee = getEmployee($employeeId);

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
    "desactivate" => $employee['desactivate']
];

echo json_encode($result);
http_response_code(200);
