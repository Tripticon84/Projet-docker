<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');


if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


// Récupération de l'ID depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    returnError(400, 'ID parameter is required');
    return;
}

$adminId = $_GET['id'];

// Récupération des informations de l'administrateur
$admin = getAdminById($adminId);

if (!$admin) {
    returnError(404, 'Admin not found');
    return;
}

// On ne renvoie jamais le mot de passe, même hashé
unset($admin['password']);

// Retour des informations de l'administrateur
echo json_encode($admin);
http_response_code(200);
exit();
