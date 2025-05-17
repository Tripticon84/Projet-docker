<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);

$limit = null;
$offset = null;

if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'Limit must be a positive and non zero number');
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'Offset must be a positive number');
    }
}

$events = getAllEvents( $limit, $offset);

$result = [];

foreach ($events as $event) {
    $result[] = [
        "evenement_id" => $event['evenement_id'],
        "nom" => $event['nom'],
        "lieu" => $event['lieu'],
        "date" => $event['date'],
        "type" => $event['type'],
        "statut" => $event['statut'],
        "id_association" => $event['id_association']
    ];
}

echo json_encode($result);
