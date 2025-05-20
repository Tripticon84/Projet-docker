<?php
$title = "Demande de devis";
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';

// Vérifier que employee_count existe dans la session
if (!isset($_SESSION['employee_count'])) {
    $_SESSION['employee_count'] = 5; // Valeur par défaut
}

// Récupérer l'ID de la société depuis la session
$societe_id = $_SESSION['societe_id'];

// Récupérer les informations de l'abonnement actuel
$subscription = getCompanyActualSubscription($societe_id);

// Vérifier que l'abonnement existe
if (!$subscription) {
    // Journal d'erreur pour le débogage
    error_log("Aucun abonnement trouvé pour la société ID: " . $societe_id);
    
    // On peut définir un message d'alerte à afficher
    $alertMessage = "Aucun abonnement actif trouvé. Les calculs seront basés sur les informations par défaut.";
}

// Récupérer les informations du plan d'abonnement actuel
$currentPlan = isset($_SESSION['plan']) ? $_SESSION['plan'] : 'basic';

// Prix annuels par employé selon le plan
$annualPricePerEmployee = [
    'starter' => 180,
    'basic' => 150,
    'premium' => 100
];

// Prix par employé pour le plan actuel
$currentAnnualPricePerEmployee = isset($annualPricePerEmployee[$currentPlan]) ? $annualPricePerEmployee[$currentPlan] : 150;

// Récupérer et formater les dates du contrat
$contractStartDate = isset($subscription['date_debut']) ? $subscription['date_debut'] : date('Y-m-d');
$contractEndDate = isset($subscription['date_fin']) ? $subscription['date_fin'] : date('Y-m-d', strtotime('+1 year'));

// Calculer les mois restants dans le contrat
$today = new DateTime();
$endDate = new DateTime($contractEndDate);
$interval = $today->diff($endDate);
$monthsRemaining = ($interval->y * 12) + $interval->m;
if ($interval->d > 0) $monthsRemaining++; // Compte le mois partiel

// Si pas de contrat trouvé ou calcul erroné, calculer les mois restants dans l'année
if ($monthsRemaining <= 0 || !$subscription) {
    $currentMonth = date('n'); // 1-12 pour Jan-Déc
    $monthsElapsed = $currentMonth - 1; // Mois déjà passés (en comptant à partir de 0)
    $monthsRemaining = 12 - $monthsElapsed; // Mois restants dans l'année
}

