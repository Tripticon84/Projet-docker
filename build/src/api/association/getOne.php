<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/association.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

// Vérification de l'ID de l'association
if (!isset($_GET['association_id'])) { // Correction du paramètre attendu
    returnError(400, 'Association ID not provided');
    return;
}

$associationId = intval($_GET['association_id']); // Utilisation du paramètre corrigé
$association = getAssociationById($associationId);

if (!$association) {
    returnError(404, 'Association not found');
    return;
}

$result = [
    "id" => $association['association_id'],
    "name" => $association['name'],
    "description" => $association['description'],
    "logo" => $association['logo'],
    "banniere" => $association['banniere'],
    "date_creation" => $association['date_creation'],
    "desactivate" => $association['desactivate']
];

echo json_encode($result);
http_response_code(200);
