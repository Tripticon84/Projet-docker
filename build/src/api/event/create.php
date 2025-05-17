<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/association.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();

if (validateMandatoryParams($data, ['nom', 'date', 'lieu', 'type', 'statut', 'id_association'])) {

    if (!is_numeric($data['id_association'])) {
        returnError(400, 'id_association must be a number');
        return;
    }
    if (!is_string($data['nom']) || !is_string($data['lieu']) || !is_string($data['type'])) {
        returnError(400, 'nom, lieu and type must be strings');
        return;
    }
    if (!DateTime::createFromFormat('Y-m-d', $data['date'])) {
        returnError(400, 'date must be in the format Y-m-d');
        return;
    }

    $association = getAssociationById($data['id_association']);
    if (empty($association)) {
        returnError(400, 'association does not exist');
        return;
    }


    $newEventId = createEvent($data['nom'], $data['date'], $data['lieu'], $data['type'], $data['statut'], $data['id_association']);

    if (!$newEventId) {
        returnError(500, 'Could not create the event');
        return;
    }

    echo json_encode(['id' => $newEventId]);
    http_response_code(201);
    exit;

} else {
    returnError(412, 'Mandatory parameters : nom, date, lieu, type, statut, id_association');
}
