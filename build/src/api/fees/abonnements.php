<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/fees.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);

$abonnements = getAbonnements();

if (!$abonnements) {
    returnError(404, 'No subscriptions found');
    return;
}

echo json_encode($abonnements);
