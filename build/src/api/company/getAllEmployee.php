<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

// Récupérer les paramètres
$idSociete = intval($_GET['societe_id']);

// Récupérer le paramètre desactivate (0 pour actifs, 1 pour désactivés)
$desactivate = isset($_GET['desactivate']) ? intval($_GET['desactivate']) : null;

// Récupérer les filtres optionnels
$name = isset($_GET['name']) ? $_GET['name'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

// Récupérer les employés avec le filtre de désactivation
$Employees = getSocietyEmployees($idSociete, $desactivate, $name, $role, $date);

if (!$Employees) {
    returnError(404, 'No employees found');
    return;
}

$result = []; // Initialize the result array

foreach ($Employees as $employee) {
    $result[] = [
        "collaborateur_id" => $employee['collaborateur_id'],
        "nom" => $employee['nom'],
        "prenom" => $employee['prenom'],
        "username" => $employee['username'],
        "role" => $employee['role'],
        "email" => $employee['email'],
        "telephone" => $employee['telephone'],
        "date_creation" => $employee['date_creation'],
        "date_activite" => $employee['date_activite'],
        "desactivate" => $employee['desactivate']
    ];
}

if (empty($result)) {
    returnError(404, 'No employee found');
    return;
}

echo json_encode($result);
