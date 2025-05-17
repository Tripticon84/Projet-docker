<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chatbot.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);

if (!isset($_GET['parent_id'])) {
    returnError(400, 'parent_id is required');
    return;
}

$parent_id = $_GET['parent_id'];

$chatbots = getSubQuestions($parent_id);

if (!$chatbots) {
    returnError(404, 'No chatbot found');
    return;
}

$result = []; // Initialize the result array

foreach ($chatbots as $chatbot) {
    $result[] = [
        "chatbot_id" => $chatbot['question_id'],
        "question" => $chatbot['question'],
        "answer" => $chatbot['reponse'],
        "parent_id" => $chatbot['parent_id']
    ];
}

if (empty($result)) {
    returnError(404, 'No chatbot found');
    return;
}

echo json_encode($result);
http_response_code(200);
