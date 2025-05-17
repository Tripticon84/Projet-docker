<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['collaborateur_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';

$stripeSecretKey = 'sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm';
\Stripe\Stripe::setApiKey($stripeSecretKey);

$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData);

if (!$data || !isset($data->amount) || !isset($data->associationId)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $amountInCents = round($data->amount * 100);

    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amountInCents,
        'currency' => 'eur',
        'metadata' => [
            'association_id' => $data->associationId,
            'collaborateur_id' => $_SESSION['collaborateur_id']
        ],
        'description' => 'Don pour l\'association #' . $data->associationId,
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
    ]);

    header('Content-Type: application/json');
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
