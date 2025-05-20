<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chatbot.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);

if (!isset($_GET['chatbot_id'])) {
    returnError(400, 'chatbot_id is required');
    return;
}

$chatbotId = $_GET['chatbot_id'];

$chatbot = getInitialQuestion($chatbotId);

if (!$chatbot) {
    returnError(404, 'chatbot not found');
    return;
}
$result = [
    "chatbot_id" => $chatbot['question_id'],
    "question" => $chatbot['question'],
    "answer" => $chatbot['reponse'],
    "parent_id" => $chatbot['parent_id']
];

if (empty($result)) {
    returnError(404, 'No chatbot found');
    return;
}

echo json_encode($result);
http_response_code(200);
