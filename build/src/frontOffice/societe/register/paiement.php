<?php
session_start();

// Vérifier si les données de la société sont présentes
if (!isset($_SESSION['company_data'])) {
    header('Location: register.php');
    exit();
}

$company_data = $_SESSION['company_data'];

// Récupérer les données d'abonnement depuis company_data
$employee_count = $company_data['employee_count'];
$selected_plan = $company_data['plan'];
$price = $company_data['price'];

// Définir les détails du plan basé sur la sélection
$plan_details = [
    'starter' => [
        'name' => 'Starter',
        'price_per_employee' => 180
    ],
    'basic' => [
        'name' => 'Basic',
        'price_per_employee' => 150
    ],
    'premium' => [
        'name' => 'Premium',
        'price_per_employee' => 100
    ]
];

// Calculer le total et les taxes
$price_per_employee = $plan_details[$selected_plan]['price_per_employee'];
$subtotal = $employee_count * $price_per_employee;
$tax_rate = 0.20; // TVA à 20%
$tax_amount = $subtotal * $tax_rate;
$total = $subtotal + $tax_amount;

// Générer un numéro de facture
$invoice_number = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

$_SESSION['company_data']['invoice_number'] = $invoice_number;
$_SESSION['company_data']['total'] = $total;

// Initialiser Stripe
$stripePublishableKey = "pk_test_51PAXnVP0arAN6IC0UPtpOnaMS4O1Qp153mO28fvjMR3Qzq486KDOiScwYdv3svHGcARvLi2MuE9dh7shZtRnd5cW00Vx0swc4q";

