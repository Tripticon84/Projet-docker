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
$costs = getCompanyOtherCost($societe_id);

if (!$costs) {
    returnError(404, 'No costs found');
    return;
}

$result = []; // Initialize the result array

foreach ($costs as $cost) {
    $result[] = [
        "frais_id" => $cost['frais_id'],
        "nom" => $cost['nom'],
        "montant" => $cost['montant'],
        "date_creation" => $cost['date_creation'],
        "description" => $cost['description'],
        "est_abonnement" => $cost['est_abonnement'],
        "devis" => [
            "devis_id" => $cost['devis_id'],
            "date_debut" => $cost['date_debut'],
            "date_fin" => $cost['date_fin'],
            "statut" => $cost['statut']
        ]
    ];
}

echo json_encode($result);
?>
