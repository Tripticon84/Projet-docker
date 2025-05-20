<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

header('Content-Type: application/json');

try {
    // Vérification de la session
    session_start();
    if (!isset($_SESSION['collaborateur_id'])) {
        throw new Exception('Non autorisé');
    }

    // Récupérer les données du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['collaborateur_id'], $data['currentPassword'], $data['newPassword'])) {
        throw new Exception('Données manquantes');
    }

    // Vérifier que l'utilisateur modifie son propre mot de passe
    if ($_SESSION['collaborateur_id'] != $data['collaborateur_id']) {
        throw new Exception('Non autorisé');
    }

    // Récupérer les informations complètes du collaborateur
    $collaborateur = getEmployeeProfile($_SESSION['collaborateur_id']);
    
    if (!$collaborateur) {
        throw new Exception('Collaborateur non trouvé');
    }

    // Récupérer le collaborateur avec les identifiants fournis
    $result = findEmployeeByCredentials(
        $_SESSION['username'], // Utiliser le username stocké en session
        $data['currentPassword']
    );

    if (!$result) {
        throw new Exception('Mot de passe actuel incorrect');
    }

    // Mettre à jour le mot de passe
    $success = updateEmployee(
        $data['collaborateur_id'],
        password: $data['newPassword']
    );

    if (!$success) {
        throw new Exception('Erreur lors de la mise à jour du mot de passe');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}
