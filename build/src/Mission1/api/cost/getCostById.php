<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/headers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/authenticate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/cost.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Use GET.']);
    exit();
}

// Check authentication
try {
    // Authenticate the request
    authenticate();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

// Get and validate the cost_id parameter
if (!isset($_GET['cost_id']) || empty($_GET['cost_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing cost_id parameter']);
    exit();
}

$costId = intval($_GET['cost_id']);

// Get the cost from database
$cost = getCostById($costId);

if (!$cost) {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Cost not found']);
    exit();
}

// Return the cost information
echo json_encode($cost);
