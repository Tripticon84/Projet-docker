<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

try {
    // Récupérer les données POST
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['type']) || !isset($data['description'])) {
        throw new Exception('Données manquantes');
    }

    // Créer le signalement avec l'id_societe
    $result = createSignalement(
        $data['type'],
        $data['description'],
        isset($data['id_societe']) ? $data['id_societe'] : null
    );

    if ($result) {
        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $result]);
    } else {
        throw new Exception('Erreur lors de la création du signalement');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
