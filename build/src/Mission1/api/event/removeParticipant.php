<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Debug logs
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
$input = file_get_contents('php://input');
error_log('Raw Input: ' . $input);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'No input data received']);
    exit;
}

$data = json_decode($input, true);
error_log('Decoded data: ' . print_r($data, true));

// Vérification des paramètres
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Les données doivent être un objet JSON',
        'received' => $input
    ]);
    exit;
}

if (!isset($data['id_evenement']) || !isset($data['id_collaborateur']) || 
    $data['id_evenement'] === null || $data['id_collaborateur'] === null) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Les paramètres id_evenement et id_collaborateur sont requis et ne peuvent pas être null',
        'received' => $data
    ]);
    exit;
}

// Convertir et valider les IDs
$event_id = filter_var($data['id_evenement'], FILTER_VALIDATE_INT);
$collaborator_id = filter_var($data['id_collaborateur'], FILTER_VALIDATE_INT);

if ($event_id === false || $collaborator_id === false || $event_id <= 0 || $collaborator_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Les IDs doivent être des nombres entiers positifs',
        'received' => [
            'event_id' => $data['id_evenement'],
            'collaborator_id' => $data['id_collaborateur']
        ]
    ]);
    exit;
}

// Appeler la fonction de suppression
$result = removeParticipant($event_id, $collaborator_id);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Participant supprimé avec succès',
        'event_id' => $event_id,
        'collaborator_id' => $collaborator_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Échec de la suppression du participant',
        'event_id' => $event_id,
        'collaborator_id' => $collaborator_id
    ]);
}
