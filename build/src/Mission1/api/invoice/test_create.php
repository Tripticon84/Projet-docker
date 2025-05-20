<?php
header('Content-Type: application/json');

// Répondre avec un succès simple
echo json_encode(['success' => true, 'message' => 'Test successful']);
http_response_code(200);
exit;