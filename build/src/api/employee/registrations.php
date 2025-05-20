<?php
header('Content-Type: application/json');

require_once '../../api/dao/employee.php';

// Log pour debug
error_log("Registrations API called with ID: " . $_GET['collaborateur_id']);

// Vérifier si l'ID du collaborateur est fourni
if (!isset($_GET['collaborateur_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Collaborateur ID requis']);
    exit;
}

$collaborateurId = intval($_GET['collaborateur_id']);

try {
    $registrations = getEmployeeRegistrations($collaborateurId);
    // Log pour debug
    error_log("Registrations found: " . json_encode($registrations));
    echo json_encode($registrations);
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
}
