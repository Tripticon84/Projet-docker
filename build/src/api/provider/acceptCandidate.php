<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';


header('Content-Type: application/json');


if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();


if (validateMandatoryParams($data, ['est_candidat','prestataire_id'])) {
    // Vérification si l'admin existe déjà
    $provider = getProviderById($data['prestataire_id']);
    if (empty($provider)) {
        returnError(400, 'Provider don\'t exist');
        return;
    }

    //vérif si est_candidat est bien true
    if($data['est_candidat']!=true){
        returnError(400,'est déja prestataire.');
        return;
    }
    // Création de l'administrateur
    $newProvider = updateCandidateStatus($data['prestataire_id'],0);

    if (!$newProvider) {
        returnError(500, 'Could not accept the candidate');
        return;
    }

    echo json_encode(['prestataire' => $newProvider]);
    http_response_code(response_code: 201);
    exit();

} else {
    returnError(412, 'Mandatory parameters: est_candidat, prestataire_id');
}
