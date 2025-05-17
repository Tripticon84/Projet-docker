<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/evaluation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


$limit = null;
$offset = null;
$note = null;
$desactivate = null;


if (isset($_GET['note'])) {
    $note = intval($_GET['note']);
    if ($note < 0 || $note > 5) {
        returnError(400, 'Note must be a number between 0 and 5');
    }
}

if (isset($_GET['desactivate'])) {
    $desactivate = intval($_GET['desactivate']);
    if ($desactivate < 0 || $desactivate > 1) {
        returnError(400, 'Desactivate must be a number between 0 and 1');
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

$evals = getAllEvaluation($note, $desactivate, $limit, $offset);

if (!$evals) {
    returnError(404, 'No evaluation found');
    return;
}

$result = []; // Initialize the result array

foreach ($evals as $eval) {
    $result[] = [
        "evaluation_id" => $eval['evaluation_id'],
        "note" => $eval['note'],
        "commentaire" => $eval['commentaire'],
        "id_collaborateur" => $eval['id_collaborateur'],
        "date_creation" => $eval['date_creation'],
        "desactivate" => $eval['desactivate']
    ];
}

if (empty($result)) {
    returnError(404, 'No evaluation found');
    return;
}

echo json_encode($result);
http_response_code(200);
