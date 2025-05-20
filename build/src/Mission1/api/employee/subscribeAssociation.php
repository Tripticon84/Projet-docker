<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Method not allowed');
    return;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['collaborateur_id']) || !isset($data['association_id'])) {
    returnError(400, 'Missing required fields');
    return;
}

try {
    $db = getDatabaseConnection();
    
    // Vérifier si déjà inscrit
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM participe_association WHERE id_collaborateur = :collaborateur_id AND id_association = :association_id");
    $checkStmt->execute([
        'collaborateur_id' => $data['collaborateur_id'],
        'association_id' => $data['association_id']
    ]);
    
    if ($checkStmt->fetchColumn() > 0) {
        returnError(400, 'Already subscribed to this association');
        return;
    }

    // Inscrire à l'association
    $stmt = $db->prepare("INSERT INTO participe_association (id_collaborateur, id_association) VALUES (:collaborateur_id, :association_id)");
    $success = $stmt->execute([
        'collaborateur_id' => $data['collaborateur_id'],
        'association_id' => $data['association_id']
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Successfully subscribed to association']);
    } else {
        returnError(500, 'Failed to subscribe to association');
    }
} catch (Exception $e) {
    returnError(500, 'Server error: ' . $e->getMessage());
}
