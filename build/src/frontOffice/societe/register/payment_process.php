<?php
session_start();

// Vérifier si les données de la société sont présentes
if (!isset($_SESSION['company_data'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Session expirée. Veuillez recommencer le processus d\'inscription.']);
    exit();
}

// Récupérer les données JSON de la requête
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

// Vérifier les données requises
if (!isset($input['amount']) || !isset($input['description'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Données de paiement incomplètes.']);
    exit();
}

// Récupérer le montant et la description
$amount = intval($input['amount']);
$description = $input['description'];
$invoice_number = $input['invoice_number'] ?? '';

// Initialiser Stripe
$stripeSecretKey = "sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm";
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
\Stripe\Stripe::setApiKey($stripeSecretKey);

try {
    // Créer un PaymentIntent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'eur',
        'description' => $description,
        'metadata' => [
            'invoice_number' => $invoice_number,
            'company_name' => $_SESSION['company_data']['nom'],
            'company_siret' => $_SESSION['company_data']['siret'],
            'plan' => $_SESSION['company_data']['plan'],
            'employee_count' => $_SESSION['company_data']['employee_count']
        ],
    ]);

    // Retourner le client_secret au frontend
    header('Content-Type: application/json');
    echo json_encode([
        'client_secret' => $paymentIntent->client_secret
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>