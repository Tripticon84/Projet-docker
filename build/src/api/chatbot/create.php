<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chatbot.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();

if (!validateMandatoryParams($data, ['question', 'answer'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$question = $data['question'];
$answer = $data['answer'];

// Vérifier si c'est une sous-question
if (isset($data['parent_id']) && !empty($data['parent_id'])) {
    $parent_id = $data['parent_id'];

    // Vérifier que le parent existe
    $parentChatbot = getChatbot($parent_id);
    if (!$parentChatbot) {
        returnError(404, 'Parent question not found');
        return;
    }

    // Créer la sous-question
    $chatbot_id = createSubChatbot($question, $answer, $parent_id);
} else {
    // Créer une question principale
    $chatbot_id = createChatbot($question, $answer);
}

if (!$chatbot_id) {
    returnError(500, 'Internal Server Error');
    return;
} else {
    echo json_encode(['chatbot_id' => $chatbot_id]);
    http_response_code(201);
    exit();
}
