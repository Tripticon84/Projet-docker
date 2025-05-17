<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/estimate.php";

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// acceptedTokens(true, true, false, true);

// Récupération de l'ID du prestataire depuis la requête
$provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : null;

// Vérification que l'ID du prestataire est fourni
if ($provider_id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'ID du prestataire manquant']);
    exit;
}

// Récupération des contrats pour ce prestataire
$contracts = getContractByProvider($provider_id);

// Vérification si des contrats ont été trouvés
if ($contracts === null) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Erreur lors de la récupération des contrats']);
    exit;
}

// Retourne les contrats au format JSON
header('Content-Type: application/json');
echo json_encode($contracts);
exit;
