<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/report.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/company.php";

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);


$data = getBody();

if (!validateMandatoryParams($data, ['description', 'probleme', 'date_signalement', 'id_societe'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$description = $data['description'];
$probleme = $data['probleme'];
$date_signalement = $data['date_signalement'];
$id_societe = $data['id_societe'];

// Validate the date format (YYYY-MM-DD)
if (!DateTime::createFromFormat('Y-m-d', $date_signalement)) {
    returnError(400, 'Invalid date format. Expected format: YYYY-MM-DD');
    return;
}

//validate the id_societe
if (!is_numeric($id_societe) || $id_societe < 1) {
    returnError(400, 'Id must be a positive number');
    return;
}

//verify societe id exists
if (!getSocietyById($id_societe)) {
    returnError(404, 'Company not found');
    return;
}


$report_id = createReport($description, $probleme, $date_signalement, $id_societe);

if (!$report_id) {
    returnError(500, 'Internal Server Error');
    return;
} else {
    echo json_encode(['signalement_id' => $report_id]);
    http_response_code(201);
    exit();
}
