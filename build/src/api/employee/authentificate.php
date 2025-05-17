<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
header("Content-Type: application/json");

if (!methodIsAllowed('login')) {
    returnError(405, 'Method not allowed');
}

$data = getBody();

if (!validateMandatoryParams($data, ['token'])) {
    returnError(400, 'Mandatory parameter : token');
}

$token = $data['token'];

$expiration = getExpirationByToken($token);

if (!$expiration) {
    returnError(402, "Invalid token");
}

// Vérifier si le token est expiré
$expirationDate = new DateTime($expiration['expiration']);
$now = new DateTime();

if ($now > $expirationDate) {
    returnError(401, "Token expired");
}

// Si le token est valide, récupérer les informations de l'employé
$employee = getEmployeeByToken($token);
if (!$employee) {
    returnError(402, "Employee not found");
}

returnSuccess([
    'employee' => [
        'id' => $employee['employee_id'],
        'username' => $employee['username']
    ]
]);
