<?php


// Rediriger si l'utilisateur accède directement à cette page sans avoir terminé l'inscription


$title = "Inscription réussie";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
if (!isset($_SESSION['registration_success'])) {
    header('Location: register.php');
    exit();
}

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5, #6fc2c0);
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
        box-shadow: rgba(111, 194, 192, 0.4) 5px 5px,
                    rgba(111, 194, 192, 0.3) 10px 10px,
                    rgba(111, 194, 192, 0.2) 15px 15px,
                    rgba(111, 194, 192, 0.1) 20px 20px,
                    rgba(111, 194, 192, 0.05) 25px 25px;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem !important;
        background: linear-gradient(to right, #3a7bd5, #6fc2c0) !important;
        border: none;
    }

    .btn-login {
        background: linear-gradient(to right, #3a7bd5, #6fc2c0);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(58, 123, 213, 0.4);
        color: white;
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
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card confirmation-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Inscription réussie !</h3>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 100px;">
                        <div class="mt-4 mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        <h4 class="mt-3 fw-bold text-primary">Félicitations !</h4>
                        <p class="lead">Votre compte entreprise a été créé avec succès.</p>
                        <p>Vous pouvez maintenant vous connecter à votre espace et commencer à utiliser nos services.</p>
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

                    <div class="d-grid gap-2 col-md-8 mx-auto mt-4">
                        <a href="/frontOffice/societe/login/login.php" class="btn btn-login btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter maintenant
                        </a>
                        <a href="/frontOffice/index.php" class="btn btn-outline-secondary mt-2">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Clear the registration success flag
unset($_SESSION['registration_success']);
?>
