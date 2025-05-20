<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

$data = getBody();

if (!isset($data['devis_id']) || empty($data['devis_id'])) {
    returnError(400, 'Devis ID is required');
    return;
}

$devis_id = $data['devis_id'];

// Vérifier que le devis existe
$estimate = getEstimateById($devis_id);

if (empty($estimate)) {
    returnError(404, 'Devis not found');
    return;
}

// Vérifier que le devis n'est pas déjà un contrat
if ($estimate['is_contract'] == 1) {
    returnError(400, 'Ce devis est déjà un contrat');
    return;
}

// Vérifier que le devis est accepté ou peut être converti
if ($estimate['statut'] != 'accepté' && $estimate['statut'] != 'envoyé') {
    returnError(400, 'Le devis doit être accepté ou envoyé pour être converti en contrat');
    return;
}

$res = convertToContract($devis_id);

if (!$res) {
    returnError(500, 'Failed to convert estimate to contract');
    return;
} else {
    echo json_encode(['success' => "Le devis id : " . $devis_id . " a été converti en contrat avec succès"]);
    http_response_code(200);
}
