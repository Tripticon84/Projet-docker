<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

$idSociete = intval($_GET['societe_id']);
$is_contract = true;

// Récupérer les paramètres de filtrage
$statut = isset($_GET['statut']) ? $_GET['statut'] : null;
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : null;
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : null;

$company = getSocietyById($idSociete);

if (!$company) {
    returnError(404, 'Company not found');
    return;
}

$estimates = getCompanyEstimate($idSociete, $is_contract, $statut, $date_debut, $date_fin);

if (!$estimates) {
    returnError(404, 'Estimates not found');
    return;
}

$result = []; // Initialize the result array

foreach ($estimates as $estimate) {
    $result[] = [
        "devis_id" => $estimate['devis_id'],
        "date_debut" => $estimate['date_debut'],
        "date_fin" => $estimate['date_fin'],
        "statut" => $estimate['statut'],
        "montant" => $estimate['montant'],
        "montant_ht" => $estimate['montant_ht'],
        "montant_tva" => $estimate['montant_tva'],
        "fichier" => $estimate['fichier']
    ];
}

if (empty($result)) {
    returnError(404, 'No estimates found');
    return;
}

echo json_encode($result);
