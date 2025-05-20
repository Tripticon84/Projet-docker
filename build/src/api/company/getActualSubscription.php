<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

// Vérifier que l'ID de la société est fourni
if (!isset($_GET['societe_id'])) {
    returnError(400, 'Company ID is required');
    return;
}

$societe_id = intval($_GET['societe_id']);

$companySubscription = getCompanyActualSubscription($societe_id);
if (!$companySubscription) {
    returnError(404, 'No subscription found');
    return;
}

// Retourner tous les champs incluant les dates du contrat
$result = [
    "frais_id" => $companySubscription['frais_id'],
    "nom" => $companySubscription['nom'],
    "montant" => $companySubscription['montant'],
    "date_creation" => $companySubscription['date_creation'],
    "description" => $companySubscription['description'],
    "date_debut" => $companySubscription['date_debut'],
    "date_fin" => $companySubscription['date_fin'],
    "devis_id" => $companySubscription['devis_id']
];

returnSuccess($result);