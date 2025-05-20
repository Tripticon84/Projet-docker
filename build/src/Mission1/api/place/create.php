<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/place.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, true);


$data = getBody();

if (validateMandatoryParams($data, ['adresse', 'ville', 'code_postal'])) {


    if (!is_string($data['adresse']) || !is_string($data['ville'])) {
        returnError(400, 'Invalid parameter type. nom, type and lieu must be strings.');
        return;
    }

    if (!is_int($data['code_postal'])) {
        returnError(400, 'Invalid parameter type. code_postal must be an integer.');
        return;
    }




    $adress = $data['adresse'];
    $city = $data['ville'];
    $code_postal = $data['code_postal'];

    $newPlaceId = createPlace($adress, $city, $code_postal);

    if (!$newPlaceId) {
        // Log the error for debugging
        error_log("Failed to create place: " . print_r(error_get_last(), true));
        returnError(500, 'Could not create the place. Database operation failed.');
        return;
    }

    echo json_encode(['lieu_id' => $newPlaceId]);
    http_response_code(201);
}else{
    returnError(400, 'Missing required parameters');
    return;
}
