<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

// Récupérer les données de la requête
$data = getBody();

acceptedTokens(true, false, false, false);


// Vérifier si l'ID est présent
if (!isset($data['id']) || empty($data['id'])) {
    returnError(400, 'Admin ID is required');
    return;
}

// Récupérer l'administrateur par son ID
$adminId = $data['id'];
$existingAdmin = getAdminById($adminId);

// Vérifier si l'administrateur existe
if (!$existingAdmin) {
    returnError(404, 'Admin not found');
    return;
}

// Variables pour stocker les résultats
$username = isset($data['username']) ? $data['username'] : null;
$password = isset($data['password']) ? $data['password'] : null;
$updateResult = false;

// Cas 1: Mise à jour du nom d'utilisateur et du mot de passe
if ($username && $password) {
    // Vérifier la longueur du mot de passe
    if (strlen($password) < 8) {
        returnError(400, 'Password must be at least 8 characters long');
        return;
    }

    // Vérifier si le nom d'utilisateur existe déjà pour un autre admin
    $existingUser = getAdminByUsername($username);
    if ($existingUser && $existingUser['admin_id'] != $adminId) {
        returnError(400, 'Username already exists');
        return;
    }

    $updateResult = updateAdmin($adminId, $username, $password);
}
// Cas 2: Mise à jour du nom d'utilisateur uniquement
else if ($username && !$password) {
    // Vérifier si le nom d'utilisateur existe déjà pour un autre admin
    $existingUser = getAdminByUsername($username);
    if ($existingUser && $existingUser['admin_id'] != $adminId) {
        returnError(400, 'Username already exists');
        return;
    }

    $updateResult = updateAdmin($adminId, $username);
}
// Cas 3: Mise à jour du mot de passe uniquement
else if (!$username && $password) {
    // Vérifier la longueur du mot de passe
    if (strlen($password) < 8) {
        returnError(400, 'Password must be at least 8 characters long');
        return;
    }

    // Utiliser le nom d'utilisateur existant
    $updateResult = updateAdmin($adminId, $existingAdmin['username'], $password);
}
// Aucune donnée à mettre à jour
else {
    returnError(400, 'No data provided for update');
    return;
}

// Vérifier le résultat de la mise à jour
if ($updateResult) {
    // Récupérer les données mises à jour pour la confirmation
    $updatedAdmin = getAdminById($adminId);
    if ($updatedAdmin) {
        // Ne pas inclure le mot de passe dans la réponse
        unset($updatedAdmin['password']);

        // Ajouter un indicateur de succès
        $updatedAdmin['success'] = true;

        echo json_encode($updatedAdmin);
        http_response_code(200);
    } else {
        // Cas rare où la mise à jour a réussi mais la récupération a échoué
        echo json_encode(['success' => true, 'id' => $adminId]);
        http_response_code(200);
    }
} else {
    returnError(500, 'Failed to update admin');
}
?>
