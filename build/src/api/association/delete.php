<?php
// Basic error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/api_errors.log');

// Log the start of execution for debugging
error_log("DELETE request received in association/delete.php");

header('Content-Type: application/json');

try {
    // Include only the essential files
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';
    
    // Log successful includes
    error_log("Database utility included successfully");
    
    // Parse request
    $json = file_get_contents('php://input');
    error_log("Request body: " . $json);
    
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['association_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Association ID is required']);
        exit;
    }
    
    $association_id = $data['association_id'];
    error_log("Processing association ID: " . $association_id);
    
    // Get database connection
    $db = getDatabaseConnection();
    error_log("Database connection established");
    
    // Simple direct update
    $sql = "UPDATE association SET desactivate = 1 WHERE association_id = :id";
    $stmt = $db->prepare($sql);
    error_log("SQL prepared: " . $sql);
    
    // Execute with explicit error handling
    $result = $stmt->execute(['id' => $association_id]);
    error_log("SQL execution result: " . ($result ? 'success' : 'failure'));
    
    if (!$result) {
        error_log("SQL error info: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Database update failed");
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Association deactivated successfully',
        'id' => $association_id
    ]);
    
    error_log("Response sent successfully");
    
} catch (Exception $e) {
    error_log("Error in delete.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

