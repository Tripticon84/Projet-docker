<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['societe_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Récupérer l'ID du devis créé
$estimate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($estimate_id <= 0) {
    header('Location: list_estimates.php?error=invalid_id');
    exit;
}
?>

<style>
    /* Styles personnalisés pour la page de confirmation */
    .confirmation-container {
        animation: fadeIn 0.6s ease-in-out;
        padding-bottom: 3rem;
    }
    
    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    .confirmation-alert {
        border-left: 5px solid #198754;
        border-radius: 0.5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        margin-top: 1.5rem;
        background-color: #f8fff9;
        position: relative;
        overflow: hidden;
    }
    
    .confirmation-alert::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(25, 135, 84, 0.1) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        z-index: 1;
    }
    
    .reference-number {
        font-size: 1.2rem;
        font-weight: 600;
        color: #0d6efd;
        padding: 0.3rem 0.7rem;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 0.25rem;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-top: 0.5rem;
        border: 1px dashed rgba(13, 110, 253, 0.3);
    }
    
    .summary-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
    }
    
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.18);
    }
    
    .card-header-primary {
        background: linear-gradient(135deg, #0d6efd, #0a47aa);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 0.75rem 0.75rem 0 0;
        font-weight: 500;
        border-bottom: 3px solid rgba(255,255,255,0.2);
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #17a2b8, #0f7a8a);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 0.75rem 0.75rem 0 0;
        font-weight: 500;
        border-bottom: 3px solid rgba(255,255,255,0.2);
    }
    
    .info-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed rgba(0,0,0,0.07);
    }
    
    .info-line:last-of-type {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
    }
    
    .price-highlight {
        font-size: 1.75rem;
        font-weight: 700;
        color: #198754;
        text-shadow: 1px 1px 1px rgba(0,0,0,0.05);
        display: block;
        animation: pulsate 2s ease-in-out infinite;
    }
    
    @keyframes pulsate {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }
    
    .card-body {
        padding: 1.75rem;
        background: #fff;
    }
    
    .action-buttons {
        margin-top: 2rem;
    }
    
    .action-btn {
        padding: 0.85rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
        border-radius: 0.5rem;
        letter-spacing: 0.3px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.12);
    }
    
    .action-btn:active {
        transform: translateY(0);
    }
    
    .step-item {
        border-left: 2px solid #dee2e6;
        padding-left: 1.75rem;
        position: relative;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }
    
    .step-item:hover {
        transform: translateX(5px);
    }
    
    .step-item:last-child {
        margin-bottom: 0;
        border-left: 2px solid rgba(25, 135, 84, 0.5);
    }
    
    .step-item:before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid;
        left: -9px;
        top: 5px;
        transition: all 0.3s ease;
    }
    
    .step-item:hover:before {
        transform: scale(1.2);
    }
    
    .step-item.step-warning:before {
        border-color: #ffc107;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }
    
    .step-item.step-primary:before {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
    }
    
    .step-item.step-success:before {
        border-color: #198754;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 0.4rem;
        font-size: 1.05rem;
    }
    
    hr {
        margin: 1.5rem 0;
        opacity: 0.1;
        border-top: 1px solid rgba(0,0,0,0.2);
    }
    
    .pdf-btn {
        margin-top: 1.5rem;
        border-radius: 0.5rem;
        padding: 0.75rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(13, 110, 253, 0.5);
    }
    
    .pdf-btn:hover {
        background-color: rgba(13, 110, 253, 0.1);
        border-color: #0d6efd;
    }
    
    /* Badge styles */
    .badge {
        padding: 0.5em 0.85em;
        font-size: 0.85em;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Alert in card styles */
    .alert-success.border {
        background-color: #f8fff9;
        border-color: #d1e7dd !important;
        color: #116a43;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    
    /* Responsive styles */
    @media (max-width: 767.98px) {
        .info-line {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .info-label {
            margin-bottom: 0.5rem;
        }
        
        .price-highlight {
            font-size: 1.5rem;
        }
        
        .action-buttons .col-lg-6:first-child {
            margin-bottom: 1rem;
        }
    }
    
    /* Status icon pulse effect */
    .status-pulse {
        animation: statusPulse 2s infinite;
        display: inline-block;
    }
    
    @keyframes statusPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* Email notification style */
    .email-notification {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        margin-top: 1rem;
    }
    
    .email-icon {
        margin-right: 0.75rem;
        color: #6c757d;
        font-size: 1.25rem;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include_once('../../includes/sidebar.php'); ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 confirmation-container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-check-circle text-success me-2"></i> Confirmation du Devis</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="estimates.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>

            <div class="alert alert-success confirmation-alert p-4 mb-4" role="alert">
                <h4 class="alert-heading mb-2"><i class="fas fa-check-circle me-2"></i> Devis créé avec succès!</h4>
                <p class="mb-1">Votre devis a été créé et envoyé avec succès.</p>
                <p class="mb-0">Numéro de référence: <span class="reference-number">#<?php echo $estimate_id; ?></span></p>
            </div>
            
            <div class="row mb-4 g-4">
                <div class="col-lg-6">
                    <div class="card summary-card">
                        <div class="card-header card-header-primary">
                            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Récapitulatif du Devis</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-line">
                                <span class="info-label"><i class="fas fa-hashtag me-1"></i> Référence:</span>
                                <span class="badge bg-light text-dark border">#<?php echo $estimate_id; ?></span>
                            </div>
                            
                            <div class="info-line">
                                <span class="info-label"><i class="far fa-calendar me-1"></i> Date de création:</span>
                                <span><?php echo date('d/m/Y'); ?></span>
                            </div>
                            
                            <div class="info-line">
                                <span class="info-label"><i class="fas fa-circle-info me-1"></i> Statut:</span>
                                <span class="badge bg-info px-3 py-2 status-pulse">
                                    <i class="fas fa-paper-plane me-1"></i> Envoyé
                                </span>
                            </div>
                            
                            <hr>
                            
                            <div class="info-line">
                                <span class="info-label"><i class="far fa-calendar-alt me-2"></i> Période:</span>
                                <span>
                                    <strong>Du</strong> <?php echo date('d/m/Y', strtotime($_GET['start_date'] ?? date('Y-m-d'))); ?> 
                                    <br class="d-md-none">
                                    <strong>au</strong> <?php echo date('d/m/Y', strtotime($_GET['end_date'] ?? '')); ?>
                                </span>
                            </div>
                            
                            <div class="info-line">
                                <span class="info-label"><i class="fas fa-user-plus me-2"></i> Employés supplémentaires:</span>
                                <span class="badge bg-primary px-3 py-2">
                                    <?php echo isset($_GET['additional_employees']) ? $_GET['additional_employees'] : '0'; ?>
                                </span>
                            </div>
                            
                            <hr>
                            
                            <div class="alert alert-success border">
                                <div class="d-flex flex-column">
                                    <strong class="mb-2">Montant à payer (au prorata):</strong> 
                                    <span class="price-highlight text-center">
                                        <?php echo isset($_GET['prorated_cost']) ? number_format(floatval($_GET['prorated_cost']), 2, ',', ' ') : '0,00'; ?> €
                                    </span>
                                    <small class="d-block mt-2 text-center fst-italic">Pour les employés supplémentaires</small>
                                </div>
                            </div>
                            
                            <div class="email-notification mt-4">
                                <i class="fas fa-envelope email-icon"></i>
                                <small>Un email de confirmation a été envoyé à l'adresse associée à votre compte.</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card summary-card">
                        <div class="card-header card-header-info">
                            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Prochaines étapes</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-4 fw-medium">Votre demande d'ajout d'employés sera traitée prochainement par notre équipe.</p>
                            
                            <div class="step-item step-warning">
                                <div class="step-title"><i class="fas fa-clock text-warning me-2"></i> En attente de validation</div>
                                <p class="text-muted mb-0">Notre équipe examinera votre demande sous 24-48h</p>
                            </div>
                            
                            <div class="step-item step-primary">
                                <div class="step-title"><i class="fas fa-file-invoice-dollar text-primary me-2"></i> Traitement du paiement</div>
                                <p class="text-muted mb-0">Un lien de paiement vous sera envoyé après validation</p>
                            </div>
                            
                            <div class="step-item step-success">
                                <div class="step-title"><i class="fas fa-check-circle text-success me-2"></i> Mise à jour de votre abonnement</div>
                                <p class="text-muted mb-0">Votre nombre d'employés sera mis à jour après paiement</p>
                            </div>
                            
                            <?php if (isset($_GET['pdf_path'])): ?>
                            <a href="<?php echo $_GET['pdf_path']; ?>" class="btn btn-outline-primary w-100 pdf-btn" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Télécharger le PDF du devis
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row action-buttons g-4">
                <div class="col-lg-6">
                    <a href="../home.php" class="btn btn-primary w-100 action-btn">
                        <i class="fas fa-home me-2"></i> Retour au Tableau de Bord
                    </a>
                </div>
                <div class="col-lg-6">
                    <a href="estimates.php" class="btn btn-outline-secondary w-100 action-btn">
                        <i class="fas fa-list me-2"></i> Voir tous mes devis
                    </a>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>