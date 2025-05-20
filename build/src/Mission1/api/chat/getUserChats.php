<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, true, false); // Admin et employés autorisés

if (!methodIsAllowed('read')) {
    returnError(405, "Méthode non autorisée");
}

// Validation des paramètres obligatoires
if (!validateMandatoryParams($_GET, ['collaborateur_id'])) {
    returnError(400, "L'ID du collaborateur est requis");
}

$collaborateur_id = $_GET['collaborateur_id'];

// Récupérer le token et l'utilisateur connecté
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) :
         (isset($headers['authorization']) ? str_replace('Bearer ', '', $headers['authorization']) : '');

$employeeUser = getEmployeeByToken($token);
$adminUser = getAdminByToken($token);

// Si c'est un employé (et pas un admin), vérifier qu'il consulte ses propres salons
if ($employeeUser && !$adminUser && $employeeUser['collaborateur_id'] != $collaborateur_id) {
    returnError(403, "Vous n'êtes pas autorisé à consulter les salons d'un autre collaborateur");
}

$chats = getUserChats($collaborateur_id);

returnSuccess($chats);
