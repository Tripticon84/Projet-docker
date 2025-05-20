<?php

session_start();

// Vérifier si les données de l'entreprise sont présentes
if (!isset($_SESSION['company_data'])) {
    header('Location: register.php');
    exit();
}


$title = "Inscription - Choisir votre abonnement";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';

?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5, #6fc2c0);
        background-attachment: fixed;
    }

    .subscription-card {
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

    .subscription-card:hover {
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

    .pricing-features {
        min-height: 300px;
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

    .plan-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .plan-card:hover {
        transform: scale(1.03);
    }

    .plan-card.selected {
        border: 3px solid #3a7bd5;
        transform: scale(1.05);
    }

    .btn-subscription {
        background: linear-gradient(to right, #3a7bd5, #6fc2c0);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-subscription:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(58, 123, 213, 0.4);
        color: white;
    }
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-10">
            <div class="card subscription-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Choisissez votre abonnement</h3>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 100px;">
                        <h4 class="mt-3 fw-bold text-primary">Sélectionnez la formule qui convient à votre entreprise</h4>
                    </div>

                    <?php if (!empty($subscription_errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erreur!</strong>
                            <ul class="mb-0">
                                <?php foreach ($subscription_errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Progress Steps -->
                    <div class="form-steps">
                        <div class="step">
                            <div class="step-circle">1</div>
                            <div class="step-text">Informations</div>
                        </div>
                        <div class="step">
                            <div class="step-circle active">2</div>
                            <div class="step-text active">Abonnement</div>
                        </div>
                        <div class="step">
                            <div class="step-circle">3</div>
                            <div class="step-text">Confirmation</div>
                        </div>
                    </div>

                    <form method="post" action="subscription_process.php" id="subscription-form">
                        <input type="hidden" name="plan" id="selected-plan" value="<?php echo htmlspecialchars($form_data['plan'] ?? ''); ?>">

                        <div class="row g-4">
                            <!-- Starter Plan -->
                            <div class="col-md-4">
                                <div class="card plan-card h-100" data-plan="starter" data-max-employees="30">
                                    <div class="card-header bg-primary text-white text-center">
                                        <h3 class="fw-bold">Starter</h3>
                                        <h4 class="fw-bold">180 €</h4>
                                        <p>par salarié / an</p>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="pricing-features">
                                            <ul class="list-unstyled">
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Jusqu'à 30 salariés</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>2 activités avec prestataires</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>1 RDV médical (présentiel/visio)</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>RDV supplémentaires : 75€/rdv</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Chatbot : 6 questions</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux fiches pratiques</li>
                                                <li class="mb-3"><i class="fas fa-times text-danger me-2"></i>Conseils hebdomadaires</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Événements/communautés</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Plan -->
                            <div class="col-md-4">
                                <div class="card plan-card h-100" data-plan="basic" data-max-employees="250" data-min-employees="1">
                                    <div class="card-header bg-primary text-white text-center">
                                        <span class="badge bg-warning position-absolute top-0 end-0 mt-2 me-2">Populaire</span>
                                        <h3 class="fw-bold">Basic</h3>
                                        <h4 class="fw-bold">150 €</h4>
                                        <p>par salarié / an</p>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="pricing-features">
                                            <ul class="list-unstyled">
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Jusqu'à 250 salariés</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>3 activités avec prestataires</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>2 RDV médicaux (présentiel/visio)</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>RDV supplémentaires : 75€/rdv</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Chatbot : 20 questions</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux fiches pratiques</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Conseils hebdomadaires (non personnalisés)</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Événements/communautés</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Premium Plan -->
                            <div class="col-md-4">
                                <div class="card plan-card h-100" data-plan="premium" data-min-employees="251">
                                    <div class="card-header bg-primary text-white text-center">
                                        <h3 class="fw-bold">Premium</h3>
                                        <h4 class="fw-bold">100 €</h4>
                                        <p>par salarié / an</p>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="pricing-features">
                                            <ul class="list-unstyled">
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>À partir de 251 salariés</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>4 activités avec prestataires</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>3 RDV médicaux (présentiel/visio)</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>RDV supplémentaires : 50€/rdv</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Chatbot : questions illimitées</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux fiches pratiques</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Conseils hebdomadaires personnalisés</li>
                                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Événements/communautés</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 mb-4 text-center">
                            <div class="col-md-6 mx-auto">
                                <div class="form-group">
                                    <label for="employee_count" class="form-label fw-bold mb-3">Nombre de collaborateurs :</label>
                                    
                                    <input type="number" class="form-control form-control-lg text-center" 
                                        name="employee_count" id="employee_count"
                                        value="<?php echo htmlspecialchars($form_data['employee_count'] ?? ''); ?>"
                                        placeholder="Entrez le nombre de collaborateurs" required min="1">
                                    <div class="form-text mt-2">
                                        Ce nombre nous permettra de générer votre devis initial.
                                    </div>
                                    <div id="employee-error" class="invalid-feedback" style="display: none;">
                                        Le nombre d'employés ne correspond pas au plan sélectionné.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-center">
                            <div class="d-grid gap-2 col-md-6 mx-auto">
                                <button type="submit" class="btn btn-lg btn-subscription" id="continue-btn" disabled>
                                    <i class="fas fa-check-circle me-2"></i>Continuer avec cette formule
                                </button>
                                <a href="register.php" class="btn btn-outline-secondary mt-2">
                                    <i class="fas fa-arrow-left me-2"></i>Retour aux informations
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const planCards = document.querySelectorAll('.plan-card');
        const selectedPlanInput = document.getElementById('selected-plan');
        const continueBtn = document.getElementById('continue-btn');
        const employeeInput = document.getElementById('employee_count');
        const employeeError = document.getElementById('employee-error');
        let selectedCard = null;

        // Pré-sélectionner le plan si défini dans form_data
        const preselectedPlan = selectedPlanInput.value;
        if (preselectedPlan) {
            const cardToSelect = document.querySelector(`.plan-card[data-plan="${preselectedPlan}"]`);
            if (cardToSelect) {
                cardToSelect.classList.add('selected');
                selectedCard = cardToSelect;
                continueBtn.disabled = !validateEmployeeCount();
            }
        }

        // Gestion de la sélection des plans
        planCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                planCards.forEach(c => c.classList.remove('selected'));

                // Add selected class to clicked card
                this.classList.add('selected');
                selectedCard = this;

                // Update hidden input value
                selectedPlanInput.value = this.dataset.plan;

                // Validate employee count
                validateEmployeeCount();

                // Enable continue button if valid
                continueBtn.disabled = !validateEmployeeCount();
            });
        });

        // Validation du nombre d'employés en fonction du plan
        employeeInput.addEventListener('input', function() {
            validateEmployeeCount();
            continueBtn.disabled = !validateEmployeeCount() || !selectedCard;
        });

        // Fonction de validation du nombre d'employés
        function validateEmployeeCount() {
            if (!selectedCard || !employeeInput.value) {
                employeeError.style.display = 'none';
                return false;
            }

            const count = parseInt(employeeInput.value);
            const plan = selectedCard.dataset.plan;
            const minEmployees = parseInt(selectedCard.dataset.minEmployees || 1);
            const maxEmployees = parseInt(selectedCard.dataset.maxEmployees || Number.MAX_SAFE_INTEGER);
            
            if (count < minEmployees || count > maxEmployees) {
                employeeInput.classList.add('is-invalid');
                employeeError.style.display = 'block';
                
                if (plan === 'starter') {
                    employeeError.textContent = 'Pour le plan Starter, le nombre de salariés doit être entre 1 et 30.';
                } else if (plan === 'basic') {
                    employeeError.textContent = 'Pour le plan Basic, le nombre de salariés doit être entre 1 et 250.';
                } else if (plan === 'premium') {
                    employeeError.textContent = 'Pour le plan Premium, le nombre de salariés doit être d\'au moins 251.';
                }
                
                return false;
            } else {
                employeeInput.classList.remove('is-invalid');
                employeeError.style.display = 'none';
                return true;
            }
        }

        // Validation au chargement de la page
        if (selectedCard && employeeInput.value) {
            validateEmployeeCount();
        }

        // Validation finale avant soumission
        document.getElementById('subscription-form').addEventListener('submit', function(e) {
            if (!validateEmployeeCount() || !selectedCard) {
                e.preventDefault();
                validateEmployeeCount();
            }
        });
    });
</script>