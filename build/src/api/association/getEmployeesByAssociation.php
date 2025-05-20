<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/association.php";

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Méthode non autorisée');
    return;
}

acceptedTokens(true, false, false, false);


// Récupérer l'ID de l'association depuis les paramètres
if (!isset($_GET['association_id'])) {
    returnError(400, 'ID d\'association non spécifié');
    return;
}

$association_id = intval($_GET['association_id']);
$limit = null;
$offset = null;

// Ajout de la pagination
if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'La limite doit être un nombre positif non nul');
        return;
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'L\'offset doit être un nombre positif');
        return;
    }
}

// Vérifier si l'association existe
$association = getAssociationById($association_id);
if (!$association) {
    returnError(404, 'Association non trouvée');
    return;
}

// Récupérer les employés de l'association
// Modification de la fonction pour prendre en compte limit et offset
$employees = getEmployeesByAssociation($association_id, $limit, $offset);

if (!$employees) {
    returnError(404, 'Aucun employé trouvé pour cette association');
    return;
}

$result = [
    'association' => $association,
    'employees' => $employees
];

echo json_encode($result);
http_response_code(200);
