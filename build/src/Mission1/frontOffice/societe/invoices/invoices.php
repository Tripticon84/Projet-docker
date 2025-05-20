<?php
$title = "Gestion des Factures";

include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';

// Configuration Stripe - À placer dans un fichier de configuration sécurisé en production
$stripePublishableKey = "pk_test_51PAXnVP0arAN6IC0UPtpOnaMS4O1Qp153mO28fvjMR3Qzq486KDOiScwYdv3svHGcARvLi2MuE9dh7shZtRnd5cW00Vx0swc4q";
?>

<!-- Ajouter la bibliothèque Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Factures</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtres de recherche -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filtres</h5>
                </div>
                <div class="card-body">
                    <form id="invoiceFilterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Statut</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">Tous</option>
                                <option value="Payee">Payée</option>
                                <option value="Attente">En attente</option>
                                <option value="Annulé">Annulée</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateStartFilter" class="form-label">Date d'émission</label>
                            <input type="date" class="form-control" id="dateStartFilter">
                        </div>
                        <div class="col-md-3">
                            <label for="dateEndFilter" class="form-label">Date d'échéance</label>
                            <input type="date" class="form-control" id="dateEndFilter">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="applyFilters" class="btn btn-primary">Appliquer</button>
                            <button type="button" id="resetFilters" class="btn btn-secondary ms-2">Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des factures -->
            <div class="card">
                <div class="card-header">
                    <h5>Liste des factures</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date d'émission</th>
                                    <th>Date d'échéance</th>
                                    <th>Montant TTC</th>
                                    <th>TVA</th>
                                    <th>Montant HT</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="invoices-table">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement des factures...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal pour visualiser/modifier une facture -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewInvoiceModalLabel">Détails de la facture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="invoice-details">
                <!-- Le contenu sera injecté dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <!-- Bouton de paiement Stripe -->
                <button type="button" class="btn btn-primary" id="payWithStripe" style="display: none;">
                    <i class="fab fa-stripe"></i> Payer maintenant
                </button>
                <!-- Élément pour contenir le formulaire de paiement Stripe -->
                <div id="stripe-payment-element" style="display: none;" class="mt-3 w-100"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let societyId = <?php echo $_SESSION['societe_id']; ?>;
    let stripe = Stripe('<?php echo $stripePublishableKey; ?>');
    let currentInvoiceId = null;
    let elements = null;

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Charger toutes les factures
        loadAllInvoices(societyId);

        // Configuration des événements
        document.getElementById('refreshData').addEventListener('click', function() {
            loadAllInvoices(societyId);
        });

        document.getElementById('applyFilters').addEventListener('click', function() {
            applyInvoiceFilters();
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('invoiceFilterForm').reset();
            loadAllInvoices(societyId);
        });

        // Ajouter l'événement pour le paiement Stripe
        document.getElementById('payWithStripe').addEventListener('click', function() {
            setupStripePayment(currentInvoiceId);
        });
    });

    // Fonction pour initialiser le paiement Stripe
    function setupStripePayment(invoiceId) {
        const stripeContainer = document.getElementById('stripe-payment-element');
        stripeContainer.style.display = 'block';
        stripeContainer.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement du formulaire de paiement...</span></div>';

        // Créer une session de paiement côté serveur
        fetch('/api/invoice/create-payment-intent.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                invoiceId: invoiceId
            })
        })
        .then(response => response.json())
        .then(data => {
            const {clientSecret} = data;

            // Configuration des éléments Stripe
            const options = {
                clientSecret: clientSecret,
                appearance: {
                    theme: 'stripe',
                    labels: 'floating',
                    variables: {
                        colorPrimary: '#0d6efd',
                    },
                },
            };

            // Créer les éléments de paiement
            elements = stripe.elements(options);
            const paymentElement = elements.create('payment');
            paymentElement.mount('#stripe-payment-element');

            // Ajouter un formulaire de paiement
            stripeContainer.innerHTML = `
                <form id="payment-form" class="mt-3">
                    <div id="payment-element"></div>
                    <button class="btn btn-primary w-100 mt-3" id="submit-payment">Payer maintenant</button>
                </form>
            `;

            paymentElement.mount('#payment-element');

            // Gérer la soumission du paiement
            document.getElementById('submit-payment').addEventListener('click', function(e) {
                e.preventDefault();
                processPayment(clientSecret, invoiceId);
            });
        })
        .catch(error => {
            console.error('Erreur lors de l\'initialisation du paiement:', error);
            stripeContainer.innerHTML = '<div class="alert alert-danger">Une erreur est survenue lors de l\'initialisation du paiement.</div>';
        });
    }

    // Fonction pour traiter le paiement
    function processPayment(clientSecret, invoiceId) {
        const submitButton = document.getElementById('submit-payment');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';

        stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: window.location.origin + '/frontoffice/societe/invoices/payment-status.php?invoice_id=' + invoiceId,
            },
        })
        .then(function(result) {
            if (result.error) {
                // Afficher l'erreur à l'utilisateur
                submitButton.disabled = false;
                submitButton.innerHTML = 'Payer maintenant';
                alert(result.error.message);
            }
            // Le client sera redirigé vers return_url en cas de succès
        });
    }
</script>
