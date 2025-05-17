<?php
require __DIR__ . '/vendor/autoload.php';// Charge les librairies Stripe installées via Composer

// 🔐 Ta clé secrète (à cacher dans un .env en prod hein beau gosse)
\Stripe\Stripe::setApiKey('sk_test_taclepaslapubliquedemathieu');

header('Content-Type: application/json');

try {
    // 💸 Création de la session de paiement
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'caca',
                ],
                'unit_amount' => 1000000000, // 1 000 000,00 € (en centimes) 
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'https://tonsite.com/success.html',
        'cancel_url' => 'https://tonsite.com/cancel.html',
    ]);

    // Envoie l'ID de session au frontend
    echo json_encode(['id' => $session->id]);

} catch (Exception $e) {
    // En cas d’erreur, envoie un message au frontend
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}


// Exemple de code JavaScript pour rediriger vers la page de paiement Stripe
// Assure-toi d'inclure Stripe.js dans ton HTML avant d'utiliser ce code

//<script src="https://js.stripe.com/v3/"></script>
// <script>
// fetch('/create-checkout-session.php')
//   .then(res => res.json())
//   .then(data => {
//     const stripe = Stripe('pk_test_taclepaslapubliquedemathieu');
//     stripe.redirectToCheckout({ sessionId: data.id });
//   });
// </script>
