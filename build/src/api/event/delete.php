<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';


header('Content-Type: application/json');

if (!methodIsAllowed('delete')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();
$evenement_id = $data['evenement_id'];

if (validateMandatoryParams($data, ['evenement_id'])) {

    // Vérifier l'id existe
    $event = getEventById($evenement_id);

    if (empty($event)) {
        returnError(400, 'Event does not exist');
        return;
    }


    $res = deleteEvent($evenement_id);

    if (!$res) {
        returnError(500, 'Could not delete the Event');
        return;
    }

    echo json_encode(['evenement_id' => $evenement_id]);
    http_response_code(200);
    exit;
} else {
    returnError(412, 'Mandatory parameters: id');
}
