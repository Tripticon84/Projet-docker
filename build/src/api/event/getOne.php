<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);


// Vérification de l'ID de l'évènement
if (!isset($_GET['evenement_id']) || empty($_GET['evenement_id'])) {
    returnError(400, 'Event ID not provided');
    return;
}

$eventId = intval($_GET['evenement_id']);
$event = getEvent($eventId);

if (!$event) {
    returnError(404, 'Event not found');
    return;
}

$result = [
    "evenement_id" => $event['evenement_id'],
    "nom" => $event['nom'],
    "date" => $event['date'],
    "lieu" => $event['lieu'],
    "type" => $event['type'],
    "statut" => $event['statut'],
    "id_association" => $event['id_association']
];

echo json_encode($result);
http_response_code(200);
