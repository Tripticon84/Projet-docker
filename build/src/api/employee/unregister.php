<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Method not allowed');
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    returnError(400, 'Invalid JSON data');
    exit;
}

if (!isset($data['type'])) {
    returnError(400, 'Le champ "type" est obligatoire');
    exit;
}

if (!in_array($data['type'], ['event', 'activite'])) {
    returnError(400, 'Le type doit être "event" ou "activite"');
    exit;
}

$idField = $data['type'] === 'event' ? 'id_evenement' : 'id_activite';
$requiredFields = ['type', $idField, 'collaborateur_id'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    returnError(400, 'Champs manquants: ' . implode(', ', $missingFields));
    exit;
}

try {
    $desinscription = deleteInscription([
        'type' => $data['type'],
        'id_collaborateur' => (int)$data['collaborateur_id'],
        'id_service' => (int)$data[$idField]
    ]);

    if ($desinscription) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Désinscription réussie'
        ]);
    } else {
        returnError(500, 'Échec de la désinscription');
    }
} catch (Exception $e) {
    error_log("Unregistration error: " . $e->getMessage());
    returnError(500, 'Erreur serveur: ' . $e->getMessage());
}
