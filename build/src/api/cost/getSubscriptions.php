<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/cost.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

// Get society ID from query params
$societyId = null;
if (isset($_GET['society_id'])) {
    $societyId = intval($_GET['society_id']);
    if ($societyId < 1) {
        returnError(400, 'Society ID must be a positive and non-zero number');
        return;
    }
} else {
    returnError(400, 'Society ID is required');
    return;
}

// Get subscriptions using the DAO function
$subscriptionsData = getAllSubscription($societyId);

// Format the response to match the expected structure
$subscriptions = [];
foreach ($subscriptionsData as $sub) {
    $subscriptions[] = [
        'id' => $sub['frais_id'],
        'name' => $sub['nom'],
        'amount' => $sub['montant'],
        'description' => $sub['description'],
        'created_at' => $sub['date_creation']
    ];
}

echo json_encode($subscriptions);
