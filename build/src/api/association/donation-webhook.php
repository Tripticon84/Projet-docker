<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';

$stripeSecretKey = 'sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm';
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Récupérer l'événement Stripe
$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(json_decode($payload, true));
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
}

// Gestion des événements de paiement réussis
if ($event->type == 'payment_intent.succeeded') {
    $paymentIntent = $event->data->object; // PaymentIntent objet
    
    // Récupérer les métadonnées
    $associationId = $paymentIntent->metadata->association_id;
    $donorId = $paymentIntent->metadata->collaborateur_id;
    $amount = $paymentIntent->amount / 100; // Convertir les centimes en euros
    $stripePaymentId = $paymentIntent->id;
    
    // Enregistrer le don dans la base de données
    try {
        $db = getDatabaseConnection();
        $sql = "INSERT INTO don (id_association, id_collaborateur, montant, date) 
                VALUES (:id_association, :id_collaborateur, :montant, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id_association' => $associationId,
            'id_collaborateur' => $donorId ?: null,
            'montant' => $amount
        ]);
    } catch (PDOException $e) {
        error_log("Error recording donation: " . $e->getMessage());
    }
}

http_response_code(200);
