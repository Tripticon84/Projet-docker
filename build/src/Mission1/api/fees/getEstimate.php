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
    returnError(400, 'Missing frais_id parameter');
    return;
}

$fraisId = intval($_GET['frais_id']);
$devis = getDevisByFraisId($fraisId);

if (!$devis) {
    echo json_encode([]);
    return;
}

echo json_encode($devis);
