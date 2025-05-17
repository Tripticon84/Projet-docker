<?php
$title = "Statut du don";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

$associationId = isset($_GET['association_id']) ? intval($_GET['association_id']) : 0;
$paymentIntent = isset($_GET['payment_intent']) ? $_GET['payment_intent'] : '';
$redirectStatus = isset($_GET['redirect_status']) ? $_GET['redirect_status'] : '';

if (!isset($_SESSION['collaborateur_id']) || !$associationId) {
    header('Location: /frontOffice/employee/associations.php');
    exit;
}

$stripeSecretKey = "sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm";
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
\Stripe\Stripe::setApiKey($stripeSecretKey);

try {
    if ($paymentIntent) {
        $payment = \Stripe\PaymentIntent::retrieve($paymentIntent);
        
        if ($payment->status === 'succeeded') {
            // Enregistrer le don dans la base de données
            $db = getDatabaseConnection();
            $stmt = $db->prepare("INSERT INTO don (montant, date, id_collaborateur, id_association) VALUES (:montant, NOW(), :id_collaborateur, :id_association)");
            $stmt->execute([
                'montant' => $payment->amount / 100,
                'id_collaborateur' => $_SESSION['collaborateur_id'],
                'id_association' => $associationId
            ]);
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5>Statut du don</h5>
        </div>
        <div class="card-body">
            <?php if ($redirectStatus === 'succeeded'): ?>
                <div class="alert alert-success">
                    <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Don effectué avec succès!</h4>
                    <p>Merci pour votre générosité.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <h4 class="alert-heading"><i class="fas fa-times-circle"></i> Échec du don</h4>
                    <p>Une erreur est survenue lors du traitement de votre don.</p>
                    <?php if (isset($error)): ?>
                        <p>Détail: <?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="/frontOffice/employee/associations.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour aux associations
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/footer.php'; ?>
