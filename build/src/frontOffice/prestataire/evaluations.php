<?php
$title = "Mes Évaluations";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/evaluation.php';

// Vérifier si une session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prestataire_id'])) {
    header("Location: login/login.php?message=Veuillez vous connecter.");
    exit();
}

$prestataireId = $_SESSION['prestataire_id'];
$evaluations = getEvaluationsByProviderId($prestataireId);
$ratingStats = getAverageRatingByProviderId($prestataireId);
$ratingDistribution = getRatingDistributionByProviderId($prestataireId);

// Préparer les données pour le graphique de distribution
$distributionData = [0, 0, 0, 0, 0]; // Par défaut, 0 évaluation pour chaque note (1-5)
foreach($ratingDistribution as $rating) {
    $distributionData[$rating['note']-1] = $rating['count'];
}

// Formatage de la date
function formatDate($date) {
    if (!$date) return "Date inconnue";
    return date('d/m/Y', strtotime($date));
}

// Fonction pour générer les étoiles HTML en fonction de la note
function generateStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-warning"></i> ';
        } else {
            $html .= '<i class="far fa-star text-muted"></i> ';
        }
    }
    return $html;
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Mes Évaluations</h1>
                    <p class="card-text">Consultez les avis et retours sur vos prestations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé des évaluations -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-light h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Note moyenne</h5>
                    <div class="display-4 mb-2">
                        <?= number_format($ratingStats['average_rating'], 1) ?>
                        <small class="text-muted">/5</small>
                    </div>
                    <div class="mb-3">
                        <?= generateStars(round($ratingStats['average_rating'])) ?>
                    </div>
                    <p class="card-text">Basé sur <?= $ratingStats['total_evaluations'] ?> avis</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card bg-light h-100">
                <div class="card-body">
                    <h5 class="card-title">Distribution des notes</h5>
                    <canvas id="ratingDistribution" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des évaluations -->
    <div class="card">
        <div class="card-header bg-white">
            <h4>Avis des collaborateurs</h4>
        </div>
        <div class="card-body">
            <?php if (empty($evaluations)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Vous n'avez pas encore reçu d'évaluations.
                </div>
            <?php else: ?>
                <?php foreach ($evaluations as $evaluation): ?>
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><?= htmlspecialchars($evaluation['collaborateur_prenom'] . ' ' . $evaluation['collaborateur_nom']) ?></h5>
                                    <div class="mb-2">
                                        <?= generateStars($evaluation['note']) ?>
                                    </div>
                                    <p class="card-text"><?= nl2br(htmlspecialchars($evaluation['commentaire'])) ?></p>
                                </div>
                                <div class="text-muted">
                                    <small>Le <?= formatDate($evaluation['date_creation']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Inclure Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Distribution des notes
    const distributionCtx = document.getElementById('ratingDistribution');
    
    if (distributionCtx) {
        const distributionChart = new Chart(distributionCtx, {
            type: 'bar',
            data: {
                labels: ['5 étoiles', '4 étoiles', '3 étoiles', '2 étoiles', '1 étoile'],
                datasets: [{
                    label: 'Nombre d\'avis',
                    data: <?= json_encode(array_reverse($distributionData)) ?>,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(255, 153, 0, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(255, 153, 0, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
