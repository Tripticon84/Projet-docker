<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    returnError(400, 'Provider ID is required');
    return;
}

// Récupérer le prestataire par son ID
$providerId = $_GET['id'];
$provider = getProviderById($providerId);

// Vérifier si le prestataire existe
if (!$provider) {
    returnError(404, 'Provider not found');
    return;
}

// Masquer le mot de passe dans la réponse
if (isset($provider['password'])) {
    unset($provider['password']);
}

// Renvoyer les données du prestataire
echo json_encode($provider);
http_response_code(200);
