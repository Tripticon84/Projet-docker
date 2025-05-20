<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/stringUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
header("Content-Type: application/json");

if (!methodIsAllowed('login')) {
    returnError(405, 'Method not allowed');
}

$data = getBody();

if (!validateMandatoryParams($data, ['username', 'password'])) {
    returnError(400, 'Mandatory parameters : username, password');
}

$username = $data['username'];
$password = $data['password'];

$employee = findEmployeeByCredentials($username, $password);
if (!$employee) {
    returnError(401, 'Invalid credentials');
}
$employeeId = $employee['collaborateur_id'];

$token = date('d/M/Y h:m:s') . '_' . $employeeId . '_' . generateRandomString(100);
$tokenHashed = hash('md5', $token);

$res = setEmployeeSession($employeeId, $tokenHashed);
if (!$res) {
    returnError(500, 'An error has occurred');
}

returnSuccess(
    [
        'token' => $tokenHashed,
        'date' => date_add(
            date_create("now", new DateTimeZone('Europe/Paris')),
            DateInterval::createFromDateString('3 hour')
        )
    ]
);