$title = "Inscription - Paiement";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5, #6fc2c0);
        background-attachment: fixed;
    }

    .payment-card {
        border-radius: 15px;
        box-shadow: rgba(58, 123, 213, 0.4) 5px 5px,
                    rgba(58, 123, 213, 0.3) 10px 10px,
                    rgba(58, 123, 213, 0.2) 15px 15px,
                    rgba(58, 123, 213, 0.1) 20px 20px,
                    rgba(58, 123, 213, 0.05) 25px 25px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.95);
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem !important;
        background: linear-gradient(to right, #3a7bd5, #6fc2c0) !important;
        border: none;
    }

    .form-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        width: 100%;
        height: 2px;
        background-color: #dee2e6;
        z-index: -1;
    }

    .step:last-child::after {
        display: none;
    }

    .step-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #dee2e6;
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .step-circle.active {
        background-color: #3a7bd5;
        color: white;
    }

    .step-text {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .step-text.active {
        color: #3a7bd5;
        font-weight: bold;
    }

    .btn-payment {
        background: linear-gradient(to right, #3a7bd5, #6fc2c0);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-payment:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(58, 123, 213, 0.4);
        color: white;
    }

    .invoice-details {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
    }

    .payment-method-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 1.5rem;
        background-color: #f8f9fa;
    }

    #card-element {
        padding: 12px;
        border: 1px solid #ced4da;
        border-radius: 5px;
    }

    .spinner-border {
        display: none;
    }

    .payment-error {
        color: #dc3545;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
</style>

<!-- Intégration de Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-10">
            <div class="card payment-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Finalisation de votre abonnement</h3>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 100px;">
                        <h4 class="mt-3 fw-bold text-primary">Paiement de votre abonnement</h4>
                    </div>

                    <!-- Progress Steps -->
                    <div class="form-steps">
                        <div class="step">
                            <div class="step-circle">1</div>
                            <div class="step-text">Informations</div>
                        </div>
                        <div class="step">
                            <div class="step-circle">2</div>
                            <div class="step-text">Abonnement</div>
                        </div>
                        <div class="step">
                            <div class="step-circle active">3</div>
                            <div class="step-text active">Confirmation</div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Détails de la facture -->
                        <div class="col-lg-6 mb-4">
                            <div class="invoice-details">
                                <h5 class="fw-bold border-bottom pb-2 mb-3">Détails de la facture</h5>
                                <p class="mb-1"><strong>N° de facture:</strong> <?php echo $invoice_number; ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?php echo date('d/m/Y'); ?></p>
                                <p class="mb-3"><strong>Société:</strong> <?php echo htmlspecialchars($company_data['nom']); ?></p>
                                
                                <h6 class="fw-bold mt-4">Récapitulatif de l'abonnement</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td>Formule</td>
                                                <td class="text-end"><strong><?php echo $plan_details[$selected_plan]['name']; ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Nombre de salariés</td>
                                                <td class="text-end"><?php echo $employee_count; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Prix unitaire</td>
                                                <td class="text-end"><?php echo $price_per_employee; ?> € / salarié / an</td>
                                            </tr>
                                            <tr>
                                                <td>Sous-total (HT)</td>
                                                <td class="text-end"><?php echo number_format($subtotal, 2, ',', ' '); ?> €</td>
                                            </tr>
                                            <tr>
                                                <td>TVA (20%)</td>
                                                <td class="text-end"><?php echo number_format($tax_amount, 2, ',', ' '); ?> €</td>
                                            </tr>
                                            <tr class="fw-bold">
                                                <td>Total (TTC)</td>
                                                <td class="text-end"><?php echo number_format($total, 2, ',', ' '); ?> €</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de paiement -->
                        <div class="col-lg-6">
                            <form id="payment-form" method="post">
                                <h5 class="fw-bold mb-3">Paiement par carte bancaire</h5>

                                <div class="payment-method-card mb-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                                        <h6 class="mt-2 mb-0">Paiement sécurisé</h6>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="cardholder" class="form-label">Nom du titulaire</label>
                                        <input type="text" class="form-control" id="cardholder" name="cardholder" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="card-element" class="form-label">Informations de carte</label>
                                        <div id="card-element"></div>
                                        <div id="card-errors" class="payment-error" role="alert"></div>
                                    </div>
                                </div>

                                <div class="form-check mt-4 mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        J'accepte les <a href="#" class="text-primary">conditions générales de vente</a> et la <a href="#" class="text-primary">politique de confidentialité</a>.
                                    </label>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" id="submit-button" class="btn btn-lg btn-payment">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" id="spinner"></span>
                                        <i class="fas fa-lock me-2" id="lock-icon"></i>Payer <?php echo number_format($total, 2, ',', ' '); ?> €
                                    </button>
                                    <a href="subscription.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Retour à l'abonnement
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser Stripe
        const stripe = Stripe('<?php echo $stripePublishableKey; ?>');
        const elements = stripe.elements();

        // Créer l'élément de carte
        const cardElement = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        
        // Monter l'élément de carte dans le DOM
        cardElement.mount('#card-element');

        // Gérer les erreurs de saisie de carte en temps réel
        cardElement.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Gérer la soumission du formulaire
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const spinner = document.getElementById('spinner');
        const lockIcon = document.getElementById('lock-icon');
        
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Vérifier si les conditions sont acceptées
            if (!document.getElementById('terms').checked) {
                alert('Veuillez accepter les conditions générales de vente.');
                return;
            }

            // Vérifier si le titulaire de la carte est renseigné
            const cardholder = document.getElementById('cardholder').value;
            if (!cardholder) {
                alert('Veuillez indiquer le nom du titulaire de la carte.');
                return;
            }

            // Désactiver le bouton et afficher le spinner
            submitButton.disabled = true;
            spinner.style.display = 'inline-block';
            lockIcon.style.display = 'none';

            // Créer un PaymentIntent côté serveur
            fetch('payment_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: <?php echo intval($total * 100); ?>, // Montant en centimes
                    description: 'Abonnement <?php echo $plan_details[$selected_plan]['name']; ?> - <?php echo $employee_count; ?> salariés',
                    invoice_number: '<?php echo $invoice_number; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    // En cas d'erreur lors de la création du PaymentIntent
                    document.getElementById('card-errors').textContent = data.error;
                    submitButton.disabled = false;
                    spinner.style.display = 'none';
                    lockIcon.style.display = 'inline-block';
                    return;
                }

                // Confirmer le paiement avec les éléments de carte
                stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: cardholder
                        }
                    }
                })
                .then(result => {
                    if (result.error) {
                        // En cas d'erreur lors de la confirmation du paiement
                        document.getElementById('card-errors').textContent = result.error.message;
                        submitButton.disabled = false;
                        spinner.style.display = 'none';
                        lockIcon.style.display = 'inline-block';
                    } else {
                        if (result.paymentIntent.status === 'succeeded') {
                            // Paiement réussi, rediriger vers la page de confirmation
                            window.location.href = 'payment_status.php?payment_intent=' + result.paymentIntent.id + '&redirect_status=succeeded';
                        }
                    }
                });
            })
            .catch(error => {
                document.getElementById('card-errors').textContent = 'Une erreur est survenue lors de la communication avec le serveur.';
                submitButton.disabled = false;
                spinner.style.display = 'none';
                lockIcon.style.display = 'inline-block';
                console.error('Error:', error);
            });
        });
    });
</script>