<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, true, false); // Admin et employés autorisés

// Vérifier si la méthode HTTP est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, "Méthode non autorisée");
}

$data = getBody();

if (!validateMandatoryParams($data, ['salon_id', 'collaborateur_id', 'message'])) {
    returnError(400, "L'ID du salon, l'ID du collaborateur et le message sont requis");
}

$salon_id = $data['salon_id'];
$collaborateur_id = $data['collaborateur_id'];
$message = htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8');
$timestamp = time();

// Récupérer le token et l'utilisateur connecté
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) :
         (isset($headers['authorization']) ? str_replace('Bearer ', '', $headers['authorization']) : '');

$employeeUser = getEmployeeByToken($token);
$adminUser = getAdminByToken($token);

// Vérifier que l'utilisateur est dans le salon
if (!isUserInChat($salon_id, $collaborateur_id)) {
    returnError(403, "Cet utilisateur n'appartient pas au salon");
}

// Si c'est un employé (et pas un admin), vérifier qu'il envoie le message en son nom
if ($employeeUser && !$adminUser && $employeeUser['collaborateur_id'] != $collaborateur_id) {
    returnError(403, "Vous ne pouvez pas envoyer un message au nom d'un autre utilisateur");
}

$result = saveMessage($salon_id, $collaborateur_id, $message, $timestamp);

if ($result) {
    returnSuccess(['message' => 'Message envoyé avec succès'], 201);
} else {
    returnError(500, "Erreur lors de l'envoi du message");
}
