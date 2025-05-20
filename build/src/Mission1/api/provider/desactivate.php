<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

$data = getBody();

acceptedTokens(true, false, false, false);

if (!isset($data['prestataire_id'])) {
    returnError(400, 'Missing id');
    return;
}

$prestataire_id = $data['prestataire_id'];

// Vérifier que le prestataire existe
$provider = getProviderById($prestataire_id);
if (!$provider) {
    returnError(404, 'Provider not found');
    return;
}

$result = deactivateProvider($prestataire_id);


if ($result) {
    echo json_encode([
        'success' => "Prestataire désactivé avec succès"
    ]);
    return http_response_code(200);
} else {
    returnError(500, "Erreur lors de la modification du statut du prestataire");
}
