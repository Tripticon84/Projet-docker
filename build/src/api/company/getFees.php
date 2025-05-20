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
$fees = getCompanyFees($societe_id);

if (!$fees) {
    returnError(404, 'No costs found');
    return;
}

$result = []; // Initialize the result array

foreach ($fees as $fee) {
    $result[] = [
        "frais_id" => $fee['frais_id'],
        "nom" => $fee['nom'],
        "montant" => $fee['montant'],
        "date_creation" => $fee['date_creation'],
        "description" => $fee['description'],
        "est_abonnement" => $fee['est_abonnement'],
        "devis" => [
            "devis_id" => $fee['devis_id'],
            "date_debut" => $fee['date_debut'],
            "date_fin" => $fee['date_fin'],
            "statut" => $fee['statut']
        ]
    ];
}

echo json_encode($result);
?>
