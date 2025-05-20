<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

// Vérification de l'ID du prestataire
if (!isset($_GET['prestataire_id'])) {
    returnError(400, 'prestataire_id not provided');
    return;
}

$id = intval($_GET['prestataire_id']);
$provider = getProviderById($id);

// Vérifier si le prestataire existe
if (!$provider) {
    returnError(404, 'provider not found');
    return;
}

// Masquer le mot de passe dans la réponse
if (isset($provider['password'])) {
    unset($provider['password']);
}

// Renvoyer les données du prestataire
echo json_encode($provider);
http_response_code(200);
