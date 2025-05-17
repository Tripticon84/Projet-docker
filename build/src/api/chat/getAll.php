<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification - seuls les admins peuvent voir tous les salons
acceptedTokens(true, false, false, false); // Admin seulement

if (!methodIsAllowed('read')) {
    returnError(405, "Méthode non autorisée");
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : null;

$chats = getAllChats($limit, $offset);

if ($chats !== null) {
    returnSuccess($chats);
} else {
    returnError(500, "Erreur lors de la récupération des salons");
}
