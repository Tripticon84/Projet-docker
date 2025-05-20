<?php

require_once 'includes/head.php';
include_once 'includes/header.php';
?>

<div class="container mt-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom']); ?> !</h1>
                    <p class="card-text">Votre espace prestataire pour gérer vos services Business Care.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-md-8">
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar-check"></i> Mes activités planifiées</h5>
                </div>
                <div class="card-body">
                    <?php
                    require_once '../../api/dao/provider.php';
                    
                    $activities = isset($_SESSION['prestataire_id']) ? 
                        getAllActivities(5, 0, $_SESSION['prestataire_id']) : [];
                    
                    if ($activities && count($activities) > 0) {
                        $today = date('Y-m-d');
                        $upcomingActivities = array_filter($activities, function($activity) use ($today) {
                            return $activity['date'] >= $today;
                        });
                        
                        // Prendre les 3 premières activités à venir
                        $upcomingActivities = array_slice($upcomingActivities, 0, 3);
                        
                        if (count($upcomingActivities) > 0) {
                            foreach ($upcomingActivities as $activity) {
                                ?>
                                <div class="mb-2 p-2 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?php echo htmlspecialchars($activity['nom']); ?></strong>
                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($activity['date'])); ?></small>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($activity['type']); ?>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-muted">Aucune activité planifiée pour le moment.</p>';
                        }
                    } else {
                        echo '<p class="text-muted">Aucune activité disponible.</p>';
                    }
                    ?>
                    <div class="mt-3">
                        <a href="activites.php" class="btn btn-outline-primary">Voir toutes mes activités</a>
                    </div>
                </div>
            </div>

           
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-file-invoice"></i> Mes dernières factures</h5>
                </div>
                <div class="card-body">
                    <div id="recent-invoices">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                require_once '../../api/dao/provider.php';
                                
                                if (isset($_SESSION['prestataire_id'])) {
                                    $invoices = getProviderInvoices($_SESSION['prestataire_id']);
                                    
                                    // Limiter aux 3 dernières factures pour l'affichage dans le dashboard
                                    $recentInvoices = array_slice($invoices, 0, 3);
                                    
                                    if (count($recentInvoices) > 0) {
                                        foreach ($recentInvoices as $invoice) {
                                            // Déterminer la classe de badge selon le statut
                                            $statusClass = '';
                                            switch ($invoice['statut']) {
                                                case 'Payee':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'Attente':
                                                    $statusClass = 'bg-warning text-dark';
                                                    break;
                                                case 'Annulee':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                            }
                                            
                                            // Formater la date
                                            $dateEmission = date('d/m/Y', strtotime($invoice['date_emission']));
                                            
                                            // Formater le montant
                                            $montant = number_format($invoice['montant'], 2, ',', ' ') . ' €';
                                            
                                            echo "<tr>";
                                            echo "<td>F-" . $invoice['facture_id'] . "</td>";
                                            echo "<td>{$dateEmission}</td>";
                                            echo "<td>{$montant}</td>";
                                            echo "<td><span class='badge {$statusClass}'>" . ucfirst($invoice['statut']) . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>Aucune facture disponible.</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>Session invalide. Veuillez vous reconnecter.</td></tr>";
                                }
                            ?>
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <a href="factures.php" class="btn btn-outline-primary">Voir toutes mes factures</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="col-md-4">
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> Accès rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="activites.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Mes Activités
                        </a>
                        <a href="evaluations.php" class="btn btn-warning">
                            <i class="fas fa-star"></i> Mes Évaluations
                        </a>
                        <a href="factures.php" class="btn btn-info text-white">
                            <i class="fas fa-file-invoice"></i> Mes Factures
                        </a>
                    </div>
                </div>
            </div>

            
            <!-- Carte des évaluations -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-star"></i> Dernières évaluations reçues</h5>
                </div>
                <div class="card-body">
                    <?php
                    require_once '../../api/dao/evaluation.php';
                    
                    if (isset($_SESSION['prestataire_id'])) {
                        $evaluations = getEvaluationsByProviderId($_SESSION['prestataire_id']);
                        
                        // Limiter aux 3 dernières évaluations
                        $recentEvaluations = array_slice($evaluations, 0, 3);
                        
                        if (count($recentEvaluations) > 0) {
                            foreach ($recentEvaluations as $evaluation) {
                                // Formater la date
                                $dateCreation = date('d/m/Y', strtotime($evaluation['date_creation']));
                                
                                // Générer les étoiles en fonction de la note
                                $stars = '';
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $evaluation['note']) {
                                        $stars .= '<i class="fas fa-star"></i>';
                                    } else {
                                        $stars .= '<i class="far fa-star"></i>';
                                    }
                                }
                                
                                // Afficher l'évaluation
                                echo '<div class="mb-3 pb-2 border-bottom">';
                                echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                echo '<div>';
                                echo '<span class="text-warning">' . $stars . '</span>';
                                echo '<span class="ms-2">' . $evaluation['note'] . '.0</span>';
                                echo '</div>';
                                echo '<small class="text-muted">' . $dateCreation . '</small>';
                                echo '</div>';
                                echo '<p class="small mb-1">"' . htmlspecialchars(substr($evaluation['commentaire'], 0, 100)) . (strlen($evaluation['commentaire']) > 100 ? '...' : '') . '"</p>';
                                echo '<small class="text-muted">De: ' . htmlspecialchars($evaluation['collaborateur_prenom'] . ' ' . substr($evaluation['collaborateur_nom'], 0, 1) . '.') . '</small>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-info">Vous n\'avez pas encore reçu d\'évaluations.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">Session invalide. Veuillez vous reconnecter.</div>';
                    }
                    ?>
                    
                    <a href="evaluations.php" class="btn btn-outline-primary btn-sm mt-3">Voir toutes les évaluations</a>
                </div>
            </div>

        </div>
    </div>
</div>
>
<script>
    const prestataireId = <?php echo isset($_SESSION['prestataire_id']) ? $_SESSION['prestataire_id'] : 'null'; ?>;
</script>
<script src="/data/static/js/prestataire.js"></script>

<?php

include 'includes/footer.php';
?>
