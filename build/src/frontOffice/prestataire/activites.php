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
    if ($activity['date'] < $currentDate) {
        $pastActivities[] = $activity;
    } else {
        $upcomingActivities[] = $activity;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity'])) {
    $activityId = $_POST['activity_id'];
    $date = $_POST['date'];
    
    $result = updateActivity($activityId, $date);
    
    if ($result !== null) {
        
        header("Location: activites.php?message=Date de l'activité mise à jour avec succès");
        exit();
    } else {
        $error = "Erreur lors de la mise à jour de la date de l'activité";
    }
}
?>

<div class="container mt-4">
    <
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
                                    <button type="button" class="btn btn-primary btn-sm edit-activity-btn" 
                                            data-id="<?= $activity['activite_id'] ?>"
                                            data-nom="<?= htmlspecialchars($activity['nom']) ?>"
                                            data-date="<?= $activity['date'] ?>">
                                        <i class="fas fa-calendar-alt"></i> Modifier la date
                                    </button>
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


<div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editActivityModalLabel">Modifier la date de l'activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editActivityForm" method="POST" action="activites.php">
                    <input type="hidden" name="update_activity" value="1">
                    <input type="hidden" name="activity_id" id="activity_id">
                    
                    <div class="mb-3">
                        <label for="activity_name" class="form-label">Activité</label>
                        <input type="text" class="form-control" id="activity_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label">Nouvelle date</label>
                        <input type="date" class="form-control" id="date" name="date" required min="<?= date('Y-m-d') ?>">
                        <small class="text-muted">Veuillez choisir une date à partir d'aujourd'hui.</small>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Enregistrer la nouvelle date</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const editButtons = document.querySelectorAll('.edit-activity-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const activityId = this.getAttribute('data-id');
            const nom = this.getAttribute('data-nom');
            const date = this.getAttribute('data-date');
            
            
            document.getElementById('activity_id').value = activityId;
            document.getElementById('activity_name').value = nom;
            document.getElementById('date').value = date;
            
            
            const modal = new bootstrap.Modal(document.getElementById('editActivityModal'));
            modal.show();
        });
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
