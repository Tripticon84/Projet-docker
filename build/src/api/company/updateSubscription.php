<?php
header("Content-Type: application/json");

// Démarrer la session et vérifier l'authentification
session_start();
if (!isset($_SESSION['societe_id'])) {
    echo json_encode(["success" => false, "message" => "Non autorisé. Veuillez vous connecter."]);
    exit();
}

// Inclure les fichiers nécessaires
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/company.php";

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si les données ne sont pas au format JSON, essayer de les récupérer depuis $_POST
    $data = $_POST;
}

// Vérifier que les données requises sont présentes
$societyId = isset($data['society_id']) ? intval($data['society_id']) : $_SESSION['societe_id'];
$targetPlan = isset($data['target_plan']) ? $data['target_plan'] : null;
$newEmployeeCount = isset($data['new_employee_count']) ? intval($data['new_employee_count']) : null;
$upgradeEmployeeCount = isset($data['upgrade_employee_count']) ? intval($data['upgrade_employee_count']) : null;
$currentPlan = isset($data['current_plan']) ? $data['current_plan'] : null;

// Vérifier que la société connectée est bien celle qu'on essaie de mettre à jour
if ($societyId != $_SESSION['societe_id']) {
    echo json_encode(["success" => false, "message" => "Vous n'êtes pas autorisé à modifier cet abonnement."]);
    exit();
}

try {
    $db = getDatabaseConnection();

    // Récupérer l'abonnement actuel
    $currentSubscription = getCompanyActualSubscription($societyId);
    if (!$currentSubscription) {
        throw new Exception("Impossible de récupérer l'abonnement actuel.");
    }

    // Déterminer le type d'opération (mise à jour du nombre d'employés ou changement de plan)
    $isUpgradingPlan = !empty($targetPlan);
    
    // Définir les prix et limites selon les plans
    $planDetails = [
        'starter' => ['price' => 180, 'maxEmployees' => 30],
        'basic' => ['price' => 150, 'maxEmployees' => 250],
        'premium' => ['price' => 100, 'maxEmployees' => 1000] // Pratiquement illimité
    ];

    // Vérifier si le plan demandé existe
    if ($isUpgradingPlan && !isset($planDetails[$targetPlan])) {
        throw new Exception("Plan non valide.");
    }

    // Créer une transaction pour garantir la cohérence des données
    $db->beginTransaction();

    if ($isUpgradingPlan) {
        // Cas de mise à niveau du plan
        $employeeCount = $upgradeEmployeeCount;
        $planPrice = $planDetails[$targetPlan]['price'];
        
        // Vérifier que le nombre d'employés ne dépasse pas la limite du plan
        if ($employeeCount > $planDetails[$targetPlan]['maxEmployees']) {
            throw new Exception("Le nombre d'employés dépasse la limite du plan " . ucfirst($targetPlan));
        }

        // Mettre à jour le plan et la capacité d'employés dans la table société
        $updateSql = "UPDATE societe SET plan = :plan, employee_count = :employee_count WHERE societe_id = :societe_id";
        $stmt = $db->prepare($updateSql);
        $stmt->execute([
            'plan' => $targetPlan,
            'employee_count' => $employeeCount, 
            'societe_id' => $societyId
        ]);
        
        // Calculer le montant total du nouvel abonnement
        $totalAmount = $planPrice * $employeeCount;
        
    } else {
        // Cas de mise à jour du nombre d'employés uniquement
        $employeeCount = $newEmployeeCount;
        $planName = $currentPlan;
        $planPrice = $planDetails[$currentPlan]['price'];
        
        // Vérifier que le nombre d'employés ne dépasse pas la limite du plan actuel
        if ($employeeCount > $planDetails[$currentPlan]['maxEmployees']) {
            throw new Exception("Le nombre d'employés dépasse la limite du plan actuel.");
        }
        
        // Mettre à jour la capacité d'employés dans la table société
        $updateSql = "UPDATE societe SET employee_count = :employee_count WHERE societe_id = :societe_id";
        $stmt = $db->prepare($updateSql);
        $stmt->execute([
            'employee_count' => $employeeCount,
            'societe_id' => $societyId
        ]);
        
        // Calculer le montant total du nouvel abonnement
        $totalAmount = $planPrice * $employeeCount;
    }

    // Récupérer l'ID de l'abonnement existant
    $subscriptionFraisId = $currentSubscription['frais_id'];
    
    // Déterminer les dates
    $dateDebut = date('Y-m-d'); // Aujourd'hui
    $dateFin = $currentSubscription['date_fin']; // Conserver la date de fin existante
    
    // Créer un nouveau devis pour le changement d'abonnement
    $sqlDevis = "INSERT INTO devis (date_debut, date_fin, statut, montant, montant_ht, montant_tva, is_contract, id_societe) 
                VALUES (:date_debut, :date_fin, 'accepté', :montant, :montant_ht, :montant_tva, 1, :societe_id)";
    
    $tva = 0.20; // TVA à 20%
    $montantHT = $totalAmount / (1 + $tva);
    $montantTVA = $totalAmount - $montantHT;
    
    $stmtDevis = $db->prepare($sqlDevis);
    $stmtDevis->execute([
        'date_debut' => $dateDebut,
        'date_fin' => $dateFin,
        'montant' => $totalAmount,
        'montant_ht' => $montantHT,
        'montant_tva' => $montantTVA,
        'societe_id' => $societyId
    ]);
    
    $nouveauDevisId = $db->lastInsertId();
    
    // Créer le lien entre le frais d'abonnement existant et le nouveau devis
    $sqlLien = "INSERT INTO INCLUT_FRAIS_DEVIS (id_devis, id_frais) VALUES (:id_devis, :id_frais)";
    $stmtLien = $db->prepare($sqlLien);
    $stmtLien->execute([
        'id_devis' => $nouveauDevisId,
        'id_frais' => $subscriptionFraisId
    ]);
    
    // Valider la transaction
    $db->commit();
    
    // Stocker le plan actuel dans la session
    $_SESSION['plan'] = $isUpgradingPlan ? $targetPlan : $currentPlan;
    
    // Déterminer si nous devons retourner du JSON ou rediriger
    $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjaxRequest) {
        // Retourner une réponse JSON pour les requêtes AJAX
        echo json_encode([
            "success" => true, 
            "message" => "Abonnement mis à jour avec succès",
            "devis_id" => $nouveauDevisId,
            "updated_plan" => $isUpgradingPlan ? $targetPlan : $currentPlan,
            "employee_count" => $employeeCount
        ]);
    } else {
        // Pour les soumissions de formulaires standard, rediriger avec un message
        $_SESSION['subscription_success'] = "Votre abonnement a été mis à jour avec succès.";
        header('Location: /frontOffice/societe/employees/employees.php');
        exit();
    }
    
} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    // Enregistrer l'erreur dans les logs
    error_log("Erreur lors de la mise à jour de l'abonnement: " . $e->getMessage());
    
    if (isset($isAjaxRequest) && $isAjaxRequest) {
        // Réponse JSON pour AJAX
        echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour de l'abonnement: " . $e->getMessage()]);
    } else {
        // Redirection avec message d'erreur pour les formulaires standard
        $_SESSION['subscription_error'] = "Erreur lors de la mise à jour de l'abonnement: " . $e->getMessage();
        header('Location: /frontOffice/societe/employees/abonnements.php');
        exit();
    }
}
?>
