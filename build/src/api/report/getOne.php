<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/report.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

// Vérification de l'ID de l'administrateur
if (!isset($_GET['signalement_id'])) {
    returnError(400, 'report ID not provided');
    return;
}

$reportId = intval($_GET['signalement_id']);
$report = getReportById($reportId);

if (!$report) {
    returnError(404, 'report not found');
    return;
}

$result = [
    "signalement_id" => $report['signalement_id'],
    "description" => $report['description'],
    "probleme" => $report['probleme'],
    "date_signalement" => $report['date_signalement'],
    "id_societe" => $report['id_societe'],
    "statut" => $report['statut'],
];

echo json_encode($result);
http_response_code(200);
