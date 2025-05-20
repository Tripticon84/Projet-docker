<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

try {
    // Check if the required functions exist
    if (!function_exists('canAddEmployee')) {
        throw new Exception("Function canAddEmployee is not defined");
    }

    if (!function_exists('getCompanyActualSubscription')) {
        throw new Exception("Function getCompanyActualSubscription is not defined");
    }

    if (!function_exists('getDatabaseConnection')) {
        throw new Exception("Function getDatabaseConnection is not defined");
    }

    // Check request method
    if (!methodIsAllowed('read')) {
        returnError(405, 'Method not allowed');
        exit();
    }

    // Verify company ID is provided
    if (!isset($_GET['societe_id'])) {
        returnError(400, 'Company ID is required');
        exit();
    }

    $societe_id = intval($_GET['societe_id']);
    if ($societe_id <= 0) {
        returnError(400, 'Invalid company ID');
        exit();
    }

    // Get database connection
    $db = getDatabaseConnection();
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    // Verify the company exists
    $sql = "SELECT societe_id, employee_count FROM societe WHERE societe_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $societe_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        throw new Exception("Company with ID $societe_id not found");
    }

    // Get the current number of active employees
    $sqlCount = "SELECT COUNT(*) as count FROM collaborateur WHERE id_societe = :id AND desactivate = 0";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute(['id' => $societe_id]);
    $employeeCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
    $currentEmployees = $employeeCount ? intval($employeeCount['count']) : 0;

    // Get the employee limit from the company record
    $maxEmployees = $company['employee_count'] ? intval($company['employee_count']) : 0;

    // Check if the company can add more employees
    $canAdd = $currentEmployees < $maxEmployees;
    $remaining = max(0, $maxEmployees - $currentEmployees);

    // Prepare the base result
    $result = [
        'status' => $canAdd,
        'message' => $canAdd ? 
            'You can add more employees' : 
            'You have reached your employee limit. Please upgrade your subscription.',
        'current' => $currentEmployees,
        'max' => $maxEmployees,
        'remaining' => $remaining
    ];

    // Try to get subscription details
    try {
        $subscription = getCompanyActualSubscription($societe_id);
        if ($subscription) {
            $result['subscription'] = [
                'nom' => $subscription['nom'] ?? 'Basic Subscription',
                'montant' => $subscription['montant'] ?? 0,
                'date_debut' => $subscription['date_debut'] ?? date('Y-m-d'),
                'date_fin' => $subscription['date_fin'] ?? date('Y-m-d', strtotime('+1 year'))
            ];
        }
    } catch (Exception $e) {
        // Log the error but continue
        error_log("Warning: " . $e->getMessage());
        // Add a simple subscription object with default values
        $result['subscription'] = [
            'nom' => 'Basic Subscription',
            'montant' => 150,
            'date_debut' => date('Y-m-d'),
            'date_fin' => date('Y-m-d', strtotime('+1 year'))
        ];
    }

    // Return the result as JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log the error for server-side debugging
    error_log("Error in checkEmployeeLimit.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return a user-friendly error message
    returnError(500, 'An error occurred while checking employee limits: ' . $e->getMessage());
}
