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
$events = getEmployeeEvents($employeeId);

if (!$events) {
    returnError(404, 'Event not found for this employee');
    return;
}
$result = []; // Initialize the result array
foreach ($events as $event) {
    $result[] = [
        "evenement_id" => $event['evenement_id'],
        "nom" => $event['nom'],
        "date" => $event['date'],
        "lieu" => $event['lieu'],
        "type" => $event['type'],
        "statut" => $event['statut'],
        "id_association" => $event['id_association']
    ];
}

echo json_encode($result);
http_response_code(200);
