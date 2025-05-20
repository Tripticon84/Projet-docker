<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/evaluation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Check de la méthode HTTP - on veut juste un POST pour créer une évaluation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Method not allowed');
    return;
}

// J'ai commenté cette vérification temporairement pour faciliter les tests
// acceptedTokens(true, false, true, false);

// Récupère le contenu de la requête JSON
$data = getBody();
$note = $data['note'];
$commentaire = $data['commentaire'];
$collaborateur_id = $data['collaborateur_id'];
$date_creation = date('Y-m-d H:i:s'); // Date actuelle au format SQL
$id_prestataire = $data['prestataire_id'];

// Validation des données reçues
if (validateMandatoryParams($data, ['note', 'commentaire', 'collaborateur_id'])) {

    // Check que la note est bien entre 0 et 5
    if (!is_numeric($note) || $note < 0 || $note > 5) {
        returnError(400, 'Note must be a number between 0 and 5');
        return;
    }
    // Limite la taille du commentaire (évite les roman-fleuves)
    if (strlen($commentaire) > 255) {
        returnError(400, 'Commentaire must be less than 255 characters');
        return;
    }

    // Vérif que l'ID collab est bien un nombre
    if (!is_numeric($collaborateur_id)) {
        returnError(400, 'collaborateur_id must be a number');
        return;
    }
    // Check si le collab existe en BDD
    if (!getEmployee($collaborateur_id)) {
        returnError(404, 'employye not found');
        return;
    }

    // Création de l'évaluation dans la BDD
    $newEvaluationId = createEvaluation($note, $commentaire, $collaborateur_id, $date_creation);

    if (!$newEvaluationId) {
        // Log les erreurs pour debug
        error_log("Failed to create evaluation: " . print_r(error_get_last(), true));
        returnError(500, 'Could not create the Evaluation. Database operation failed.');
        return;
    }

    // Associe l'évaluation au prestataire
    $insert=newEvaluationInNote_prestataire($newEvaluationId,$id_prestataire);
    if (!$insert) {
        // Si ça échoue, on log l'erreur
        error_log("Failed to create evaluation: " . print_r(error_get_last(), true));
        returnError(500, 'Could not create the Evaluation. Database operation failed.');
        return;
    }

    // Retourne l'ID de la nouvelle évaluation en cas de succès
    echo json_encode(['evaluation_id' => $newEvaluationId]);
    http_response_code(201); // Created
} else {
    returnError(400, 'Missing required parameters');
    return;
}
