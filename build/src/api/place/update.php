<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/place.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, true);


$data = getBody();

if (!validateMandatoryParams($data, ['lieu_id'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$place_id = $data['lieu_id'];
if (!getPlaceById($place_id)) {
    returnError(404, 'Place not found');
    return;
}

$adress = isset($data['adresse']) ? $data['adresse'] : null;
$city = isset($data['ville']) ? $data['ville'] : null;
$postalCode = isset($data['code_postal']) ? $data['code_postal'] : null;

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($adress === null && $city === null && $postalCode === null) {
    returnError(400, 'No data provided for update');
    return;
}

$updatedPlace = updatePlace($place_id, $adress, $city, $postalCode);


if (!$updatedPlace) {
    // Log the error for debugging
    error_log("Failed to update place: " . print_r(error_get_last(), true));
    returnError(500, 'Could not update the place. Database operation failed.');
    return;
}
else{
    echo json_encode(['lieu_id' => $place_id]);
    http_response_code(200);
}
?>
