<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/activity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/place.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();

// Seuls nom, type et date sont obligatoires
if (validateMandatoryParams($data, ['nom', 'type', 'date'])) {

    // Vérification des types pour les valeurs fournies
    if (!is_string($data['nom']) || !is_string($data['type'])) {
        returnError(400, 'Invalid parameter type. nom, type must be strings.');
        return;
    }

    // Initialisation des valeurs par défaut pour les champs optionnels
    $id_devis = isset($data['id_devis']) && $data['id_devis'] ? $data['id_devis'] : null;
    $id_prestataire = isset($data['id_prestataire']) && $data['id_prestataire'] ? $data['id_prestataire'] : null;
    $id_lieu = isset($data['id_lieu']) && $data['id_lieu'] ? $data['id_lieu'] : null;

    // Vérification des types pour les champs optionnels fournis
    if ($id_devis !== null && !is_numeric($id_devis)) {
        returnError(400, 'Invalid parameter type. id_devis must be an integer.');
        return;
    }

    if ($id_prestataire !== null && !is_numeric($id_prestataire)) {
        returnError(400, 'Invalid parameter type. id_prestataire must be an integer.');
        return;
    }

    if ($id_lieu !== null && !is_numeric($id_lieu)) {
        returnError(400, 'Invalid parameter type. id_lieu must be an integer.');
        return;
    }

    // Vérification de l'existence des entités référencées, seulement si les IDs sont fournis
    if ($id_devis) {
        $estimate = getEstimateById($id_devis);
        if (!$estimate) {
            returnError(404, 'Estimate not found');
            return;
        }
    }

    if ($id_prestataire) {
        $provider = getProviderById($id_prestataire);
        if (!$provider) {
            returnError(404, 'Provider not found');
            return;
        }
    }

    if ($id_lieu) {
        $place = getPlaceById($id_lieu);
        if (!$place) {
            returnError(404, 'Place not found');
            return;
        }
    }

    $name = $data['nom'];
    $type = $data['type'];
    $date = $data['date'];

    $newActivityId = createActivity($name, $type, $date, $id_devis, $id_prestataire, $id_lieu);

    if (!$newActivityId) {
        error_log("Failed to create activity: " . print_r(error_get_last(), true));
        returnError(500, 'Could not create the activity. Database operation failed.');
        return;
    }

    echo json_encode(['activity_id' => $newActivityId]);
    http_response_code(201);
} else {
    returnError(400, 'Missing required parameters');
    return;
}
