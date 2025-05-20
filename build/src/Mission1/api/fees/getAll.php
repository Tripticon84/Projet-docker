<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/fees.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);

$nom = '';
$est_abonnement = null;
$limit = null;
$offset = null;

if (isset($_GET['nom'])) {
    $nom = trim($_GET['nom']);
}
if (isset($_GET['est_abonnement']) && ($_GET['est_abonnement'] === '0' || $_GET['est_abonnement'] === '1')) {
    $est_abonnement = intval($_GET['est_abonnement']);
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

$frais = getAllFrais($nom, $est_abonnement, $limit, $offset);

if (!$frais) {
    echo json_encode([]);
    return;
}

echo json_encode($frais);
