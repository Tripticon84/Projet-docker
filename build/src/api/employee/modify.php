<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);


// Récupérer les données de la requête
$data = getBody();

// Vérifier si l'ID est présent
if (!isset($data['id']) || empty($data['id'])) {
    returnError(400, 'Employee ID is required');
    return;
}

// Récupérer l'employee par son ID
$employeeId = $data['id'];
$existingEmployee = getEmployee($employeeId);

// Vérifier si l'employee existe
if (!$existingEmployee) {
    returnError(404, 'Employee not found');
    return;
}

// Initialiser les variables avec null pour indiquer qu'elles ne sont pas à mettre à jour
$name = isset($data['nom']) ? $data['nom'] : null;
$prenom = isset($data['prenom']) ? $data['prenom'] : null;
$username = isset($data['username']) ? $data['username'] : null;
$role = isset($data['role']) ? $data['role'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$password = isset($data['password']) ? $data['password'] : null;
$telephone = isset($data['telephone']) ? $data['telephone'] : null;
$idSociete = isset($data['id_societe']) ? $data['id_societe'] : null;

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($name === null && $prenom === null && $username === null && $role === null &&
    $email === null && $password === null && $telephone === null && $idSociete === null) {
    returnError(400, 'No data provided for update');
    return;
}

// Vérifications conditionnelles uniquement si les données sont fournies

// Vérifier la longueur du mot de passe uniquement s'il est fourni
if ($password !== null && strlen($password) < 8) {
    returnError(400, 'Password must be at least 8 characters long');
    return;
}

// Vérifier téléphone uniquement s'il est fourni
if ($telephone !== null) {
    $existingEmployeeTelephone = getEmployeeByTelephone($telephone);
    if ($existingEmployeeTelephone && $existingEmployeeTelephone['collaborateur_id'] != $employeeId) {
        returnError(400, 'Telephone already exists');
        return;
    }
}

// Vérifier nom d'utilisateur uniquement s'il est fourni
if ($username !== null) {
    $existingEmployeeUsername = getEmployeeByUsername($username);
    if ($existingEmployeeUsername && $existingEmployeeUsername['collaborateur_id'] != $employeeId) {
        returnError(400, 'Username already exists');
        return;
    }
}

// Vérifier l'email uniquement s'il est fourni
if ($email !== null) {
    $existingEmployeeEmail = getEmployeeByEmail($email);
    if ($existingEmployeeEmail && $existingEmployeeEmail['collaborateur_id'] != $employeeId) {
        returnError(400, 'Email already exists');
        return;
    }
}

// Appeler updateEmployee avec les paramètres corrects
$updateResult = updateEmployee($employeeId, $name, $prenom, $role, $email, $telephone, $idSociete, $username, $password);

// Vérifier le résultat de la mise à jour
if ($updateResult !== null) {
    // Récupérer les données mises à jour pour la confirmation
    $updatedEmployee = getEmployee($employeeId);
    if ($updatedEmployee) {
        // Ne pas inclure le mot de passe dans la réponse
        unset($updatedEmployee['password']);

        // Ajouter un indicateur de succès
        $updatedEmployee['success'] = true;

        echo json_encode($updatedEmployee);
        http_response_code(200);
    } else {
        // Cas rare où la mise à jour a réussi mais la récupération a échoué
        echo json_encode(['success' => true, 'id' => $employeeId]);
        http_response_code(200);
    }
} else {
    returnError(500, 'Failed to update employee');
}
