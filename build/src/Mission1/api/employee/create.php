<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);


$data = getBody();

if (validateMandatoryParams($data, ['nom', 'prenom', 'username', 'role', 'email', 'password', 'telephone', 'id_societe'])) {

    // Vérifier l'email n'existe pas
    $employee = getEmployeeByEmail($data['email']);
    if (!empty($employee)) {
        returnError(400, 'Email already exists');
        return;
    }

    // Vérifier l'username n'existe pas
    $employee = getEmployeeByUsername($data['username']);
    if (!empty($employee)) {
        returnError(400, 'Username already exists');
        return;
    }

    // Vérifier le telephone n'existe pas
    $employee = getEmployeeByTelephone($data['telephone']);
    if (!empty($employee)) {
        returnError(400, 'Telephone already exists');
        return;
    }

    // Vérification de la longueur du mot de passe
    if (strlen($data['password']) < 8) {
         returnError(400, 'Password must be at least 8 characters long');
        return;
    }

    $newEmployeeId = createEmployee($data['nom'], $data['prenom'], $data['username'], $data['role'], $data['email'], $data['password'], $data['telephone'], $data['id_societe']);

    if (!$newEmployeeId) {
        returnError(500, 'Could not create the employee');
        return;
    }

    echo json_encode(['id' => $newEmployeeId]);
    http_response_code(201);
    exit;

} else {
    returnError(412, 'Mandatory parameters : name, prenom, username, role, email, password, telephone, id_societe');
}
