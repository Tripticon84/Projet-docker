<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


$name = '';
$limit = null;
$offset = null;
$id_societe = null;

if (isset($_GET['name'])) {
    $name = trim($_GET['name']); // Fix the parameter name
}
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

$societes = getAllSociety($name, $limit, $offset);

if (!$societes) {
    returnError(404, 'No company found');
    return;
}

$result = []; // Initialize the result array

foreach ($societes as $societe) {
    $result[] = [
        "societe_id" => $societe['societe_id'],
        "nom" => $societe['nom'],
        "contact_person" => $societe['contact_person'],
        "adresse" => $societe['adresse'],
        "email" => $societe['email'],
        "telephone" => $societe['telephone'],
        "date_creation" => $societe['date_creation'],
        "siret" => $societe['siret'],
        "desactivate" => $societe['desactivate']
    ];
}

echo json_encode($result);
