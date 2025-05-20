<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

// Add CORS headers
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Method not allowed');
    exit;
}

// Get and validate input data
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    returnError(400, 'Invalid JSON data');
    exit;
}

// First validate type field existence
if (!isset($data['type'])) {
    returnError(400, 'Le champ "type" est obligatoire');
    exit;
}

// Validate type value
if (!in_array($data['type'], ['event', 'activite'])) {  // Changed 'activity' to 'activite'
    returnError(400, 'Le type doit être "event" ou "activite"');
    exit;
}

// Determine required ID field based on type
$idField = $data['type'] === 'event' ? 'id_evenement' : 'id_activite';

// Build array of required fields with clear field names
$requiredFields = ['type', $idField, 'collaborateur_id'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    $message = 'Champs manquants: ' . implode(', ', $missingFields);
    $message .= '. Pour le type "event", utilisez "id_evenement", pour le type "activite", utilisez "id_activite"';
    returnError(400, $message);
    exit;
}

// Validate data types
if (!is_string($data['type'])) {
    returnError(400, 'Le champ "type" doit être une chaîne de caractères');
    exit;
}

if (!is_numeric($data[$idField])) {
    returnError(400, sprintf('Le champ "%s" doit être un nombre', $idField));
    exit;
}

if (!is_numeric($data['collaborateur_id'])) {
    returnError(400, 'Le champ "collaborateur_id" doit être un nombre');
    exit;
}

try {
    $inscription = createInscription([
        'type' => $data['type'],
        'id_collaborateur' => (int)$data['collaborateur_id'],
        'id_service' => (int)$data[$idField],
        'date_inscription' => date('Y-m-d H:i:s')
    ]);

    if ($inscription) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Inscription réussie'
        ]);
    } else {
        returnError(500, 'Échec de l\'inscription');
    }
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    returnError(500, 'Erreur serveur: ' . $e->getMessage());
}
