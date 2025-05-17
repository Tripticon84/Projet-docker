<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/association.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

$data = getBody();

acceptedTokens(true, false, false, false);

$name = $data['name'];
$description = $data['description'];
$logo = isset($data['logo']) ? $data['logo'] : null;
$banniere = isset($data['banniere']) ? $data['banniere'] : null;

if (validateMandatoryParams($data, ['name', 'description'])) {

    $association = getAssociationByName($data['name']);
    if (!empty($association)) {
        returnError(400, 'Association already exist');
        return;
    }
    
    $newAssociationId = createAssociation($data['name'], $data['description'], $logo, $banniere);

    if (!$newAssociationId) {
        returnError(500, 'Could not create the association');
        return;
    }

    echo json_encode(['associationid' => $newAssociationId]);
    http_response_code(201);
    exit();

} else {
    returnError(412, 'Mandatory parameters: name, description');
}
