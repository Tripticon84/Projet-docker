<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header("Content-Type: application/json");

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}
acceptedTokens(true, false, true, false);

if (empty($_GET['id']))
    returnError(400, 'Mandatory parameter : id');


$employeeId = intval($_GET['id']);
if ($employeeId <= 0) {
    returnError(400, 'Invalid employee ID');
    return;
}
$employee = getEmployee($employeeId);
if (!$employee) {
    returnError(404, 'Employee not found');
    return;
}
$associations = getEmployeeAssociations($employeeId);

if (!$associations) {
    returnError(404, 'Associations not found for this employee');
    return;
}
$result=[]; // Initialize the result array
foreach ($associations as $association) {
    $result[] = [
        "association_id" => $association['association_id'],
        "nom" => $association['name'],
        "description" => $association['description'],
        "banniere" => $association['banniere'],
        "logo" => $association['logo'],
        "date_creation" => $association['date_creation'],
        "desactivate" => $association['desactivate']
    ];
}

echo json_encode($result);
http_response_code(200);
