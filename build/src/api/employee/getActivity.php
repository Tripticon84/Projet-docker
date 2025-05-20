<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header("Content-Type: application/json");

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}
acceptedTokens(true, false, true, false);

if (empty($_GET['collaborateur_id']))
    returnError(400, 'Mandatory parameter : collaborateur_id');


$employeeId = intval($_GET['collaborateur_id']);
if ($employeeId <= 0) {
    returnError(400, 'Invalid employee ID');
    return;
}
$employee = getEmployee($employeeId);
if (!$employee) {
    returnError(404, 'Employee not found');
    return;
}
$activitys = getEmployeeActivities($employeeId);

if (!$activitys) {
    returnError(404, 'Activity not found for this employee');
    return;
}
$result = []; // Initialize the result array
foreach ($activitys as $activity) {
    $result[] = [
        "activity_id" => $activity['activite_id'], // Changed from activity_id
        "nom" => $activity['type'], // Using type as name since it's available
        "type" => $activity['type'],
        "date" => $activity['date'],
        "is_devis" => $activity['id_devis'],
        "id_prestataire" => $activity['id_prestataire'],
        "id_lieu" => $activity['id_lieu']
    ];
}

echo json_encode($result);
http_response_code(200);
