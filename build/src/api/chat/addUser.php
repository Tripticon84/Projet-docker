<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, true, false); // Admin et employés autorisés

// Vérifier directement si la méthode HTTP est POST au lieu de methodIsAllowed('create')
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, "Méthode non autorisée");
}

$data = getBody();

if (!validateMandatoryParams($data, ['salon_id', 'collaborateur_id'])) {
    returnError(400, "L'ID du salon et l'ID du collaborateur sont requis");
}

$salon_id = $data['salon_id'];
$collaborateur_id = $data['collaborateur_id'];
$is_admin = isset($data['is_admin']) ? (bool)$data['is_admin'] : false;

// Récupérer le token et l'utilisateur connecté
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) :
         (isset($headers['authorization']) ? str_replace('Bearer ', '', $headers['authorization']) : '');

$employeeUser = getEmployeeByToken($token);
$adminUser = getAdminByToken($token);

// Si c'est un employé (et pas un admin système), vérifier ses droits
if ($employeeUser && !$adminUser) {
    // Un employé ne peut ajouter que lui-même
    if ($employeeUser['collaborateur_id'] != $collaborateur_id) {
        returnError(403, "Vous ne pouvez pas ajouter un autre utilisateur au salon");
    }
    
    // Si l'employé essaie de s'ajouter comme admin sans être admin système
    if ($is_admin) {
        // Vérifier s'il est déjà admin d'un autre salon (première personne à rejoindre)
        $chatUsers = getChatUsers($salon_id);
        if (count($chatUsers) > 0) {
            // Le salon a déjà des membres, vérifier si l'utilisateur est admin du salon
            if (!isUserChatAdmin($salon_id, $employeeUser['collaborateur_id'])) {
                $is_admin = false; // Forcer à false si l'utilisateur n'a pas les droits
            }
        } else {
            // Premier utilisateur à rejoindre le salon => devient admin
            $is_admin = true;
        }
    }
}

// Vérifie si le salon existe
$salon = getChat($salon_id);
if ($salon == null) {
    returnError(404, "Salon introuvable");
}

// Vérifie si le collaborateur existe
$collaborateur = getEmployee($collaborateur_id);
if ($collaborateur === null) {
    returnError(404, "Collaborateur introuvable");
}

// Vérifie si le collaborateur est déjà dans le salon
$collaborateurInSalon = getUserChats($collaborateur_id);
if ($collaborateurInSalon !== null) {
    foreach ($collaborateurInSalon as $chat) {
        if ($chat['salon_id'] == $salon_id) {
            // Si l'utilisateur existe déjà et qu'on veut modifier son statut admin
            if (isset($data['is_admin'])) {
                if (setUserChatAdmin($salon_id, $collaborateur_id, $is_admin)) {
                    returnSuccess(['message' => 'Statut d\'administrateur mis à jour'], 200);
                } else {
                    returnError(500, "Erreur lors de la mise à jour du statut d'administrateur");
                }
            } else {
                returnSuccess(['message' => 'Le collaborateur est déjà dans le salon'], 200);
            }
            exit;
        }
    }
}

$result = addUserToChat($salon_id, $collaborateur_id, $is_admin);

if ($result > 0) {
    returnSuccess(['message' => 'Utilisateur ajouté au salon avec succès'], 201);
} else {
    returnError(500, "Erreur lors de l'ajout de l'utilisateur au salon");
}
