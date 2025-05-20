<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/activity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}



//acceptedTokens(true, true, true, true);


$limit = null;
$offset = null;
$desactivate = null;
$search = null;

if (isset($_GET['desactivate'])) {
    $desactivate = intval($_GET['desactivate']);
    if ($desactivate < 0 || $desactivate > 1) {
        returnError(400, 'Desactivate must be 0 or 1');
    }
}

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

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
}
$activities = getAllActivity($desactivate, $limit, $offset, $search);

if (!$activities) {
    returnError(500, 'Failed to retrieve activities');
    return;
}

$result = [];
foreach ($activities as $activity) {
    $result[] = [
        "id" => $activity['activite_id'],
        "nom" => $activity['nom'],
        "type" => $activity['type'],
        "date" => $activity['date'],
        "id_devis" => $activity['id_devis'],
        "id_prestataire" => $activity['id_prestataire'],
        "id_lieu" => $activity['id_lieu'],
        "desactivate" => $activity['desactivate'],
    ];
}
echo json_encode($result);
