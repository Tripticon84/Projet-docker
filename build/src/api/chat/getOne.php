<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, true, false); // Admin et employés autorisés

if (!methodIsAllowed('read')) {
    returnError(405, "Méthode non autorisée");
}

if (!validateMandatoryParams($_GET, ['salon_id'])) {
    returnError(400, "L'ID du salon est requis");
}

$salon_id = $_GET['salon_id'];

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

$chat = getChat($salon_id);

if ($chat) {
    returnSuccess($chat);
} else {
    returnError(404, "Salon non trouvé");
}
