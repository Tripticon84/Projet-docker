<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit();
}

acceptedTokens(true, false, true, false);

// Vérification de l'ID de l'évènement
if (!isset($_GET['id']) || empty($_GET['id'])) {
    returnError(400, 'Event ID not provided');
    return;
}

$eventId = intval($_GET['id']);
$participants = getCollaborateursByEvent($eventId);

if (!$participants || empty($participants)) {
    returnError(404, 'No participants found for this event');
    return;
}

// No need to transform the data as it's already in the correct format
echo json_encode($participants);
http_response_code(200);

?>
