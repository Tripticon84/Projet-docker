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

$evaluation_id = $data['evaluation_id'];
$note = isset($data['note']) ? $data['note'] : null;
$commentaire = isset($data['commentaire']) ? $data['commentaire'] : null;
$date_creation = isset($data['date_creation']) ? $data['date_creation'] : null;
$collaborateur_id = isset($data['collaborateur_id']) ? $data['collaborateur_id'] : null;
$desactivate = isset($data['desactivate']) ? $data['desactivate'] : null;

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($note === null && $commentaire === null && $date_creation === null && $collaborateur_id === null && $desactivate === null) {
    returnError(400, 'No data provided for update');
    return;
}

$updatedEvaluation = updateEvaluation($evaluation_id, $note, $commentaire,  $collaborateur_id, $date_creation, $desactivate);

if (!$updatedEvaluation) {
    // Log the error for debugging
    error_log("Failed to update evaluation: " . print_r(error_get_last(), true));
    returnError(500, 'Could not update the Evaluation. Database operation failed.');
    return;
} else {
    echo json_encode(['evaluation_id' => $evaluation_id]);
    http_response_code(200);
}
