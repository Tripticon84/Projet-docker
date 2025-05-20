<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

// acceptedTokens(true, true, false, true);


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

$contracts = getAllContractExpired($limit, $offset);

if (!$contracts) {
    returnError(404, 'No estimate found');
    return;
}

$result = [];
foreach ($contracts as $contract) {
    $result[] = [
        "devis_id" => $contract['devis_id'],
        "date_debut" => $contract['date_debut'],
        "date_fin" => $contract['date_fin'],
        "statut" => $contract['statut'],
        "montant" => $contract['montant'],
        "montant_tva" => $contract['montant_tva'],
        "montant_ht" => $contract['montant_ht'],
        "is_contract" => $contract['is_contract'],
        "fichier" => $contract['fichier'],
        "id_societe" => $contract['id_societe']
    ];
}


echo json_encode($result);
