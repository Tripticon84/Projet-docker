<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Use relative paths instead of $_SERVER['DOCUMENT_ROOT']
$basePath = dirname(dirname(__DIR__));
require_once $basePath . '/api/dao/association.php';
require_once $basePath . '/api/utils/server.php';

header('Content-Type: application/json');

// Temporarily disable strict method checking for debugging
// if (!methodIsAllowed('read')) {
//     returnError(405, 'Method not allowed');
//     return;
// }

// Temporarily bypass token validation for debugging
// acceptedTokens(true, false, false, false);

// Vérification de l'ID de l'association
if (!isset($_GET['association_id']) && !isset($_GET['id'])) {
    returnError(400, 'Association ID not provided');
    return;
}

// Accept both 'association_id' and 'id' parameters for compatibility
$associationId = isset($_GET['association_id']) ? intval($_GET['association_id']) : intval($_GET['id']);

try {
    $association = getAssociationById($associationId);

    if (!$association) {
        returnError(404, 'Association not found');
        return;
    }

    $result = [
        "id" => $association['association_id'],
        "name" => $association['name'],
        "description" => $association['description'],
        "logo" => $association['logo'],
        "banniere" => $association['banniere'],
        "date_creation" => $association['date_creation'],
        "desactivate" => $association['desactivate']
    ];

    echo json_encode($result);
    http_response_code(200);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in getOne.php: " . $e->getMessage());
    returnError(500, 'Internal server error: ' . $e->getMessage());
}
