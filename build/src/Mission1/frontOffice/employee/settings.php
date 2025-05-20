<?php
$title = "Paramètres - Espace Salarié";
include_once __DIR__ . '/includes/head.php';
include_once __DIR__ . '/includes/header.php';

// Vérification de la session
if (!isset($_SESSION['collaborateur_id'])) {
    header('Location: /login.php');
    exit;
}

// Récupération des informations du collaborateur
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
$collaborateur = getEmployeeProfile($_SESSION['collaborateur_id']);

if (!$collaborateur) {
    header('Location: /login.php');
    exit;
}
?>

<div class="container mt-5">
    <h2>Paramètres du compte</h2>
    
    <!-- Section Profil -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Modifier le profil</h5>
                    <form id="profileForm">
                        <input type="hidden" id="collaborateur_id" name="collaborateur_id" value="<?php echo $collaborateur['collaborateur_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" value="<?php echo $collaborateur['nom']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" value="<?php echo $collaborateur['prenom']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo $collaborateur['email']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" value="<?php echo $collaborateur['telephone']; ?>" disabled>
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
                        <input type="hidden" name="collaborateur_id" value="<?php echo $collaborateur['collaborateur_id']; ?>">
                        
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
<script src="/data/static/js/employee.js"></script>
</body>
</html>
