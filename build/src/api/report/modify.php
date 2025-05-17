<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/report.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/company.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/validDate.php";

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


// Récupérer les données de la requête
$data = getBody();

if (!isset($data['signalement_id']) || empty($data['signalement_id'])) {
    returnError(400, 'Signalement ID is required');
    return;
}

// Récupérer le signalement par son ID
$reportId = $data['signalement_id'];
$existingReport = getReportById($reportId);

// Vérifier si le signalement existe
if (!$existingReport) {
    returnError(404, 'Signalement not found');
    return;
}

// Variables pour stocker les résultats
$description = isset($data['description']) ? $data['description'] : null;
$probleme = isset($data['probleme']) ? $data['probleme'] : null;
$date_signalement = isset($data['date_signalement']) ? $data['date_signalement'] : null;
$id_societe = isset($data['id_societe']) ? $data['id_societe'] : null;
$statut = isset($data['statut']) ? $data['statut'] : null;

if ($date_signalement !== null && !isValidDate($date_signalement)) {
    returnError(400, 'Invalid date format for date_signalement. Expected format: YYYY-MM-DD');
    return;

}

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($description === null && $probleme === null && $date_signalement === null && $id_societe === null && $statut === null) {
    returnError(400, 'At least one field must be provided for update');
    return;
}

// Vérifier si le statut est valide
$actualState = checkState($reportId);
if ($actualState['statut'] == $data['statut']) {
    returnError(400, 'State not changed. Report already in the requested state.');
    return;
}

if ($id_societe !== null && !(getSocietyById($id_societe))) {
    returnError(404, 'Company not found');
    return;
}
// Mettre à jour le signalement
$updateResult = modifyReport($reportId, $description, $probleme, $date_signalement, $id_societe, $statut);

if (!$updateResult) {
    // Log the error for debugging
    error_log("Failed to update Invoice: " . print_r(error_get_last(), true));
    returnError(500, 'Could not update the Report. Database operation failed.');
    return;
}
else{
    echo json_encode(['signalement_id' => $reportId]);
    http_response_code(200);
}
?>
