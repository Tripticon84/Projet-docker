<?php
$title = "Mes Activités";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';

// Vérifier si une session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['prestataire_id'])) {
    header("Location: login.php?message=Veuillez vous connecter.");
    exit();
}

$providerId = $_SESSION['prestataire_id'];
$activities = getAllActivities(null, null, $providerId);

$pastActivities = [];
$upcomingActivities = [];
$currentDate = date('Y-m-d');


foreach ($activities as $activity) {
    // Ne pas afficher les activités refusées
    if (isset($activity['refusee']) && $activity['refusee'] == 1) {
        continue;
    }
    
    if ($activity['date'] < $currentDate) {
        $pastActivities[] = $activity;
    } else {
        $upcomingActivities[] = $activity;
    }
}

// Traitement de la demande de refus d'activité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refuse_activity'])) {
    // Récupération de l'ID de l'activité à refuser
    $activityId = $_POST['activity_id'];
    
    // Appel de la fonction pour refuser l'activité
    $result = refuseActivity($activityId);
    
    // Redirection avec message de succès ou d'erreur
    if ($result !== null) {
        header("Location: activites.php?message=L'activité a été refusée avec succès");
        exit();
    } else {
        $error = "Erreur lors du refus de l'activité";
    }
}
?>

<div class="container mt-4">
    <?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Mes Activités</h1>
                    <p class="card-text">Consultez vos activités passées et à venir.</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4>Activités à venir</h4>
        </div>
        <div class="card-body">
            <?php if (empty($upcomingActivities)): ?>
                <div class="alert alert-info">Aucune activité à venir.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($upcomingActivities as $activity): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($activity['nom']) ?></h5>
                                    <p class="card-text">
                                        <strong>Date :</strong> <?= date('d/m/Y', strtotime($activity['date'])) ?><br>
                                        <strong>Type :</strong> <?= htmlspecialchars($activity['type']) ?><br>
                                        <strong>Lieu :</strong> <?php 
                                            if (!empty($activity['adresse']) || !empty($activity['ville'])) {
                                                echo htmlspecialchars($activity['adresse'] ?? '');
                                                echo !empty($activity['adresse']) && !empty($activity['ville']) ? ', ' : '';
                                                echo htmlspecialchars($activity['ville'] ?? '');
                                                echo !empty($activity['code_postal']) ? ' ' . htmlspecialchars($activity['code_postal']) : '';
                                            } else {
                                                echo "Lieu non spécifié";
                                            }
                                        ?>
                                    </p>
                                    <div class="d-flex justify-content-end">
                                        <!-- Bouton pour refuser l'activité -->
                                        <button type="button" class="btn btn-danger btn-sm refuse-activity-btn" 
                                                data-id="<?= $activity['activite_id'] ?>"
                                                data-nom="<?= htmlspecialchars($activity['nom']) ?>">
                                            <i class="fas fa-times-circle"></i> Refuser cette activité
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

   
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h4>Activités passées</h4>
        </div>
        <div class="card-body">
            <?php if (empty($pastActivities)): ?>
                <div class="alert alert-info">Aucune activité passée.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($pastActivities as $activity): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($activity['nom']) ?></h5>
                                    <p class="card-text">
                                        <strong>Date :</strong> <?= date('d/m/Y', strtotime($activity['date'])) ?><br>
                                        <strong>Type :</strong> <?= htmlspecialchars($activity['type']) ?><br>
                                        <strong>Lieu :</strong> <?php 
                                            if (!empty($activity['adresse']) || !empty($activity['ville'])) {
                                                echo htmlspecialchars($activity['adresse'] ?? '');
                                                echo !empty($activity['adresse']) && !empty($activity['ville']) ? ', ' : '';
                                                echo htmlspecialchars($activity['ville'] ?? '');
                                                echo !empty($activity['code_postal']) ? ' ' . htmlspecialchars($activity['code_postal']) : '';
                                            } else {
                                                echo "Lieu non spécifié";
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pour confirmer le refus d'une activité -->
<div class="modal fade" id="refuseActivityModal" tabindex="-1" aria-labelledby="refuseActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="refuseActivityModalLabel">Confirmer le refus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir refuser l'activité <strong id="refuse_activity_name"></strong> ?</p>
                <p class="text-danger">Cette action est irréversible.</p>
                
                <form id="refuseActivityForm" method="POST" action="activites.php">
                    <input type="hidden" name="refuse_activity" value="1">
                    <input type="hidden" name="activity_id" id="refuse_activity_id">
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons de refus d'activité
    const refuseButtons = document.querySelectorAll('.refuse-activity-btn');
    refuseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const activityId = this.getAttribute('data-id');
            const nom = this.getAttribute('data-nom');
            
            // Remplir le modal de confirmation
            document.getElementById('refuse_activity_id').value = activityId;
            document.getElementById('refuse_activity_name').textContent = nom;
            
            // Afficher le modal de confirmation
            const modal = new bootstrap.Modal(document.getElementById('refuseActivityModal'));
            modal.show();
        });
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
