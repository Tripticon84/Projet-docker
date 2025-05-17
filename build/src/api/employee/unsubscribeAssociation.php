<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Method not allowed');
    return;
}

// Récupérer les données du corps de la requête
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['collaborateur_id']) || !isset($data['association_id'])) {
    returnError(400, 'Missing required fields');
    return;
}

try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("DELETE FROM participe_association WHERE id_collaborateur = :collaborateur_id AND id_association = :association_id");
    
    $success = $stmt->execute([
        'collaborateur_id' => $data['collaborateur_id'],
        'association_id' => $data['association_id']
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Désinscription réussie']);
    } else {
        returnError(500, 'Failed to unsubscribe from association');
    }
} catch (Exception $e) {
    returnError(500, 'Database error: ' . $e->getMessage());
}
