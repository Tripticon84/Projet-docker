<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');


if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// Récupération de l'ID depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    returnError(400, 'ID parameter is required');
    return;
}

$employeeId = $_GET['id'];

// Récupération des informations de l'employee
$employee = getEmployee($employeeId);

if (!$employee) {
    returnError(404, 'Employee not found');
    return;
}

// On ne renvoie jamais le mot de passe, même hashé
unset($employee['password']);

// Retour des informations de l'employee
echo json_encode($employee);
http_response_code(200);
exit();
