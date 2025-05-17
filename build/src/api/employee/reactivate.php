<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérification de la méthode HTTP (PUT ou POST recommandé pour une réactivation)
if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

// Vérification des tokens/permissions
acceptedTokens(true, true, false, false);

// Récupération des données
$data = getBody();

// Vérification de l'ID de l'employé
if (!isset($data['id']) || empty($data['id'])) {
    returnError(400, 'Employee ID is required');
    return;
}

$id = intval($data['id']);

// Vérification que l'employé existe et est désactivé
$employee = getEmployee($id);
if (!$employee) {
    returnError(404, 'Employee not found');
    return;
}

if ($employee['desactivate'] != 1) {
    returnError(400, 'Employee is already active');
    return;
}

// Réactivation de l'employé
$result = reactivateEmployee($id);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Employee reactivated successfully',
        'employee_id' => $id
    ]);
} else {
    returnError(500, 'Failed to reactivate employee');
}
