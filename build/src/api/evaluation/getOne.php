<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/evaluation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, true);


// Vérification de  l evaluation_id
if (!isset($_GET['evaluation_id']) || empty($_GET['evaluation_id'])) {
    returnError(400, 'evaluation_id  not provided');
    return;
}

$evalId = intval($_GET['evaluation_id']);
$eval = getEvaluation($evalId);

if (!$eval) {
    returnError(404, 'evaluation not found');
    return;
}

$result = [
    "evaluation_id" => $eval['evaluation_id'],
    "note" => $eval['note'],
    "commentaire" => $eval['commentaire'],
    "id_collaborateur" => $eval['id_collaborateur'],
    "date_creation" => $eval['date_creation'],
    "desactivate" => $eval['desactivate']
];

if (empty($result)) {
    returnError(404, 'No chatbot found');
    return;
}

echo json_encode($result);
http_response_code(200);
