<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chatbot.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('delete')) {
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


$chatbot = getChatbot($chatbot_id);
if (!$chatbot) {
    returnError(404, 'Chatbot not found');
    return;
}


$deletedChatbot = deleteChatbot($chatbot_id);

if (!$deletedChatbot) {
    returnError(500, 'Could not delete the Chatbot. Database operation failed.');
    return;
}

echo json_encode(['chatbot_id' => $chatbot_id]);
http_response_code(200);
