<?php
$title = "Inscription - Espace Prestataire";

include_once $_SERVER['DOCUMENT_ROOT'] . "/frontOffice/prestataire/includes/head.php";


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
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%);
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
        box-shadow: rgba(109, 91, 152, 0.4) 5px 5px,
                    rgba(109, 91, 152, 0.3) 10px 10px,
                    rgba(109, 91, 152, 0.2) 15px 15px,
                    rgba(109, 91, 152, 0.1) 20px 20px,
                    rgba(109, 91, 152, 0.05) 25px 25px;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1rem !important;
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%) !important;
        border: none;
    }

    .register-btn {
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%);
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

    .step-text {
        font-size: 0.7rem;
        color: #6c757d;
    }
    
    .step-text.active {
        color: #3a7bd5;
        font-weight: bold;
    }

    .form-control, .input-group-text {
        padding: 0.4rem 0.75rem;
    }

    .mb-3 {
        margin-bottom: 0.7rem !important;
    }
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card register-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Devenir Prestataire</h3>
                </div>
                <div class="card-body p-4">
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

                    <div class="text-center mb-4">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 80px;">
                        <h4 class="mt-3 fw-bold text-primary">Rejoignez notre réseau de prestataires</h4>
                        <p class="text-muted">Votre candidature sera examinée par nos administrateurs</p>
                    </div>

                    <form method="post" action="register_process.php" enctype="multipart/form-data">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($form_data['prenom'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($form_data['nom'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email professionnel</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type de service</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-briefcase text-primary"></i></span>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="" selected disabled>Choisissez votre domaine d'expertise</option>
                                    <option value="Coach bien-être" <?php echo (isset($form_data['type']) && $form_data['type'] === 'Coach bien-être') ? 'selected' : ''; ?>>Coach bien-être</option>
                                    <option value="Psychologue" <?php echo (isset($form_data['type']) && $form_data['type'] === 'Psychologue') ? 'selected' : ''; ?>>Psychologue</option>
                                    <option value="Formateur" <?php echo (isset($form_data['type']) && $form_data['type'] === 'Formateur') ? 'selected' : ''; ?>>Formateur</option>
                                    <option value="Nutritionniste" <?php echo (isset($form_data['type']) && $form_data['type'] === 'Nutritionniste') ? 'selected' : ''; ?>>Nutritionniste</option>
                                    <option value="Coach sportif" <?php echo (isset($form_data['type']) && $form_data['type'] === 'Coach sportif') ? 'selected' : ''; ?>>Coach sportif</option>
                                    <option value="Autre" <?php echo (isset($form_data['type']) && $form_data['type'] === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tarif" class="form-label">Tarif horaire (€)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-euro-sign text-primary"></i></span>
                                <input type="number" class="form-control" id="tarif" name="tarif" min="0" step="0.01" value="<?php echo htmlspecialchars($form_data['tarif'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description de vos services</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-comment text-primary"></i></span>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-text">Décrivez vos services, votre expérience et vos qualifications</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut_disponibilite" class="form-label">Date de début de disponibilité</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" class="form-control" id="date_debut_disponibilite" name="date_debut_disponibilite" value="<?php echo htmlspecialchars($form_data['date_debut_disponibilite'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_fin_disponibilite" class="form-label">Date de fin de disponibilité</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" class="form-control" id="date_fin_disponibilite" name="date_fin_disponibilite" value="<?php echo htmlspecialchars($form_data['date_fin_disponibilite'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                </div>
                                <div class="form-text">8 caractères minimum</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmation du mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label small" for="terms">J'accepte les <a href="#" class="text-decoration-none">conditions générales</a></label>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn register-btn text-white">
                                <i class="fas fa-user-plus me-2"></i>Soumettre ma candidature
                            </button>
                        </div>
                    </form>

                    <div class="divider">
                        <span>OU</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/frontOffice/prestataire/login/login.php" class="btn btn-sm btn-outline-primary">
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
        const dateDebut = document.getElementById('date_debut_disponibilite');
        const dateFin = document.getElementById('date_fin_disponibilite');
        
        
        const today = new Date().toISOString().split('T')[0];
        dateDebut.min = today;
        dateFin.min = today;

        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                confirmPassword.focus();
                return;
            }

           
            if (dateDebut.value && dateFin.value && dateDebut.value > dateFin.value) {
                e.preventDefault();
                alert('La date de fin doit être après la date de début');
                dateFin.focus();
            }
        });
    });
</script>
