<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, true, false); // Admin et employés autorisés

if (!methodIsAllowed('delete')) {
    returnError(405, "Méthode non autorisée");
}

$data = getBody();

// Validation des paramètres obligatoires
if (!validateMandatoryParams($data, ['salon_id', 'collaborateur_id'])) {
    returnError(400, "L'ID du salon et l'ID du collaborateur sont requis");
}

$salon_id = $data['salon_id'];
$collaborateur_id = $data['collaborateur_id'];

// Récupérer le token et l'utilisateur connecté
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) :
         (isset($headers['authorization']) ? str_replace('Bearer ', '', $headers['authorization']) : '');

$employeeUser = getEmployeeByToken($token);
$adminUser = getAdminByToken($token);

// Si c'est un employé (et pas un admin), vérifier qu'il appartient au salon et qu'il se retire lui-même
if ($employeeUser && !$adminUser) {
    if (!isUserInChat($salon_id, $employeeUser['collaborateur_id'])) {
        returnError(403, "Vous n'avez pas accès à ce salon");
    }

    // Un employé ne peut retirer que lui-même
    if ($employeeUser['collaborateur_id'] != $collaborateur_id) {
        returnError(403, "Vous ne pouvez pas retirer un autre utilisateur du salon");
    }
}

$result = removeUserFromChat($salon_id, $collaborateur_id);

if ($result > 0) {
    returnSuccess(['message' => 'Utilisateur retiré du salon avec succès']);
} else {
    returnError(404, "Utilisateur non trouvé dans ce salon");
}
