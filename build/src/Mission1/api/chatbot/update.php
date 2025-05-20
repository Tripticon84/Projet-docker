<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chatbot.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();

if (!validateMandatoryParams($data, ['chatbot_id'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$chatbot_id = $data['chatbot_id'];
$question = isset($data['question']) ? $data['question'] : null;
$answer = isset($data['answer']) ? $data['answer'] : null;
$parent_id = isset($data['parent_id']) ? $data['parent_id'] : null;

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($question === null && $answer === null && $parent_id === null) {
    returnError(400, 'No data provided for update');
    return;
}

$updatedChatbot = updateChatbot($chatbot_id, $question, $answer, $parent_id);

if (!$updatedChatbot) {
    // Log the error for debugging
    error_log("Failed to update chatbot: " . print_r(error_get_last(), true));
    returnError(500, 'Could not update the Chatbot. Database operation failed.');
    return;
}
else{
    echo json_encode(['chatbot_id' => $chatbot_id]);
    http_response_code(200);
}
?>
