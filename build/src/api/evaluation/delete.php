<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/evaluation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);


$data = getBody();

if (!validateMandatoryParams($data, ['evaluation_id'])) {
    returnError(400, 'Missing required parameters');
    return;
}
$eval_id = $data['evaluation_id'];



$eval = getEvaluation($eval_id);
if (!$eval) {
    returnError(404, 'evaluation_id not found');
    return;
}


$deletedEval = deleteEvaluation($eval_id);

if (!$deletedEval) {
    returnError(500, 'Could not delete the evaluation. Database operation failed.');
    return;
}

echo json_encode(['evaluation_id' => $eval_id]);
http_response_code(200);
