<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

$limit = null;
$offset = null;

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

$Estimates = getAllEstimate($limit, $offset);

if (!$Estimates) {
    returnError(404, 'No estimate found');
    return;
}

$result = []; // Initialize the result array
foreach ($Estimates as $Estimate) {
    $result[] = [
        "devis_id" => $Estimate['devis_id'],
        "date_debut" => $Estimate['date_debut'],
        "date_fin" => $Estimate['date_fin'],
        "statut" => $Estimate['statut'],
        "montant" => $Estimate['montant'],
        "montant_tva" => $Estimate['montant_tva'],
        "montant_ht" => $Estimate['montant_ht'],
        "is_contract" => $Estimate['is_contract'],
        "fichier" => $Estimate['fichier'],
        "id_societe" => $Estimate['id_societe']
    ];
}


echo json_encode($result);
