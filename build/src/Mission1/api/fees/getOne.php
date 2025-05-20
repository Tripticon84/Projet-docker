<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/fees.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);

if (!isset($_GET['frais_id'])) {
    returnError(400, 'Missing id parameter');
    return;
}

$id = intval($_GET['frais_id']);
$frais = getFraisById($id);

if (!$frais) {
    returnError(404, 'Frais not found');
    return;
}

$frais['devis'] = getDevisByFraisId($id);

echo json_encode($frais);
