<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
header("Content-Type: application/json");

if (!methodIsAllowed('login')) {
    returnError(405, 'Method not allowed');
}

$data = getBody();

if (!validateMandatoryParams($data, ['token'])) {
    returnError(400, 'Mandatory parameter : token');
}

$token = $data['token'];

$expiration = getExpirationByToken($token);

if (!$expiration) {
    returnError(402, "Invalid token");
}

// Vérifier si le token est expiré
$expirationDate = new DateTime($expiration['expiration']);
$now = new DateTime();

if ($now > $expirationDate) {
    returnError(401, "Token expired");
}

// Si le token est valide, récupérer les informations du fournisseur
$provider = getProviderByToken($token);
if (!$provider) {
    returnError(402, "Provider not found");
}

returnSuccess([
    'provider' => [
        'id' => $provider['prestataire_id'],
        'username' => $provider['username']
    ]
]);
