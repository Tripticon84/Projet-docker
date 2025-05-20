<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';


function getChat($salon_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('SELECT salon_id, nom, description FROM salon WHERE salon_id = :salon_id');
    $params = [
        'salon_id' => $salon_id
    ];
    $query->execute($params);
    return $query->fetch(PDO::FETCH_ASSOC);
}

function getAllChats($limit = null, $offset = null){
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT salon_id, nom, description FROM salon";

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . intval($limit);

        if ($offset !== null) {
            $sql .= " OFFSET " . intval($offset);
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function createChat($nom, $description){
    $db = getDatabaseConnection();
    $query = $db->prepare('INSERT INTO salon (nom, description) VALUES (:nom, :description)');
    $params = [
        'nom' => $nom,
        'description' => $description
    ];
    $query->execute($params);
    return $db->lastInsertId();
}

function updateChat($salon_id, ?string $name = null, ?string $description = null){
    $db = getDatabaseConnection();
    $params = ['salon_id' => $salon_id];
    $setFields = [];

    if ($name !== null) {
        $setFields[] = "nom = :nom";
        $params['nom'] = $name;
    }

    if ($description !== null) {
        $setFields[] = "description = :description";
        $params['description'] = $description;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE salon SET " . implode(", ", $setFields) . " WHERE salon_id = :salon_id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

/**
 * Supprime un salon de discussion
 */
function deleteChat($salon_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('DELETE FROM salon WHERE salon_id = :salon_id');
    $params = [
        'salon_id' => $salon_id
    ];
    $query->execute($params);
    return $query->rowCount();
}

// Fonctions pour la gestion des participants aux salons

/**
 * Ajoute un utilisateur à un salon de discussion
 * @param int $salon_id L'ID du salon
 * @param int $collaborateur_id L'ID du collaborateur
 * @param bool $is_admin Indique si l'utilisateur est administrateur du salon (false par défaut)
 * @return int Nombre de lignes affectées
 */
function addUserToChat($salon_id, $collaborateur_id, $is_admin = false){
    $db = getDatabaseConnection();
    $query = $db->prepare('INSERT INTO discute_dans (id_salon, id_collaborateur, is_admin) VALUES (:salon_id, :collaborateur_id, :is_admin)');
    $params = [
        'salon_id' => $salon_id,
        'collaborateur_id' => $collaborateur_id,
        'is_admin' => $is_admin ? 1 : 0
    ];
    $query->execute($params);
    return $query->rowCount();
}

/**
 * Supprime un utilisateur d'un salon de discussion
 */
function removeUserFromChat($salon_id, $collaborateur_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('DELETE FROM discute_dans WHERE id_salon = :salon_id AND id_collaborateur = :collaborateur_id');
    $params = [
        'salon_id' => $salon_id,
        'collaborateur_id' => $collaborateur_id
    ];
    $query->execute($params);
    return $query->rowCount();
}

/**
 * Supprime tous les utilisateurs d'un salon de discussion
 */
function removeAllUsersFromChat($salon_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('DELETE FROM discute_dans WHERE id_salon = :salon_id');
    $params = [
        'salon_id' => $salon_id
    ];
    $query->execute($params);
    return $query->rowCount();
}

/**
 * Récupère les utilisateurs d'un salon de discussion
 */
function getChatUsers($salon_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('
        SELECT c.collaborateur_id, c.nom, c.prenom, c.username, c.email
        FROM collaborateur c
        JOIN discute_dans d ON c.collaborateur_id = d.id_collaborateur
        WHERE d.id_salon = :salon_id
    ');
    $params = [
        'salon_id' => $salon_id
    ];
    $query->execute($params);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les salons de discussion d'un utilisateur
 */
function getUserChats($collaborateur_id){
    $db = getDatabaseConnection();
    $query = $db->prepare('
        SELECT s.salon_id, s.nom, s.description
        FROM salon s
        JOIN discute_dans d ON s.salon_id = d.id_salon
        WHERE d.id_collaborateur = :collaborateur_id
    ');
    $params = [
        'collaborateur_id' => $collaborateur_id
    ];
    $query->execute($params);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si un utilisateur est dans un salon de discussion
 */
function isUserInChat($salon_id, $collaborateur_id):bool {
    $db = getDatabaseConnection();

    $query = $db->prepare('
        SELECT COUNT(*) FROM discute_dans
        WHERE id_salon = :salon_id AND id_collaborateur = :collaborateur_id
    ');
    $params = [
        'salon_id' => $salon_id,
        'collaborateur_id' => $collaborateur_id
    ];
    $query->execute($params);
    return $query->fetchColumn() ? true : false;
}

/**
 * Vérifie si un utilisateur est administrateur d'un salon
 * @param int $salon_id L'ID du salon
 * @param int $collaborateur_id L'ID du collaborateur
 * @return bool Retourne true si l'utilisateur est administrateur du salon, false sinon
 */
function isUserChatAdmin($salon_id, $collaborateur_id) {
    $db = getDatabaseConnection();
    $query = $db->prepare('
        SELECT is_admin FROM discute_dans 
        WHERE id_salon = :salon_id AND id_collaborateur = :collaborateur_id
    ');
    $params = [
        'salon_id' => $salon_id,
        'collaborateur_id' => $collaborateur_id
    ];
    $query->execute($params);
    return (bool)$query->fetchColumn();
}

/**
 * Définit ou retire les privilèges d'administrateur d'un utilisateur dans un salon
 * @param int $salon_id L'ID du salon
 * @param int $collaborateur_id L'ID du collaborateur
 * @param bool $is_admin Nouvelle valeur pour is_admin
 * @return bool|null Succès de l'opération
 */
function setUserChatAdmin($salon_id, $collaborateur_id, $is_admin = true) {
    $db = getDatabaseConnection();
    $query = $db->prepare('
        UPDATE discute_dans SET is_admin = :is_admin
        WHERE id_salon = :salon_id AND id_collaborateur = :collaborateur_id
    ');
    $params = [
        'salon_id' => $salon_id,
        'collaborateur_id' => $collaborateur_id,
        'is_admin' => $is_admin ? 1 : 0
    ];
    return $query->execute($params);
}

// Fonctions pour la gestion des messages en JSON

/**
 * Enregistre un message dans le fichier JSON
 * @param int $salon_id L'ID du salon
 * @param int $collaborateur_id L'ID du collaborateur qui envoie le message
 * @param string $message Le message à enregistrer
 * @param int|null $timestamp Le timestamp du message (facultatif, sinon utilise time())
 * @return bool|int Retourne le nombre d'octets écrits ou false en cas d'erreur
 */
function saveMessage($salon_id, $collaborateur_id, $message, $timestamp = null){
    if ($timestamp === null) {
        $timestamp = time();
    }

    $messagesFile = $_SERVER['DOCUMENT_ROOT'] . "/data/messages/salon_" . $salon_id . ".json";
    $directory = dirname($messagesFile);

    // Créer le répertoire s'il n'existe pas
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Charger les messages existants ou créer un tableau vide
    $messages = [];
    if (file_exists($messagesFile)) {
        $jsonContent = file_get_contents($messagesFile);
        $messages = json_decode($jsonContent, true) ?: [];
    }

    // Ajouter le nouveau message
    $messages[] = [
        'collaborateur_id' => $collaborateur_id,
        'message' => $message,
        'timestamp' => $timestamp
    ];

    // Sauvegarder le fichier JSON
    return file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
}

/**
 * Récupère les messages d'un salon de discussion
 * @param int $salon_id L'ID du salon
 * @param int|null $limit Le nombre maximum de messages à récupérer (facultatif)
 * @param int $offset Le décalage pour la pagination (facultatif, par défaut 0)
 * @return array Un tableau contenant les messages du salon
 */
function getMessages($salon_id, $limit = null, $offset = 0){
    $messagesFile = $_SERVER['DOCUMENT_ROOT'] . "/data/messages/salon_" . $salon_id . ".json";

    if (!file_exists($messagesFile)) {
        return [];
    }

    $jsonContent = file_get_contents($messagesFile);
    $messages = json_decode($jsonContent, true) ?: [];

    // Trier les messages par timestamp (plus récents en dernier)
    usort($messages, function($a, $b) {
        return $a['timestamp'] - $b['timestamp'];
    });
    
    // Enrichir les messages avec les informations des collaborateurs
    $db = getDatabaseConnection();
    foreach ($messages as &$message) {
        $query = $db->prepare('
            SELECT CONCAT(prenom, " ", nom) as username 
            FROM collaborateur 
            WHERE collaborateur_id = :collaborateur_id
        ');
        $query->execute(['collaborateur_id' => $message['collaborateur_id']]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $message['username'] = $user ? $user['username'] : 'Utilisateur inconnu';
    }

    // Appliquer limit et offset
    if ($limit !== null) {
        return array_slice($messages, $offset, $limit);
    }

    return array_slice($messages, $offset);
}

/**
 * Récupère les derniers messages d'un salon de discussion
 * @param int $salon_id L'ID du salon
 * @param int $count Le nombre de messages à récupérer (par défaut 20)
 * @return array Un tableau contenant les derniers messages du salon
 */
function getLatestMessages($salon_id, $count = 20){
    $messagesFile = $_SERVER['DOCUMENT_ROOT'] . "/data/messages/salon_" . $salon_id . ".json";

    if (!file_exists($messagesFile)) {
        return [];
    }

    $jsonContent = file_get_contents($messagesFile);
    $messages = json_decode($jsonContent, true) ?: [];

    // Trier les messages par timestamp (les plus récents en premier)
    usort($messages, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    // Enrichir les messages avec les informations des collaborateurs
    $db = getDatabaseConnection();
    foreach ($messages as &$message) {
        $query = $db->prepare('
            SELECT CONCAT(prenom, " ", nom) as username 
            FROM collaborateur 
            WHERE collaborateur_id = :collaborateur_id
        ');
        $query->execute(['collaborateur_id' => $message['collaborateur_id']]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $message['username'] = $user ? $user['username'] : 'Utilisateur inconnu';
    }

    // Retourner directement les $count messages les plus récents
    return array_slice($messages, 0, $count);
}
