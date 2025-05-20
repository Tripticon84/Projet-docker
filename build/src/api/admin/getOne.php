<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

// Vérification de l'ID de l'administrateur
if (!isset($_GET['id'])) {
    returnError(400, 'Admin ID not provided');
    return;
}

$adminId = intval($_GET['id']);
$admin = getAdminById($adminId);

if (!$admin) {
    returnError(404, 'Admin not found');
    return;
}

$result = [
    "id" => $admin['admin_id'],
    "username" => $admin['username']
];

echo json_encode($result);
http_response_code(200);
