<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, true, false); // Admin et employés autorisés

if (!methodIsAllowed('update')) {
    returnError(405, "Méthode non autorisée");
}

$data = getBody();

if (!validateMandatoryParams($data, ['salon_id'])) {
    returnError(400, "L'ID du salon est requis");
}

$salon_id = $data['salon_id'];
$nom = isset($data['nom']) ? $data['nom'] : null;
$description = isset($data['description']) ? $data['description'] : null;

// Récupérer le token et l'utilisateur connecté
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) :
         (isset($headers['authorization']) ? str_replace('Bearer ', '', $headers['authorization']) : '');

$employeeUser = getEmployeeByToken($token);
$adminUser = getAdminByToken($token);

// Si c'est un employé (et pas un admin), vérifier qu'il appartient au salon
if ($employeeUser && !$adminUser) {
    $collaborateur_id = $employeeUser['collaborateur_id'];
    if (!isUserInChat($salon_id, $collaborateur_id)) {
        returnError(403, "Vous n'avez pas accès à ce salon");
    }
}

$result = updateChat($salon_id, $nom, $description);

if ($result !== null) {
    if ($result > 0) {
        returnSuccess(['message' => 'Salon mis à jour avec succès']);
    } else {
        returnSuccess(['message' => 'Aucune modification effectuée']);
    }
} else {
    returnError(500, "Erreur lors de la mise à jour du salon");
}
