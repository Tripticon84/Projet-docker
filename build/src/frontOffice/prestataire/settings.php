<?php
$title = "Paramètres - Espace Prestataire";
include_once __DIR__ . '/includes/head.php';
include_once __DIR__ . '/includes/header.php';

// Vérification de la session
if (!isset($_SESSION['prestataire_id'])) {
    header('Location: /login.php');
    exit;
}

// Récupération des informations du prestataire
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
$prestataire = getProviderById($_SESSION['prestataire_id']);

if (!$prestataire) {
    header('Location: /login.php');
    exit;
}
?>

<div class="container mt-5">
    <h2>Paramètres du compte</h2>
    
    <div id="notificationContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
    
    <!-- Section Profil -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Modifier le profil</h5>
                    <form id="profileForm">
                        <input type="hidden" id="prestataire_id" name="prestataire_id" value="<?php echo $prestataire['prestataire_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" value="<?php echo $prestataire['nom']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" value="<?php echo $prestataire['prenom']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo $prestataire['email']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tarif" class="form-label">Tarif</label>
                            <input type="number" class="form-control" id="tarif" value="<?php echo $prestataire['tarif']; ?>" disabled>
                        </div>
                        
                        <div id="editButtons" style="display: none;">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">Annuler</button>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary" onclick="toggleEditMode()">Modifier</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Section Mot de passe -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Modifier le mot de passe</h5>
                    <form id="passwordForm">
                        <input type="hidden" name="prestataire_id" value="<?php echo $prestataire['prestataire_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="currentPassword" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="newPassword" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/data/static/js/prestataire.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form submission
    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = {
                prestataire_id: document.getElementById('prestataire_id').value,
                name: document.getElementById('nom').value,
                firstname: document.getElementById('prenom').value,
                email: document.getElementById('email').value,
                tarif: document.getElementById('tarif').value
            };

            const response = await callApi('/api/provider/updateProfile.php', 'POST', formData);
            showNotification('Profil mis à jour avec succès', 'success');
            toggleEditMode();
        } catch (error) {
            showNotification('Erreur: ' + error.message, 'error');
        }
    });

    // Password form submission
    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            showNotification('Les nouveaux mots de passe ne correspondent pas', 'error');
            return;
        }
        
        try {
            const response = await callApi('/api/provider/updatePassword.php', 'POST', {
                prestataire_id: document.querySelector('input[name="prestataire_id"]').value,
                currentPassword: currentPassword,
                newPassword: newPassword
            });
            
            showNotification('Mot de passe modifié avec succès', 'success');
            document.getElementById('passwordForm').reset();
        } catch (error) {
            showNotification('Erreur: ' + error.message, 'error');
        }
    });
});

function toggleEditMode() {
    const inputs = document.querySelectorAll('#profileForm input:not([type="hidden"])');
    const editButtons = document.getElementById('editButtons');
    const isDisabled = inputs[0].disabled;

    inputs.forEach(input => {
        input.disabled = !isDisabled;
    });

    editButtons.style.display = isDisabled ? 'block' : 'none';
}
</script>
</body>
</html>
