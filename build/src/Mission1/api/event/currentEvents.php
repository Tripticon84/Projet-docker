<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);

$events = getEventByStatut('en_cours');

$result = [];

foreach ($events as $event) {
    $result[] = [
        "evenement_id" => $event['evenement_id'],
        "nom" => $event['nom'],
        "date" => $event['date'],
        "lieu" => $event['lieu'],
        "type" => $event['type'],
        "statut" => $event['statut'],
        "id_association" => $event['id_association']
    ];
}

echo json_encode($result);
