<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

$data = getBody();
$id = $data['id'];
$plan = isset($data['plan']) ? $data['plan'] : null;
$max_employee = isset($data['max_employee']) ? $data['max_employee'] : null;

if ($plan === null && $max_employee === null) {
    returnError(400, 'No data provided for update');
    return;
}

// Vérifier l'id existe
$company = getSocietyById($id);
if (empty($company)) {
    returnError(400, 'company does not exist');
    return;
}
// Vérifier le plan est valide
$valid_plans = ['starter', 'basic', 'premium'];

if ($plan !== null && !in_array($plan, $valid_plans)) {
    returnError(400, 'Invalid plan');
    return;
}

// Vérifier le nombre d'employés est valide
if ($max_employee !== null && !is_numeric($max_employee)) {
    returnError(400, 'Invalid max employee');
    return;
}
// Vérifier le nombre d'employés est positif
if ($max_employee !== null && $max_employee <= 0) {
    returnError(400, 'Max employee must be positive');
    return;
}

// Vérifier le plan est compatible avec le nombre d'employés
if ($plan !== null && $max_employee !== null) {
    if ($plan === 'starter' && $max_employee > 1) {
        returnError(400, 'Starter plan can only have 1 employee');
        return;
    } elseif ($plan === 'basic' && $max_employee < 31) {
        returnError(400, 'Basic plan can only have 5 employees');
        return;
    } elseif ($plan === 'premium' && $max_employee < 251) {
        returnError(400, 'Premium plan can only have 20 employees');
        return;
    }
}

$updatePlan = updateCompanyPlan($id, $plan);

$updateMaxEmployee = addEmployeeToEmployeeCount($id, $max_employee);
if ($updatePlan && $updateMaxEmployee) {
    returnSuccess(200, 'Company plan and max employee updated successfully');
} else {
    returnError(500, 'Failed to update company plan and max employee');
}

