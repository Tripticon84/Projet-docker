<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/evaluation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);


// Vérification de  l evaluation_id
if (!isset($_GET['collaborateur_id']) || empty($_GET['collaborateur_id'])) {
    returnError(400, 'collaborateur_id  not provided');
    return;
}

$collabId = intval($_GET['collaborateur_id']);
$evals = getEvaluationByCollaboratorId($collabId);

if (!$evals) {
    returnError(404, 'evaluation not found');
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
