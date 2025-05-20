<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['collaborateur_id'])) {
        throw new Exception('ID collaborateur manquant');
    }

    $result = updateEmployee(
        $data['collaborateur_id'],
        $data['nom'] ?? null,
        $data['prenom'] ?? null,
        null, // role ne peut pas être modifié
        $data['email'] ?? null,
        $data['telephone'] ?? null
    );

    if ($result === null) {
        throw new Exception('Erreur lors de la mise à jour du profil');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profil mis à jour avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
