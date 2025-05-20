<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/fees.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérifier que la méthode HTTP est bien GET
if (!methodIsAllowed('get')) {
    returnError(405, 'Method not allowed');
    return;
}

// Vérifier l'authentification via le token
acceptedTokens(true, true, false, true);

// Récupérer l'ID du devis depuis les paramètres GET
if (!isset($_GET['devis_id']) || empty($_GET['devis_id']) || !is_numeric($_GET['devis_id'])) {
    returnError(400, 'Missing or invalid estimate ID parameter');
    return;
}

$devisId = intval($_GET['devis_id']);

// Récupérer les frais associés au devis
$fees = getFraisByEstimateId($devisId);

if ($fees === null) {
    returnError(500, 'Error retrieving fees for this estimate');
    return;
}

// Si aucun frais n'est trouvé, retourner un tableau vide
if (empty($fees)) {
    echo json_encode([]);
    return;
}

// Retourner les données au format JSON
echo json_encode($fees);
