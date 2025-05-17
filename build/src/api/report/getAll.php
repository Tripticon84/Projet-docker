<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/report.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$probleme = '';
$companyId = '';
$limit = null;
$offset = null;


if (isset($_GET['probleme'])) {
    $probleme = $_GET['probleme'];
}

if (isset($_GET['companyId'])) {
    $companyId = $_GET['companyId'];
    if ($companyId < 1) {
        returnError(400, 'Id must be a positive number');
    }
    if (!is_numeric($companyId)) {
        returnError(400, 'Id must be a number');
    }
    $company = getSocietyById($companyId);
    if ($company === null) {
        returnError(404, 'Company not found');
        return;
    }
}
if (isset($_GET['limit'])) {
    $limit = $_GET['limit'];
}
if (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
}

if ($companyId !== '') {
    $reports = getAllReportsByCompany($companyId, $limit, $offset);
} else {
    $reports = getAllReports($probleme, $limit, $offset);
}



if($reports === null){
    returnError(500, 'Internal Server Error');
    return;
}

$result = []; // Initialize the result array

foreach ($reports as $report) {
    $result[] = [
        "signalement_id" => $report['signalement_id'],
        "description" => $report['description'],
        "probleme" => $report['probleme'],
        "date_signalement" => $report['date_signalement'],
        "statut" => $report['statut'],
        "id_societe" => $report['id_societe']
    ];
}

if (empty($result)) {
    returnError(404, 'No report found');
    return;
}

echo json_encode($result);