// Récupérer le nom de l'abonnement
$subscriptionName = isset($subscription['nom']) ? $subscription['nom'] : ucfirst($currentPlan);
$subscriptionDesc = isset($subscription['description']) ? $subscription['description'] : "Plan " . ucfirst($currentPlan);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (isset($alertMessage)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $alertMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Demande de devis</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="estimates.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Votre abonnement actuel: <?php echo $subscriptionName; ?></h5>
                        </div>
                        <div class="card-body">
                            <p><?php echo $subscriptionDesc; ?></p>
                            <hr>
                            <p><strong>Nombre actuel d'employés:</strong> <?php echo $_SESSION['employee_count']; ?></p>
                            <p><strong>Prix par employé:</strong> <?php echo $currentAnnualPricePerEmployee; ?> € / an / employé</p>
                            <p><strong>Coût annuel actuel:</strong> <?php echo number_format($_SESSION['employee_count'] * $currentAnnualPricePerEmployee, 2); ?> €</p>
                            <hr>
                            <p><strong>Date de début du contrat:</strong> <?php echo date('d/m/Y', strtotime($contractStartDate)); ?></p>
                            <p><strong>Date de fin du contrat:</strong> <?php echo date('d/m/Y', strtotime($contractEndDate)); ?></p>
                            <p class="text-info"><i class="fas fa-info-circle"></i> Mois restants dans le contrat: <strong><?php echo $monthsRemaining; ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ajuster le nombre d'employés</h5>
                        </div>
                        <div class="card-body">
                            <form id="employeeCountForm">
                                <div class="mb-3">
                                    <label for="employeeCount" class="form-label">Nombre d'employés souhaité</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary" id="decreaseEmployees">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" id="employeeCount" 
                                               value="<?php echo $_SESSION['employee_count']; ?>" min="<?php echo $_SESSION['employee_count']; ?>">
                                        <button type="button" class="btn btn-outline-secondary" id="increaseEmployees">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Vous ne pouvez qu'augmenter le nombre d'employés.</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-calculator"></i> Le coût sera calculé au prorata des <?php echo $monthsRemaining; ?> mois restants dans votre contrat.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5>Récapitulatif</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Plan:</strong> <span id="planDisplay"><?php echo $subscriptionName; ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Nombre d'employés actuel:</strong> <span id="currentEmployeeDisplay"><?php echo $_SESSION['employee_count']; ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Nombre d'employés souhaité:</strong> <span id="newEmployeeDisplay"><?php echo $_SESSION['employee_count']; ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Employés supplémentaires:</strong> <span id="additionalEmployeeDisplay">0</span>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <strong>Coût annuel actuel:</strong> 
                                <span id="currentCostDisplay">
                                    <?php echo number_format($_SESSION['employee_count'] * $currentAnnualPricePerEmployee, 2); ?> €
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Coût annuel avec nouveaux employés:</strong> 
                                <span id="newAnnualCostDisplay">
                                    <?php echo number_format($_SESSION['employee_count'] * $currentAnnualPricePerEmployee, 2); ?> €
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Différence annuelle:</strong> <span id="annualDifferenceDisplay">0.00 €</span>
                            </div>
                            <hr>
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Montant à payer (au prorata):</strong> 
                                    <span id="proratedCostDisplay" class="h5 mb-0">0.00 €</span>
                                </div>
                                <small class="d-block mt-2">Pour les <?php echo $monthsRemaining; ?> mois restants du contrat</small>
                            </div>
                            <button type="button" class="btn btn-primary w-100" id="submitEstimate">
                                <i class="fas fa-paper-plane"></i> Soumettre le devis
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables
    const currentEmployeeCount = <?php echo $_SESSION['employee_count']; ?>;
    const annualPricePerEmployee = <?php echo $currentAnnualPricePerEmployee; ?>;
    const monthsRemaining = <?php echo $monthsRemaining; ?>;
    const contractStartDate = "<?php echo $contractStartDate; ?>";
    const contractEndDate = "<?php echo $contractEndDate; ?>";
    
    // Éléments du DOM
    const employeeCountInput = document.getElementById('employeeCount');
    const decreaseBtn = document.getElementById('decreaseEmployees');
    const increaseBtn = document.getElementById('increaseEmployees');
    const submitBtn = document.getElementById('submitEstimate');
    
    // Éléments d'affichage
    const newEmployeeDisplay = document.getElementById('newEmployeeDisplay');
    const additionalEmployeeDisplay = document.getElementById('additionalEmployeeDisplay');
    const currentCostDisplay = document.getElementById('currentCostDisplay');
    const newAnnualCostDisplay = document.getElementById('newAnnualCostDisplay');
    const annualDifferenceDisplay = document.getElementById('annualDifferenceDisplay');
    const proratedCostDisplay = document.getElementById('proratedCostDisplay');
    
    // Fonctions pour augmenter/diminuer le nombre d'employés
    decreaseBtn.addEventListener('click', function() {
        if (parseInt(employeeCountInput.value) > currentEmployeeCount) {
            employeeCountInput.value = parseInt(employeeCountInput.value) - 1;
            updateCostCalculations();
        }
    });
    
    increaseBtn.addEventListener('click', function() {
        employeeCountInput.value = parseInt(employeeCountInput.value) + 1;
        updateCostCalculations();
    });
    
    // Mise à jour lorsque la valeur change directement
    employeeCountInput.addEventListener('change', function() {
        // Vérifier que la valeur n'est pas inférieure au nombre actuel
        if (parseInt(this.value) < currentEmployeeCount) {
            this.value = currentEmployeeCount;
        }
        updateCostCalculations();
    });
    
    // Fonction principale de mise à jour des calculs
    function updateCostCalculations() {
        const newEmployeeCount = parseInt(employeeCountInput.value);
        const additionalEmployees = newEmployeeCount - currentEmployeeCount;
        
        // Déterminer automatiquement le plan en fonction du nombre d'employés
        let newPlan, newPricePerEmployee;
        
        if (newEmployeeCount <= 30) {
            newPlan = 'starter';
            newPricePerEmployee = 180;
        } else if (newEmployeeCount <= 250) {
            newPlan = 'basic';
            newPricePerEmployee = 150;
        } else {
            newPlan = 'premium';
            newPricePerEmployee = 100;
        }
        
        // Mettre à jour l'affichage du plan si nécessaire
        const planDisplay = document.getElementById('planDisplay');
        if (newPlan !== '<?php echo $currentPlan; ?>') {
            planDisplay.innerHTML = newPlan.charAt(0).toUpperCase() + newPlan.slice(1) + 
                ' <span class="badge bg-warning">Changement automatique</span>';
        } else {
            planDisplay.textContent = '<?php echo $subscriptionName; ?>';
        }
        
        // Calculer les coûts annuels
        const currentAnnualCost = currentEmployeeCount * annualPricePerEmployee;
        const newAnnualCost = newEmployeeCount * newPricePerEmployee;
        const annualDifference = newAnnualCost - currentAnnualCost;
        
        // Calculer le coût proratisé pour les mois restants du contrat
        const proratedCost = (annualDifference * monthsRemaining) / 12;
        
        // Mettre à jour les affichages
        newEmployeeDisplay.textContent = newEmployeeCount;
        additionalEmployeeDisplay.textContent = additionalEmployees;
        currentCostDisplay.textContent = currentAnnualCost.toFixed(2) + ' €';
        newAnnualCostDisplay.textContent = newAnnualCost.toFixed(2) + ' €';
        annualDifferenceDisplay.textContent = annualDifference.toFixed(2) + ' €';
        proratedCostDisplay.textContent = proratedCost.toFixed(2) + ' €';
        
        // Stocker le nouveau plan et prix pour la soumission
        document.getElementById('employeeCountForm').dataset.newPlan = newPlan;
        document.getElementById('employeeCountForm').dataset.newPrice = newPricePerEmployee;
    }
    
    // Initialiser les calculs
    updateCostCalculations();
    
    // Gestion de la soumission du devis
    submitBtn.addEventListener('click', function() {
        const newEmployeeCount = parseInt(employeeCountInput.value);
        const additionalEmployees = newEmployeeCount - currentEmployeeCount;
        
        // Ne pas soumettre si aucun changement
        if (additionalEmployees === 0) {
            alert('Veuillez augmenter le nombre d\'employés pour générer un devis.');
            return;
        }
        
        // Calculer les données pour la redirection
        const currentAnnualCost = currentEmployeeCount * annualPricePerEmployee;
        const newAnnualCost = newEmployeeCount * annualPricePerEmployee;
        const annualDifference = newAnnualCost - currentAnnualCost;
        const proratedCost = (annualDifference * monthsRemaining) / 12;
        
        // Formater la date du jour (nouvelle date de début)
        const today = new Date();
        const formattedToday = today.getFullYear() + '-' + 
                              String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(today.getDate()).padStart(2, '0');
        
        // Rediriger vers new_estimate_process.php avec tous les paramètres
        window.location.href = 'new_estimate_process.php?' + 
            'employee_count=' + newEmployeeCount + 
            '&additional_employees=' + additionalEmployees + 
            '&current_cost=' + currentAnnualCost.toFixed(2) + 
            '&new_annual_cost=' + newAnnualCost.toFixed(2) + 
            '&difference=' + annualDifference.toFixed(2) + 
            '&prorated_cost=' + proratedCost.toFixed(2) + 
            '&start_date=' + formattedToday + 
            '&end_date=' + contractEndDate + 
            '&months_remaining=' + monthsRemaining;
    });
});
</script>