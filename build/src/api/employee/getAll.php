<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$username = '';
$limit = null;
$offset = null;
$id_societe = null;
$desactivate = 0; // Default to active employees

if (isset($_GET['username'])) {
    $username = trim($_GET['username']);
}
if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit < 1) {
        returnError(400, 'Limit must be a positive and non zero number');
    }
}
if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
    if ($offset < 0) {
        returnError(400, 'Offset must be a positive number');
    }
}
if (isset($_GET['id_societe'])) {
    $id_societe = intval($_GET['id_societe']);
    if ($id_societe < 0) {
        returnError(400, 'id_societe must be a positive number');
    }
}
if (isset($_GET['desactivate'])) {
    $desactivate = intval($_GET['desactivate']);
    // Only accept 0 or 1
    if ($desactivate !== 0 && $desactivate !== 1) {
        returnError(400, 'desactivate must be 0 or 1');
    }
}

// Choose the appropriate function based on the desactivate parameter
if ($desactivate === 1) {
    $employees = getDisabledEmployees($username, $limit, $offset, $id_societe);
} else {
    $employees = getAllEmployees($username, $limit, $offset, $id_societe);
}

// Handle case where no employees are found
if ($employees === null || count($employees) === 0) {
    echo json_encode([]);
    exit;
}

$result = []; // Initialize the result array

foreach ($employees as $employee) {
    $result[] = [
        "collaborateur_id" => $employee['collaborateur_id'],
        "nom" => $employee['nom'],
        "prenom" => $employee['prenom'],
        "username" => $employee['username'],
        "role" => $employee['role'],
        "email" => $employee['email'],
        "telephone" => $employee['telephone'],
        "id_societe" => $employee['id_societe'],
        "date_creation" => $employee['date_creation'],
        "date_activite" => $employee['date_activite'],
    ];
}

echo json_encode($result);
