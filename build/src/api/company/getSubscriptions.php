<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// Vérification du token (entreprise connectée uniquement)
acceptedTokens(true, true, false, false);

if (!isset($_GET['societe_id'])) {
    returnError(400, 'Missing mandatory parameters');
    return;
}

$societe_id = $_GET['societe_id'];

// Récupérer les frais associés à l'entreprise
$Subscriptions = getCompanySubscriptions($societe_id);

if (!$Subscriptions) {
    returnError(404, 'No costs found');
    return;
}

$result = []; // Initialize the result array

foreach ($Subscriptions as $Subscription) {
    $result[] = [
        "frais_id" => $Subscription['frais_id'],
        "nom" => $Subscription['nom'],
        "montant" => $Subscription['montant'],
        "date_creation" => $Subscription['date_creation'],
        "description" => $Subscription['description'],
        "est_abonnement" => $Subscription['est_abonnement'],
        "devis" => [
            "devis_id" => $Subscription['devis_id'],
            "date_debut" => $Subscription['date_debut'],
            "date_fin" => $Subscription['date_fin'],
            "statut" => $Subscription['statut']
        ]
    ];
}

echo json_encode($result);
?>
