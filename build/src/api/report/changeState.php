<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/report.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

$data = getBody();

acceptedTokens(true, false, true, false);


if (!isset($data['signalement_id'])) {
    returnError(400, 'Missing signalement_id');
    return;
}
if (!is_numeric($data['signalement_id']) || $data['signalement_id'] < 1) {
    returnError(400, 'Id must be a positive number');
    return;
}

//verify if the admin exists
$report = getReportById($data['signalement_id']);
if (!$report) {
    returnError(404, 'report not found');
    return;
}

//verify that state exists
if (!isset($data['statut'])) {
    returnError(400, 'Missing statut');
    return;
}
if ($data['statut'] != 'non_traite' && $data['statut'] != 'en_cours' && $data['statut'] != 'resolu' && $data['statut'] != 'annuler') {
    returnError(400, 'Invalid statut. Expected: non_traite, en_cours, résolu, annuler');
    return;
}

$actualState = checkState($data['signalement_id']);
if ($actualState['statut'] == $data['statut']) {
    returnError(400, 'State not changed. Report already in the requested state.');
    return;
}

$changed = changeState($data['signalement_id'], $data['statut']);

if ($changed === null) {
    returnError(500, 'Failed to change state report');
    return;
} elseif ($changed == 0) {
    returnError(404, 'State not changed. Report not found or already in the requested state.');
    return;
}
else {
    echo json_encode(['signalement_id' => $data['signalement_id']]);
    http_response_code(200);
    exit();
}
