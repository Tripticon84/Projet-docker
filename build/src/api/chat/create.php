<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification - maintenant on accepte aussi les employés
// car on va vérifier manuellement s'ils sont admin d'un salon
acceptedTokens(true, false, true, false);  // Admin système et employés autorisés

if (!methodIsAllowed('create')) {
    returnError(405, "Méthode non autorisée");
}

$data = getBody();

// Validation des paramètres obligatoires
if (!validateMandatoryParams($data, ['nom', 'description'])) {
    returnError(400, "Le nom et la description sont requis");
}

$nom = $data['nom'];
$description = $data['description'];

// Récupérer le token et l'utilisateur connecté
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) :
         (isset($headers['authorization']) ? str_replace('Bearer ', '', $headers['authorization']) : '');

$employeeUser = getEmployeeByToken($token);
$adminUser = getAdminByToken($token);

// Si c'est un employé et non un admin système, vérifier s'il est admin d'au moins un salon
if ($employeeUser && !$adminUser) {
    // Récupérer tous les salons de cet employé
    $userChats = getUserChats($employeeUser['collaborateur_id']);
    $isAdmin = false;
    
    // Vérifier pour chaque salon si l'employé est admin
    foreach ($userChats as $chat) {
        if (isUserChatAdmin($chat['salon_id'], $employeeUser['collaborateur_id'])) {
            $isAdmin = true;
            break;
        }
    }
    
    // Si l'employé n'est pas admin d'au moins un salon, refuser l'accès
    if (!$isAdmin) {
        returnError(403, "Vous n'avez pas les droits pour créer un salon");
    }
}

// Créer le salon
$chat_id = createChat($nom, $description);

if ($chat_id) {
    returnSuccess([
        'message' => 'Salon créé avec succès',
        'salon_id' => $chat_id
    ], 201);
} else {
    returnError(500, "Erreur lors de la création du salon");
}
