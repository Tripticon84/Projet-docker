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

    // For debug purposes - remove this in production
    $debug = true;
    if (!$debug) {
        try {
            acceptedTokens(true, true, true, false);
        } catch (Exception $e) {
            returnError(401, 'Unauthorized: ' . $e->getMessage());
            exit;
        }
    }

    // Get associations from the database
    $associations = getAllAssociations();
    
    // Check if we got a valid response (even an empty array is valid)
    if (!is_array($associations)) {
        returnError(500, 'Failed to retrieve associations from database');
        exit;
    }

    $result = []; // Initialize the result array

    // Process each association
    foreach ($associations as $association) {
        $result[] = [
            "id" => $association['association_id'],
            "name" => $association['name'],
            "description" => $association['description'],
            "logo" => isset($association['logo']) ? $association['logo'] : null,
            "banniere" => isset($association['banniere']) ? $association['banniere'] : null,
            "date_creation" => $association['date_creation'],
            "desactivate" => isset($association['desactivate']) ? (bool)$association['desactivate'] : false
        ];
    }

    // Return the JSON encoded result
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log('Error in getAll.php: ' . $e->getMessage());
    
    // Return a clean JSON error
    returnError(500, 'Server error occurred');
}

exit;
