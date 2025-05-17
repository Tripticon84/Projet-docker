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

// Vérification des paramètres obligatoires
if (!isset($data['devis_id']) || empty($data['devis_id'])) {
    returnError(400, 'Devis ID is required');
    return;
}

if (!isset($data['frais_ids']) || !is_array($data['frais_ids'])) {
    returnError(400, 'frais_ids must be an array');
    return;
}

$devis_id = $data['devis_id'];
$frais_ids = $data['frais_ids'];

// Vérifier que le devis existe
$estimate = getEstimateById($devis_id);
if (empty($estimate)) {
    returnError(404, 'Devis not found');
    return;
}

// Attacher les frais au devis
$res = attachFraisToEstimate($devis_id, $frais_ids);

if (!$res) {
    returnError(500, 'Failed to attach frais to estimate');
    return;
}

// Récupérer les frais mis à jour
$updatedEstimate = getEstimateById($devis_id);
echo json_encode([
    'success' => true,
    'message' => "Les frais ont été associés au devis avec succès",
    'estimate' => $updatedEstimate
]);
http_response_code(200);
