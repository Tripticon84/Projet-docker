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
// Changed from 'desactivate' to match the parameter used in JavaScript
$desactivate = isset($_GET['desactivate']) ? intval($_GET['desactivate']) : null;

// Récupérer les filtres optionnels
$name = isset($_GET['name']) ? $_GET['name'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

// Debug output to check values
error_log("Getting employees for society: $idSociete, desactivate: $desactivate");

// Récupérer les employés avec le filtre de désactivation
$Employees = getSocietyEmployees($idSociete, $desactivate, $name, $role, $date);

if (!$Employees) {
    // Return an empty array instead of an error
    echo json_encode([]);
    return;
}

$result = []; // Initialize the result array

foreach ($Employees as $employee) {
    $result[] = [
        "id" => $employee['collaborateur_id'], // Changed to match what's expected in JavaScript
        "nom" => $employee['nom'],
        "prenom" => $employee['prenom'],
        "username" => $employee['username'],
        "role" => $employee['role'],
        "email" => $employee['email'],
        "telephone" => $employee['telephone'],
        "date_ajout" => $employee['date_creation'], // Changed to match frontend expectations
        "desactivate" => $employee['desactivate']
    ];
}

if (empty($result)) {
    // Also return an empty array here
    echo json_encode([]);
    return;
}

echo json_encode($result);
