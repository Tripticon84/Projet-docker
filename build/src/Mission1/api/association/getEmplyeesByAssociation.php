<?php
// No direct display of errors
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/association.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

// Clear any output
ob_clean();

header('Content-Type: application/json');

try {
    if (!methodIsAllowed('read')) {
        returnError(405, 'Method not allowed');
        exit;
    }

    // For debug purposes
    $debug = true;
    if (!$debug) {
        try {
            acceptedTokens(true, true, true, false);
        } catch (Exception $e) {
            returnError(401, 'Unauthorized: ' . $e->getMessage());
            exit;
        }
    }

    if (!isset($_GET['association_id'])) {
        returnError(400, 'Association ID is required');
        exit;
    }

    $association_id = $_GET['association_id'];

    // Get employees for this association
    $employees = getEmployeesByAssociation($association_id);

    // Return the response
    echo json_encode([
        'success' => true,
        'association_id' => $association_id,
        'employees' => $employees
    ]);
} catch (Exception $e) {
    error_log('Error in getEmplyeesByAssociation.php: ' . $e->getMessage());
    returnError(500, 'Server error occurred');
}

exit;
