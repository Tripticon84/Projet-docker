<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';


function createChatbot($question, $answer){
    $db = getDatabaseConnection();
    $query = $db->prepare('INSERT INTO chatbot (question, reponse) VALUES (:question, :answer)');
    $params = [
        'question' => $question,
        'answer' => $answer
    ];
    $query->execute($params);
    return $db->lastInsertId();
}

function createSubChatbot($question, $answer, $parent_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('INSERT INTO chatbot (question, reponse, parent_id) VALUES (:question, :answer, :parent_id)');
    $params = [
        'question' => $question,
        'answer' => $answer,
        'parent_id' => $parent_id
    ];
    $query->execute($params);
    return $db->lastInsertId();
}

function deleteChatbot($chatbot_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('DELETE FROM chatbot WHERE question_id = :chatbot_id');
    $params = [
        'chatbot_id' => $chatbot_id
    ];
    $query->execute($params);
    return $query->rowCount();
}

function getChatbot($chatbot_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('SELECT question_id, question, reponse, parent_id FROM chatbot WHERE question_id = :chatbot_id');
    $params = [
        'chatbot_id' => $chatbot_id
    ];
    $query->execute($params);
    return $query->fetch();
}

function getAllChatbots($limit = null, $offset = null){
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT question_id, question, reponse, parent_id FROM chatbot";

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }


    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function updateChatbot($chatbot_id, ?string $question = null, ?string $answer = null, $parent_id = null){
    $db = getDatabaseConnection();
    $params = ['chatbot_id' => $chatbot_id];
    $setFields = [];

    if ($question !== null) {
        $setFields[] = "question = :question";
        $params['question'] = $question;
    }

    if ($answer !== null) {
        $setFields[] = "reponse = :answer";
        $params['answer'] = $answer;
    }
    if ($parent_id !== null) {
        $setFields[] = "parent_id = :parent_id";
        $params['parent_id'] = $parent_id;
    }

    if ($parent_id !== null) {
        $setFields[] = "parent_id = :parent_id";
        $params['parent_id'] = $parent_id;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE chatbot SET " . implode(", ", $setFields) . " WHERE question_id = :chatbot_id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function getParentChatbot($chatbot_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('SELECT question_id,question,reponse, parent_id FROM chatbot WHERE question_id = :chatbot_id');
    $params = [
        'chatbot_id' => $chatbot_id
    ];
    $query->execute($params);
    return $query->fetch();
}

function getSubQuestions($parent_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('SELECT question_id, question, reponse, parent_id FROM chatbot WHERE parent_id = :parent_id');
    $params = ['parent_id' => $parent_id];
    $query->execute($params);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getAllChatbotsByParent($parent_id, $limit = null, $offset = null){
    $db = getDatabaseConnection();
    $params = ['parent_id' => $parent_id];
    $sql = "SELECT question_id, question, reponse FROM chatbot WHERE parent_id = :parent_id";

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getInitialQuestion($chatbot_id){
    $db = getDatabaseConnection();

    while ($chatbot_id) { //car on ne sait pas combien de parents il y a
        // On récupère la question et son parent
        $query = $db->prepare('SELECT question_id, parent_id, question, reponse FROM chatbot WHERE question_id = :chatbot_id');
        $query->execute(['chatbot_id' => $chatbot_id]);
        $question = $query->fetch(PDO::FETCH_ASSOC);
        if (!$question || !$question['parent_id']) {
            return $question; // On a trouvé la question initiale
        }

        $chatbot_id = $question['parent_id']; // Remonter au parent
    }
    return null;
}
