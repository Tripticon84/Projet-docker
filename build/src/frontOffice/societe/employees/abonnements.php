<?php
$title = "Gestion des Abonnements";

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['societe_id'])) {
    header('Location: /frontOffice/societe/login.php');
    exit();
}

// Inclusion du head avec gestion des erreurs
if (!@include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php') {
    die("Erreur: Impossible de charger le fichier head.php");
}

// Inclusion des fonctions nécessaires avec gestion d'erreurs
if (!@include_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/company.php") {
    die("Erreur: Impossible de charger le fichier company.php");
}

// Récupérer les informations de la société et de l'abonnement actuel avec gestion d'erreurs
$societyId = $_SESSION['societe_id'];
$subscriptionError = false;
$subscriptionErrorMessage = "";

try {
    // Récupérer les données réelles depuis la base de données avec une double vérification
    // Première tentative avec la fonction standard
    $subscription = function_exists('getCompanyActualSubscription') ? getCompanyActualSubscription($societyId) : null;
    
    // Si la première tentative échoue, essayons une requête directe à la base de données
    if (!$subscription) {
        $db = getDatabaseConnection();
        if ($db) {
            $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description, 
                    d.date_debut, d.date_fin, d.devis_id
                    FROM frais f
                    JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
                    JOIN devis d ON ifd.id_devis = d.devis_id
                    WHERE f.est_abonnement = 1 
                    AND d.id_societe = :societe_id
                    AND d.is_contract = 1
                    ORDER BY d.date_debut DESC
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['societe_id' => $societyId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    // Direct database query to ensure we get the active employee count
    $db = getDatabaseConnection();
    if ($db) {
        $sql = "SELECT COUNT(*) as count FROM collaborateur WHERE id_societe = :id AND desactivate = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $societyId]);
        $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $activeEmployees = intval($countResult['count']);
    } else {
        $activeEmployees = 0;
    }
    
    // Backup check using the function if direct query fails
    if ($activeEmployees === 0 && function_exists('countActiveEmployees')) {
        $activeEmployees = countActiveEmployees($societyId);
    }
    
    // Debug output to help diagnose count issues
    error_log("Active employees for society $societyId: $activeEmployees");

    // Valeurs par défaut si les fonctions ne sont pas disponibles
    if (!$employeeLimit || !isset($employeeLimit['max'])) {
        $employeeLimit = [
            'max' => 50,
            'current' => $activeEmployees,
            'status' => true,
            'remaining' => 50 - $activeEmployees
        ];
    }

    // Formater les données pour l'affichage
    $currentSubscription = [
        'plan' => isset($_SESSION['plan']) ? $_SESSION['plan'] : 'basic', // Valeur par défaut si non disponible
        'employee_count' => intval($activeEmployees),
        'employee_limit' => isset($employeeLimit['max']) ? intval($employeeLimit['max']) : 50,
        'expiration_date' => $subscription ? $subscription['date_fin'] : date('Y-m-d', strtotime('+1 year')),
        'montant' => $subscription ? $subscription['montant'] : 150 // Montant par défaut
    ];

    // Déterminer le plan en fonction du nombre d'employés max
    if ($employeeLimit['max'] <= 30) {
        $currentSubscription['plan'] = 'starter';
    } elseif ($employeeLimit['max'] <= 250) {
        $currentSubscription['plan'] = 'basic';
    } else {
        $currentSubscription['plan'] = 'premium';
    }
} catch (Exception $e) {
    // Log d'erreur plus détaillé
    error_log("Erreur dans abonnements.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    $subscriptionError = true;
    $subscriptionErrorMessage = $e->getMessage();
    
    // Valeurs par défaut en cas d'erreur
    $currentSubscription = [
        'plan' => 'basic',
        'employee_count' => 0,
        'employee_limit' => 50,
        'expiration_date' => date('Y-m-d', strtotime('+1 year')),
        'montant' => 150
    ];
    $subscription = null;
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar avec gestion des erreurs -->
        <?php 
        if (!@include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php') {
            echo '<div class="alert alert-danger">Impossible de charger la sidebar</div>';
        }
        ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Titre de la page -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion de votre abonnement</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Message d'information sur les erreurs éventuelles -->
            <?php if (!$subscription): ?>
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Des informations pourraient être manquantes ou imprécises car nous n'avons pas pu récupérer les détails de votre abonnement.
                <?php if ($subscriptionError): ?>
                <br><small>Erreur technique: <?php echo htmlspecialchars($subscriptionErrorMessage); ?></small>
                <?php endif; ?>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#manualSubscriptionModal">
                        <i class="fas fa-pencil-alt me-1"></i> Saisir manuellement mes données d'abonnement
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="retryLoadSubscription()">
                        <i class="fas fa-sync-alt me-1"></i> Réessayer
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Résumé de l'abonnement actuel -->
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="alert-heading">Votre abonnement actuel</h4>
                        <p class="mb-1"><strong>Plan:</strong> <?php echo ucfirst($currentSubscription['plan']); ?></p>
                        <p class="mb-1"><strong>Collaborateurs:</strong> <?php echo $currentSubscription['employee_count']; ?> / <?php echo $currentSubscription['employee_limit']; ?></p>
                        <p class="mb-1"><strong>Tarif actuel:</strong> <?php echo $currentSubscription['montant']; ?> € par collaborateur / an</p>
                        <p class="mb-0"><strong>Expiration:</strong> <?php echo date('d/m/Y', strtotime($currentSubscription['expiration_date'])); ?></p>
                        <?php if ($subscription): ?>
                        <p class="mb-0"><strong>Contrat:</strong> #<?php echo $subscription['devis_id']; ?> - Début: <?php echo date('d/m/Y', strtotime($subscription['date_debut'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section pour mettre à jour l'abonnement -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Augmenter votre limite de collaborateurs</h3>
                </div>
                <div class="card-body">
                    <p>Vous pouvez choisir un nouveau plan ou modifier le nombre de collaborateurs autorisés.</p>
                    <form id="subscription-update-form" action="/api/company/updateSubscription.php" method="POST">
                        <input type="hidden" name="society_id" value="<?php echo $societyId; ?>">
                        <input type="hidden" name="current_plan" value="<?php echo $currentSubscription['plan']; ?>">
                        
                        <!-- Choix des plans -->
                        <div class="row g-4 mb-4">
                            <!-- Starter Plan -->
                            <div class="col-md-4">
                                <div class="card h-100 <?php echo $currentSubscription['plan'] == 'starter' ? 'bg-light border-primary' : ''; ?>" 
                                    id="starter-plan" data-plan="starter" data-max-employees="30" data-price="180">
                                    <div class="card-header <?php echo $currentSubscription['plan'] == 'starter' ? 'bg-primary text-white' : ''; ?> text-center">
                                        <h3 class="fw-bold">Starter</h3>
                                        <h4 class="fw-bold">180 €</h4>
                                        <p>par collaborateur / an</p>
                                        <?php if ($currentSubscription['plan'] == 'starter'): ?>
                                            <span class="badge bg-success">Plan actuel</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body p-4">
                                        <ul class="list-unstyled">
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Jusqu'à 30 collaborateurs</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>2 activités avec prestataires</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>1 RDV médical (présentiel/visio)</li>
                                            <li class="mb-3"><i class="fas fa-times text-danger me-2"></i>Conseils hebdomadaires</li>
                                        </ul>
                                        <?php if ($currentSubscription['plan'] == 'starter'): ?>
                                            <button type="button" class="btn btn-outline-primary w-100" id="adjust-starter-btn">
                                                Ajuster le nombre de collaborateurs
                                            </button>
                                        <?php elseif ($currentSubscription['employee_count'] <= 30): ?>
                                            <button type="button" class="btn btn-outline-danger w-100" disabled>
                                                Rétrograder (contactez le support)
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-danger w-100" disabled>
                                                Non disponible (trop de collaborateurs)
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Basic Plan -->
                            <div class="col-md-4">
                                <div class="card h-100 <?php echo $currentSubscription['plan'] == 'basic' ? 'bg-light border-primary' : ''; ?>" 
                                    id="basic-plan" data-plan="basic" data-max-employees="250" data-price="150">
                                    <div class="card-header <?php echo $currentSubscription['plan'] == 'basic' ? 'bg-primary text-white' : ''; ?> text-center">
                                        <h3 class="fw-bold">Basic</h3>
                                        <h4 class="fw-bold">150 €</h4>
                                        <p>par collaborateur / an</p>
                                        <?php if ($currentSubscription['plan'] == 'basic'): ?>
                                            <span class="badge bg-success">Plan actuel</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body p-4">
                                        <ul class="list-unstyled">
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Jusqu'à 250 collaborateurs</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>3 activités avec prestataires</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>2 RDV médicaux</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Conseils hebdomadaires</li>
                                        </ul>
                                        <?php if ($currentSubscription['plan'] == 'basic'): ?>
                                            <button type="button" class="btn btn-outline-primary w-100" id="adjust-basic-btn">
                                                Ajuster le nombre de collaborateurs
                                            </button>
                                        <?php elseif ($currentSubscription['plan'] == 'starter'): ?>
                                            <button type="button" class="btn btn-success w-100" id="upgrade-to-basic-btn">
                                                Mettre à niveau
                                            </button>
                                        <?php elseif ($currentSubscription['employee_count'] <= 250): ?>
                                            <button type="button" class="btn btn-outline-danger w-100" disabled>
                                                Rétrograder (contactez le support)
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-danger w-100" disabled>
                                                Non disponible (trop de collaborateurs)
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Premium Plan -->
                            <div class="col-md-4">
                                <div class="card h-100 <?php echo $currentSubscription['plan'] == 'premium' ? 'bg-light border-primary' : ''; ?>" 
                                    id="premium-plan" data-plan="premium" data-min-employees="251" data-price="100">
                                    <div class="card-header <?php echo $currentSubscription['plan'] == 'premium' ? 'bg-primary text-white' : ''; ?> text-center">
                                        <h3 class="fw-bold">Premium</h3>
                                        <h4 class="fw-bold">100 €</h4>
                                        <p>par collaborateur / an</p>
                                        <?php if ($currentSubscription['plan'] == 'premium'): ?>
                                            <span class="badge bg-success">Plan actuel</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body p-4">
                                        <ul class="list-unstyled">
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Nombre illimité de collaborateurs</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>4 activités avec prestataires</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>3 RDV médicaux</li>
                                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Conseils hebdomadaires personnalisés</li>
                                        </ul>
                                        <?php if ($currentSubscription['plan'] == 'premium'): ?>
                                            <button type="button" class="btn btn-outline-primary w-100" id="adjust-premium-btn">
                                                Ajuster le nombre de collaborateurs
                                            </button>
                                        <?php elseif ($currentSubscription['plan'] == 'starter' || $currentSubscription['plan'] == 'basic'): ?>
                                            <button type="button" class="btn btn-success w-100" id="upgrade-to-premium-btn">
                                                Mettre à niveau
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section pour ajuster le nombre de collaborateurs -->
                        <div id="adjust-employees-section" class="card mb-4 d-none">
                            <div class="card-header">
                                <h4 id="adjust-section-title">Ajuster le nombre de collaborateurs</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_employee_count" class="form-label">Nouveau nombre de collaborateurs</label>
                                            <input type="number" class="form-control" id="new_employee_count" name="new_employee_count" 
                                                value="<?php echo $currentSubscription['employee_count']; ?>" min="1">
                                            <div class="form-text" id="employee-limit-help">
                                                Limite maximale: <span id="max-employee-count">250</span> collaborateurs
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h5>Résumé</h5>
                                                <p><strong>Plan:</strong> <span id="summary-plan">Basic</span></p>
                                                <p><strong>Prix par collaborateur:</strong> <span id="summary-price">150</span> €/an</p>
                                                <p><strong>Nouveaux collaborateurs:</strong> +<span id="summary-new-employees">0</span></p>
                                                <p><strong>Coût supplémentaire:</strong> <span id="summary-additional-cost">0</span> €</p>
                                                <div class="alert alert-info" id="prorata-notice">
                                                    Le coût supplémentaire sera calculé au prorata jusqu'à la date de renouvellement de votre abonnement.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success" id="confirm-upgrade-btn">
                                        <i class="fas fa-check-circle me-2"></i>Confirmer la mise à jour
                                    </button>
                                    <button type="button" class="btn btn-secondary ms-2" id="cancel-upgrade-btn">
                                        <i class="fas fa-times-circle me-2"></i>Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section pour mettre à niveau le plan -->
                        <div id="upgrade-plan-section" class="card mb-4 d-none">
                            <div class="card-header">
                                <h4 id="upgrade-section-title">Mettre à niveau vers le plan <span id="target-plan-name">Basic</span></h4>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    La mise à niveau de votre plan modifiera également vos limites et avantages. Veuillez confirmer cette mise à niveau.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="upgrade_employee_count" class="form-label">Nombre de collaborateurs</label>
                                            <input type="number" class="form-control" id="upgrade_employee_count" name="upgrade_employee_count" 
                                                value="<?php echo $currentSubscription['employee_count']; ?>" min="1">
                                            <div class="form-text" id="upgrade-employee-limit-help">
                                                Limite maximale: <span id="upgrade-max-employee-count">250</span> collaborateurs
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="target_plan" id="target-plan-input">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h5>Résumé de la mise à niveau</h5>
                                                <p><strong>Plan actuel:</strong> <?php echo ucfirst($currentSubscription['plan']); ?></p>
                                                <p><strong>Nouveau plan:</strong> <span id="upgrade-summary-plan">Basic</span></p>
                                                <p><strong>Nouveau prix par collaborateur:</strong> <span id="upgrade-summary-price">150</span> €/an</p>
                                                <p><strong>Coût total estimé:</strong> <span id="upgrade-summary-total-cost">0</span> €</p>
                                                <div class="alert alert-info">
                                                    Le coût supplémentaire sera calculé au prorata jusqu'à la date de renouvellement de votre abonnement.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success" id="confirm-plan-upgrade-btn">
                                        <i class="fas fa-check-circle me-2"></i>Confirmer la mise à niveau
                                    </button>
                                    <button type="button" class="btn btn-secondary ms-2" id="cancel-plan-upgrade-btn">
                                        <i class="fas fa-times-circle me-2"></i>Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- FAQ -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Questions fréquentes</h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Comment modifier mon abonnement?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Vous pouvez augmenter le nombre de collaborateurs en cliquant sur "Ajuster le nombre de collaborateurs" 
                                    dans votre plan actuel, ou vous pouvez mettre à niveau vers un plan supérieur en cliquant sur 
                                    "Mettre à niveau" dans le plan qui vous intéresse.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Puis-je réduire mon abonnement?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pour réduire votre abonnement ou passer à un plan inférieur, veuillez contacter notre équipe de support. 
                                    Notez que les réductions sont généralement appliquées lors du prochain renouvellement de votre abonnement.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Comment sont calculés les coûts supplémentaires?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Les coûts supplémentaires sont calculés au prorata en fonction du temps restant jusqu'à la date de 
                                    renouvellement de votre abonnement. Par exemple, si vous ajoutez des collaborateurs à mi-chemin de 
                                    votre période d'abonnement, vous ne paierez que la moitié du coût annuel pour ces nouveaux collaborateurs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal pour saisie manuelle des données d'abonnement -->
<div class="modal fade" id="manualSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Saisie manuelle des données d'abonnement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="manualSubscriptionForm">
                    <div class="mb-3">
                        <label for="manual_plan" class="form-label">Type d'abonnement</label>
                        <select id="manual_plan" class="form-select">
                            <option value="starter">Starter</option>
                            <option value="basic" selected>Basic</option>
                            <option value="premium">Premium</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="manual_employees" class="form-label">Nombre de collaborateurs actifs</label>
                        <input type="number" class="form-control" id="manual_employees" min="0" value="<?php echo $activeEmployees; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="manual_employee_limit" class="form-label">Limite de collaborateurs</label>
                        <input type="number" class="form-control" id="manual_employee_limit" min="1" value="250">
                    </div>
                    <div class="mb-3">
                        <label for="manual_montant" class="form-label">Prix par collaborateur (€/an)</label>
                        <input type="number" class="form-control" id="manual_montant" min="0" value="150">
                    </div>
                    <div class="mb-3">
                        <label for="manual_expiration" class="form-label">Date d'expiration</label>
                        <input type="date" class="form-control" id="manual_expiration" 
                            value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="applyManualSubscription()">Appliquer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // On affiche un message de console pour vérifier que le script se charge correctement
    console.log("Script abonnements.php chargé avec succès");
    
    // Variables globales avec valeurs par défaut au cas où
    const currentPlan = '<?php echo $currentSubscription['plan']; ?>' || 'basic';
    const currentEmployeeCount = <?php echo $currentSubscription['employee_count']; ?> || 0;
    const currentEmployeeLimit = <?php echo $currentSubscription['employee_limit']; ?> || 50;
    const currentPrice = <?php echo $currentSubscription['montant']; ?> || 150;
    
    // Vérifier que tous les éléments DOM existent avant de les utiliser
    const adjustSection = document.getElementById('adjust-employees-section');
    const upgradeSection = document.getElementById('upgrade-plan-section');
    
    if (!adjustSection || !upgradeSection) {
        console.error("Éléments DOM manquants dans la page");
        return;
    }
    
    // Inputs
    const newEmployeeCountInput = document.getElementById('new_employee_count');
    const upgradeEmployeeCountInput = document.getElementById('upgrade_employee_count');
    const targetPlanInput = document.getElementById('target-plan-input');
    
    // Summary elements
    const summaryPlan = document.getElementById('summary-plan');
    const summaryPrice = document.getElementById('summary-price');
    const summaryNewEmployees = document.getElementById('summary-new-employees');
    const summaryAdditionalCost = document.getElementById('summary-additional-cost');
    
    // Upgrade summary elements
    const upgradeSummaryPlan = document.getElementById('upgrade-summary-plan');
    const upgradeSummaryPrice = document.getElementById('upgrade-summary-price');
    const upgradeSummaryTotalCost = document.getElementById('upgrade-summary-total-cost');
    const targetPlanName = document.getElementById('target-plan-name');
    
    // Ajuster les boutons pour le plan actuel
    document.getElementById('adjust-starter-btn')?.addEventListener('click', function() {
        showAdjustSection('starter', 'Starter', 30, 180);
    });
    
    document.getElementById('adjust-basic-btn')?.addEventListener('click', function() {
        showAdjustSection('basic', 'Basic', 250, 150);
    });
    
    document.getElementById('adjust-premium-btn')?.addEventListener('click', function() {
        showAdjustSection('premium', 'Premium', 1000, 100);
    });
    
    // Mise à niveau vers d'autres plans
    document.getElementById('upgrade-to-basic-btn')?.addEventListener('click', function() {
        showUpgradeSection('basic', 'Basic', 250, 150);
    });
    
    document.getElementById('upgrade-to-premium-btn')?.addEventListener('click', function() {
        showUpgradeSection('premium', 'Premium', 1000, 100);
    });
    
    // Boutons d'annulation
    document.getElementById('cancel-upgrade-btn').addEventListener('click', function() {
        hideAllSections();
    });
    
    document.getElementById('cancel-plan-upgrade-btn').addEventListener('click', function() {
        hideAllSections();
    });
    
    // Input changes
    newEmployeeCountInput.addEventListener('input', function() {
        updateAdjustSummary();
    });
    
    upgradeEmployeeCountInput.addEventListener('input', function() {
        updateUpgradeSummary();
    });
    
    function showAdjustSection(planCode, planName, maxEmployees, price) {
        hideAllSections();
        adjustSection.classList.remove('d-none');
        
        // Mettre à jour les valeurs
        document.getElementById('adjust-section-title').textContent = `Ajuster le nombre de collaborateurs (Plan ${planName})`;
        document.getElementById('max-employee-count').textContent = maxEmployees;
        summaryPlan.textContent = planName;
        summaryPrice.textContent = price;
        
        // Définir la limite maximale
        newEmployeeCountInput.max = maxEmployees;
        
        // Mettre à jour le résumé
        updateAdjustSummary();
    }
    
    function showUpgradeSection(planCode, planName, maxEmployees, price) {
        hideAllSections();
        upgradeSection.classList.remove('d-none');
        
        // Mettre à jour les valeurs
        targetPlanName.textContent = planName;
        targetPlanInput.value = planCode;
        document.getElementById('upgrade-max-employee-count').textContent = maxEmployees;
        upgradeSummaryPlan.textContent = planName;
        upgradeSummaryPrice.textContent = price;
        
        // Définir la limite maximale
        upgradeEmployeeCountInput.max = maxEmployees;
        
        // Mettre à jour le résumé
        updateUpgradeSummary();
    }
    
    function hideAllSections() {
        adjustSection.classList.add('d-none');
        upgradeSection.classList.add('d-none');
    }
    
    function updateAdjustSummary() {
        const newCount = parseInt(newEmployeeCountInput.value) || 0;
        const additionalEmployees = Math.max(0, newCount - currentEmployeeCount);
        const price = parseInt(summaryPrice.textContent);
        const additionalCost = additionalEmployees * price;
        
        summaryNewEmployees.textContent = additionalEmployees;
        summaryAdditionalCost.textContent = additionalCost;
    }
    
    function updateUpgradeSummary() {
        const newCount = parseInt(upgradeEmployeeCountInput.value) || 0;
        const price = parseInt(upgradeSummaryPrice.textContent);
        const totalCost = newCount * price;
        
        upgradeSummaryTotalCost.textContent = totalCost;
    }
    
    // Fonction pour réessayer de charger les données d'abonnement
    window.retryLoadSubscription = function() {
        window.location.reload();
    };
    
    // Fonction pour appliquer la saisie manuelle d'abonnement
    window.applyManualSubscription = function() {
        const plan = document.getElementById('manual_plan').value;
        const employees = document.getElementById('manual_employees').value;
        const limit = document.getElementById('manual_employee_limit').value;
        const montant = document.getElementById('manual_montant').value;
        const expiration = document.getElementById('manual_expiration').value;
        
        // Stockage temporaire en session (sera remplacé par les valeurs réelles à la prochaine requête)
        sessionStorage.setItem('manualPlan', plan);
        sessionStorage.setItem('manualEmployees', employees);
        sessionStorage.setItem('manualLimit', limit);
        sessionStorage.setItem('manualMontant', montant);
        sessionStorage.setItem('manualExpiration', expiration);
        
        // Fermeture du modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('manualSubscriptionModal'));
        if (modal) {
            modal.hide();
        }
        
        // Rechargement de la page pour afficher les nouvelles valeurs
        window.location.reload();
    };
    
    // Initialize manual subscription form with current values
    const planSelect = document.getElementById('manual_plan');
    if (planSelect) {
        planSelect.value = currentPlan;
    }
});
</script>

<!-- Style spécifique à cette page -->
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-success {
        background: linear-gradient(to right, #28a745, #20c997);
        border: none;
    }
    
    .btn-outline-primary:hover {
        background: linear-gradient(to right, #007bff, #6610f2);
        color: white;
    }
    
    .alert-info {
        background-color: rgba(0, 123, 255, 0.1);
        border-color: rgba(0, 123, 255, 0.2);
        color: #0056b3;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(0, 123, 255, 0.1);
        color: #0056b3;
    }
    
    /* Style pour l'état de chargement */
    body.loading {
        cursor: wait;
    }
    
    body.loading::after {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(2px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    body.loading::before {
        content: "Chargement...";
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10000;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }
</style>
