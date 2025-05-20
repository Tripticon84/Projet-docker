<?php
$title = "Mes Disponibilités";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';

// Vérifier si une session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que le prestataire est bien connecté, sinon on le redirige
if (!isset($_SESSION['prestataire_id'])) {
    header("Location: login.php?message=Veuillez vous connecter.");
    exit();
}

// On récupère les infos du prestataire connecté
$prestataire_id = $_SESSION['prestataire_id'];
$prestataire = getProviderById($prestataire_id);

// Super important de vérifier qu'on a bien récupéré les données!
if (!$prestataire) {
    echo "<div class='alert alert-danger'>Erreur lors de la récupération des informations du prestataire.</div>";
    exit;
}
?>

<div class="container mt-4">
    <?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Gérer mes disponibilités</h1>
                    <p class="card-text">Indiquez vos périodes de disponibilité pour les missions.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message explicatif pour l'utilisateur -->
    <div class="alert alert-info">
        <p><strong>Comment ça marche :</strong> Vos dates de disponibilité permettent aux entreprises de savoir quand vous êtes disponible pour des missions.</p>
        <p>Assurez-vous que votre période de disponibilité est à jour pour ne pas manquer d'opportunités!</p>
    </div>
    
    <!-- Le formulaire pour modifier les dates -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4>Mes dates de disponibilité</h4>
        </div>
        <div class="card-body">
            <form id="disponibilitesForm">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <!-- Conversion en format français pour l'affichage -->
                        <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?php echo date('Y-m-d', strtotime($prestataire['date_debut_disponibilite'])); ?>" required>
                        <div class="form-text">Premier jour où vous êtes disponible.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?php echo date('Y-m-d', strtotime($prestataire['date_fin_disponibilite'])); ?>" required>
                        <div class="form-text">Dernier jour où vous êtes disponible.</div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save me-1"></i> Enregistrer mes disponibilités
                    </button>
                </div>
            </form>
            
            <!-- Conteneur pour les notifications -->
            <div id="notificationContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
        </div>
    </div>
</div>

<script>
// On attend que le DOM soit chargé pour ajouter nos écouteurs d'événements
document.addEventListener('DOMContentLoaded', function() {
    // Je récupère mon formulaire et j'ajoute un écouteur sur l'événement submit
    const disponibilitesForm = document.getElementById('disponibilitesForm');
    
    disponibilitesForm.addEventListener('submit', function(e) {
        // Super important d'empêcher le comportement par défaut du formulaire!!!
        e.preventDefault();
        
        // Je récupère les valeurs des dates
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;
        
        // Hop! On vérifie que la date de fin est bien après la date de début
        if (new Date(dateFin) <= new Date(dateDebut)) {
            showNotification('La date de fin doit être après la date de début!', 'error');
            return;
        }
        
        // On prépare les données à envoyer à l'API
        const data = {
            date_debut_disponibilite: dateDebut,
            date_fin_disponibilite: dateFin
        };
        
        // Et maintenant on fait un appel à notre API pour mettre à jour les dates!
        updateDisponibilites(data);
    });
});

// Fonction pour mettre à jour les disponibilités via l'API
async function updateDisponibilites(data) {
    try {
        // On utilise la fonction callApi définie dans prestataire.js - Notez que le chemin de l'API a changé!
        const response = await callApi('/api/provider/update_disponibilites.php', 'POST', data);
        
        if (response.success) {
            showNotification('Vos disponibilités ont été mises à jour avec succès!', 'success');
        } else {
            showNotification('Erreur lors de la mise à jour des disponibilités: ' + response.error, 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Une erreur s\'est produite lors de la communication avec le serveur.', 'error');
    }
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
