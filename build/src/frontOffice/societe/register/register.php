<?php
$title = "Inscription";

require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';

// Récupération des erreurs et des données du formulaire depuis GET
$register_errors = [];
$form_data = [];

if (isset($_GET['errors']) && !empty($_GET['errors'])) {
    $register_errors = json_decode(urldecode($_GET['errors']), true);
}

if (isset($_GET['form_data']) && !empty($_GET['form_data'])) {
    $form_data = json_decode(urldecode($_GET['form_data']), true);
}

?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5, #6fc2c0);
        background-attachment: fixed;
    }

    .register-card {
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

    .register-card:hover {
        transform: translateY(-5px);
        box-shadow: rgba(111, 194, 192, 0.4) 5px 5px,
                    rgba(111, 194, 192, 0.3) 10px 10px,
                    rgba(111, 194, 192, 0.2) 15px 15px,
                    rgba(111, 194, 192, 0.1) 20px 20px,
                    rgba(111, 194, 192, 0.05) 25px 25px;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1rem !important;
        background: linear-gradient(to right, #3a7bd5, #6fc2c0) !important;
        border: none;
    }

    .register-btn {
        background: linear-gradient(to right, #3a7bd5, #6fc2c0);
        border: none;
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 0.7rem 0;
    }

    .divider::before, .divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #ced4da;
    }

    .divider span {
        padding: 0 10px;
        color: #6c757d;
        font-size: 0.8rem;
    }

    .form-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step::after {
        content: '';
        position: absolute;
        top: 12px;
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
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background-color: #dee2e6;
        color: #6c757d;
        margin-bottom: 0.3rem;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .step-circle.active {
        background-color: #3a7bd5;
        color: white;
    }

    .step-text {
        font-size: 0.7rem;
        color: #6c757d;
    }

    .step-text.active {
        color: #3a7bd5;
        font-weight: bold;
    }

    /* More compact form */
    .form-label {
        margin-bottom: 0.2rem;
        font-size: 0.9rem;
    }

    .form-control, .input-group-text {
        padding: 0.4rem 0.75rem;
    }

    .mb-3 {
        margin-bottom: 0.7rem !important;
    }
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card register-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center fs-4">Créer un compte entreprise</h3>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($register_errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erreur!</strong>
                            <ul class="mb-0">
                                <?php foreach ($register_errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mb-2">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 60px;">
                        <h4 class="mt-2 fw-bold text-primary fs-5">Rejoignez Business Care</h4>
                    </div>

                    <!-- Progress Steps -->
                    <div class="form-steps">
                        <div class="step">
                            <div class="step-circle active">1</div>
                            <div class="step-text active">Informations</div>
                        </div>
                        <div class="step">
                            <div class="step-circle">2</div>
                            <div class="step-text">Validation</div>
                        </div>
                        <div class="step">
                            <div class="step-circle">3</div>
                            <div class="step-text">Confirmation</div>
                        </div>
                    </div>

                    <form method="post" action="register_process.php">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="nom" class="form-label">Nom de l'entreprise</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-building text-primary"></i></span>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($form_data['nom'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="siret" class="form-label">Numéro SIRET</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card text-primary"></i></span>
                                    <input type="text" class="form-control" id="siret" name="siret" pattern="[0-9]{14}" maxlength="14" value="<?php echo htmlspecialchars($form_data['siret'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="adresse" class="form-label">Adresse</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($form_data['adresse'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="email" class="form-label">Email professionnel</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($form_data['telephone'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="contact_person" class="form-label">Nom du contact principal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($form_data['contact_person'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="confirm_password" class="form-label">Confirmation</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label small" for="terms">J'accepte les <a href="#" class="text-decoration-none">conditions générales</a></label>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn register-btn text-white">
                                <i class="fas fa-user-plus me-2"></i>Créer mon compte
                            </button>
                        </div>
                    </form>

                    <div class="divider">
                        <span>OU</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/frontOffice/societe/login/login.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Se connecter
                        </a>
                        <a href="/frontOffice/index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const form = document.querySelector('form');

        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                confirmPassword.focus();
            }
        });
    });
</script>
