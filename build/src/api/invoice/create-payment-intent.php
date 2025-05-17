<?php

// Initialisation de la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['societe_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Configuration et chargement des dépendances
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';

// Clé secrète Stripe - À stocker de manière sécurisée en production
$stripeSecretKey = 'sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm';

// Configuration de l'API Stripe
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Récupérer les données de la requête
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData);

if (!$data || !isset($data->invoiceId)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invoice ID is required']);
    exit;
}

$invoiceId = $data->invoiceId;
$societyId = $_SESSION['societe_id'];

try {

    $db = getDatabaseConnection();


    $invoice = getCompanyInvoiceByID($societyId, $invoiceId);

    if (!$invoice) {
        throw new Exception('Facture non trouvée ou non autorisée');
    }

    if ($invoice['statut'] !== 'Attente') {
        throw new Exception('Cette facture ne peut pas être payée (statut: ' . $invoice['statut'] . ')');
    }

    // Convertir le montant en centimes pour Stripe (Stripe utilise les centimes)
    $amountInCents = round($invoice['montant'] * 100);

    // Créer l'intention de paiement Stripe
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amountInCents,
        'currency' => 'eur',
        'metadata' => [
            'invoice_id' => $invoiceId,
            'society_id' => $societyId
        ],
        'description' => 'Paiement de la facture #' . $invoiceId,
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
    ]);

    // Mettre à jour la facture avec l'ID de l'intention de paiement
    $updateStmt = $db->prepare("UPDATE facture SET payment_intent_id = :payment_intent_id WHERE facture_id = :id");
    $updateStmt->execute([
        ':payment_intent_id' => $paymentIntent->id,
        ':id' => $invoiceId
    ]);

    // Retourner le secret client au navigateur
    header('Content-Type: application/json');
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret,
        'invoiceId' => $invoiceId,
        'amount' => $invoice['montant']
    ]);

} catch (Exception $e) {
    // Gérer les erreurs
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
