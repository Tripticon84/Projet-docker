<?php
$title = "Statut du paiement";
include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';

// Récupération des paramètres
$invoiceId = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
$paymentIntent = isset($_GET['payment_intent']) ? $_GET['payment_intent'] : '';
$paymentIntentClientSecret = isset($_GET['payment_intent_client_secret']) ? $_GET['payment_intent_client_secret'] : '';
$redirectStatus = isset($_GET['redirect_status']) ? $_GET['redirect_status'] : '';

// Vérifier que l'utilisateur est connecté et a accès à cette facture
if (!isset($_SESSION['societe_id']) || !$invoiceId) {
    header('Location: /frontOffice/societe/dashboard.php');
    exit;
}

// Configuration Stripe - À placer dans un fichier de configuration sécurisé en production
$stripeSecretKey = "sk_test_51PAXnVP0arAN6IC0PadwbyzrXw0PDcaVXPYAnvRnvvnR4ZSHdJm50ZeEqLuwkiwfBNkOxAXSTWRoIqcABmYrrLTN00BvKtVVvj";

// Initialiser Stripe
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Vérifier le paiement via l'API Stripe
$isPaymentValid = false;
$paymentDetails = null;

try {
    if ($paymentIntent) {
        $updateRows = modifyInvoiceState($invoiceId, 'Payee');

        if ($redirectStatus === 'succeeded') {
            // Si le paiement a été  redirigé avec succès, on peut mettre à jour le statut de la facture
            modifyInvoiceState($invoiceId, 'Payee');
        } elseif ($redirectStatus === 'failed') {
            modifyInvoiceState($invoiceId, 'Attente');
        }

        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentClientSecret);
        $redirectStatus = $paymentIntent->status;

        $payment = \Stripe\PaymentIntent::retrieve($paymentIntent);
        $paymentDetails = $payment;
        $isPaymentValid = ($payment->status === 'succeeded');

        // Si le paiement est réussi, mettre à jour le statut de la facture
        if ($isPaymentValid !== null) {
            // Utiliser directement la fonction du DAO au lieu de faire un appel API

            // Créer un résultat similaire à celui qu'on obtiendrait de l'API
            $updateResult = [
                'success' => ($updateRows > 0),
                'message' => ($updateRows > 0) ? 'Statut de la facture mis à jour avec succès' : 'Aucune modification effectuée'
            ];

        }
    }
} catch (Exception $e) {
    // Gérer l'erreur
    $error = $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Statut du paiement</h1>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Résultat du paiement</h5>
                </div>
                <div class="card-body">
                    <?php if ($redirectStatus === 'succeeded' || $isPaymentValid): ?>
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Paiement réussi!</h4>
                            <p>Votre paiement pour la facture #<?php echo $invoiceId; ?> a été traité avec succès.</p>
                            <hr>
                            <p class="mb-0">ID de transaction: <?php echo htmlspecialchars($paymentIntent); ?></p>
                            <?php if (isset($updateResult)): ?>
                                <p class="mt-2">Statut de la facture: <?php echo $updateResult['success'] ? 'Mise à jour réussie' : 'Erreur lors de la mise à jour'; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-times-circle me-2"></i> Échec du paiement</h4>
                            <p>Votre paiement pour la facture #<?php echo $invoiceId; ?> n'a pas pu être traité.</p>
                            <hr>
                            <p class="mb-0">
                                <?php if (isset($error)): ?>
                                    Erreur: <?php echo htmlspecialchars($error); ?>
                                <?php else: ?>
                                    Veuillez vérifier vos informations de paiement et réessayer.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h5>Que souhaitez-vous faire maintenant?</h5>
                        <div class="d-flex gap-2 mt-3">
                            <a href="/frontOffice/societe/invoices/invoices.php" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i> Retour à la liste des factures
                            </a>
                            <?php if ($redirectStatus !== 'succeeded' && !$isPaymentValid): ?>
                                <a href="javascript:void(0);" onclick="retryPayment(<?php echo $invoiceId; ?>)" class="btn btn-warning">
                                    <i class="fas fa-sync me-1"></i> Réessayer le paiement
                                </a>
                            <?php endif; ?>
                            <a href="/frontOffice/societe/dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-home me-1"></i> Tableau de bord
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($paymentDetails && is_object($paymentDetails)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Détails du paiement</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">ID du paiement</th>
                                    <td><?php echo htmlspecialchars($paymentDetails->id); ?></td>
                                </tr>
                                <tr>
                                    <th>Montant</th>
                                    <td><?php echo number_format($paymentDetails->amount / 100, 2, ',', ' '); ?> €</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        <span class="badge bg-<?php echo $paymentDetails->status === 'succeeded' ? 'success' : 'danger'; ?>">
                                            <?php echo htmlspecialchars($paymentDetails->status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td><?php echo date('d/m/Y H:i:s', $paymentDetails->created); ?></td>
                                </tr>
                                <tr>
                                    <th>Devise</th>
                                    <td><?php echo strtoupper(htmlspecialchars($paymentDetails->currency)); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    function retryPayment(invoiceId) {
        // Redirection vers la page des factures avec l'ID de la facture
        window.location.href = '/frontOffice/societe/invoices/invoices.php?retry_payment=' + invoiceId;
    }
</script>
