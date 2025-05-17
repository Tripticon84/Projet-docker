<?php
session_start();
$title = "Candidature soumise";


if (!isset($_SESSION['registration_success'])) {
    header('Location: register.php');
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/frontOffice/prestataire/includes/head.php";


$waitingApproval = isset($_SESSION['waiting_approval']) && $_SESSION['waiting_approval'] === true;
?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%);
        background-attachment: fixed;
    }

    .confirmation-card {
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

    .confirmation-card:hover {
        transform: translateY(-5px);
        box-shadow: rgba(109, 91, 152, 0.4) 5px 5px,
                    rgba(109, 91, 152, 0.3) 10px 10px,
                    rgba(109, 91, 152, 0.2) 15px 15px,
                    rgba(109, 91, 152, 0.1) 20px 20px,
                    rgba(109, 91, 152, 0.05) 25px 25px;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem !important;
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%) !important;
        border: none;
    }

    .login-btn {
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(58, 123, 213, 0.4);
        color: white;
    }
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card confirmation-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Candidature soumise avec succès !</h3>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 100px;">
                        <div class="mt-4 mb-4">
                            <i class="fas fa-paper-plane text-success fa-5x"></i>
                        </div>
                        <h4 class="mt-3 fw-bold text-primary">
                            <?php echo $waitingApproval ? 'Votre compte est en attente d\'approbation' : 'Merci pour votre candidature !'; ?>
                        </h4>
                        <p class="lead">
                            <?php echo $waitingApproval ? 'Vous vous êtes connecté avec un compte en attente de validation' : 'Votre demande a été transmise à nos administrateurs.'; ?>
                        </p>
                        
                        <div class="alert alert-info mt-3">
                            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>
                                <?php echo $waitingApproval ? 'État de votre candidature' : 'Prochaines étapes'; ?>
                            </h5>
                            <hr>
                            <p class="mb-0">
                                Votre candidature est en cours d'examen par notre équipe d'administrateurs. 
                                Vous recevrez une notification par email à l'adresse <strong><?php echo htmlspecialchars($_SESSION['provider_email'] ?? 'fournie'); ?></strong> 
                                une fois que votre compte aura été validé.
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 col-md-8 mx-auto mt-4">
                        <a href="/frontOffice/index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

unset($_SESSION['registration_success']);
unset($_SESSION['provider_email']);
unset($_SESSION['waiting_approval']);
?>
