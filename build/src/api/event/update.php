<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();

$event_id = $data['evenement_id'];
$nom = isset($data['nom']) ? $data['nom'] : null;
$date = isset($data['date']) ? $data['date'] : null;
$lieu = isset($data['lieu']) ? $data['lieu'] : null;
$type = isset($data['type']) ? $data['type'] : null;
$statut = isset($data['statut']) ? $data['statut'] : null;
$id_association = isset($data['id_association']) ? $data['id_association'] : null;

// Verify at least one field is provided for update
if ($nom === null && $date === null && $lieu === null && $type === null && $statut === null && $id_association === null) {
    returnError(400, 'No data provided for update');
    return;
}

// Verifie si l'id existe
$event = getEventById($event_id);
if (empty($event)) {
    returnError(400, 'L\' événement n\'existe pas');
    return;
}

// Vérifie format de la date
if ($date !== null && !DateTime::createFromFormat('Y-m-d', $date)) {
    returnError(400, 'Date doit etre au format YYYY-MM-DD');
    return;
}

// Vérifie le statut
if ($statut !== null && !in_array($statut, ['en_cours', 'termine', 'a_venir'])) {
    returnError(400, 'Invalide statut');
    return;
}

// Vérifie id_association
if ($id_association !== null && !is_numeric($id_association)) {
    returnError(400, 'id_association doit etre un nombre');
    return;
}

$res = updateEvent($event_id, $nom, $date, $lieu, $type, $statut, $id_association);

if (!$res) {
    returnError(500, 'Evenement non mis a jour');
    return;
} else {
    echo json_encode(['id' => $event_id]);
    http_response_code(200);
}
