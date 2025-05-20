<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');


if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);


// Récupération de l'ID depuis l'URL
if (!isset($_GET['societe_id']) || empty($_GET['societe_id'])) {
    returnError(400, 'societe_id parameter is required');
    return;
}

$societeId = $_GET['societe_id'];

// Récupération des informations de l'administrateur
$societe = getSocietyById($societeId);

if (!$societe) {
    returnError(404, 'Company not found');
    return;
}

// On ne renvoie jamais le mot de passe, même hashé
unset($societe['password']);

// Retour des informations de l'administrateur
echo json_encode($societe);
http_response_code(200);
exit();
