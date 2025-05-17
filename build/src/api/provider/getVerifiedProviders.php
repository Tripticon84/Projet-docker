<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


$limit = null;
$offset = null;
$search = null;

if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'Limit must be a positive and non zero number');
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'Offset must be a positive number');
    }
}

// Récupération du paramètre de recherche
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
}

$providers = getAllProvider($limit, $offset, $search);

$result = []; // Initialize the result array

foreach ($providers as $provider) {
    $result[] = [
        "id" => $provider['prestataire_id'],
        "email" => $provider['email'],
        "name" => $provider['nom'],
        "surname" => $provider['prenom'],
        "start_date" => $provider['date_debut_disponibilite'],
        "end_date" => $provider['date_fin_disponibilite'],
        "type" => $provider['type'],
        "price"=> $provider['tarif']
    ];
}

echo json_encode($result);
