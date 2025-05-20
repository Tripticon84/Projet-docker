<?php
require_once 'includes/head.php';
require_once 'includes/header.php';

// Récupérer l'id_societe du collaborateur connecté depuis la session
$id_societe = isset($_SESSION['id_societe']) ? $_SESSION['id_societe'] : null;
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h1 class="card-title">Signalement anonyme</h1>
                    <p class="card-text">Cet espace vous permet de signaler des situations problématiques en toute confidentialité.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Formulaire de signalement</h5>
                </div>
                <div class="card-body">
                    <form id="signalementForm" method="POST">
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de signalement <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Choisir un type</option>
                                <option value="Harcèlement">Harcèlement</option>
                                <option value="Discrimination">Discrimination</option>
                                <option value="Conditions de travail">Conditions de travail</option>
                                <option value="Santé mentale">Santé mentale</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <!-- Champ qui s'affiche uniquement lorsque "Autre" est sélectionné -->
                        <div class="mb-3" id="autreTypeDiv" style="display: none;">
                            <label for="autreType" class="form-label">Précisez le type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="autreType" name="autreType" placeholder="Veuillez préciser le type de signalement...">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description détaillée <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="6" placeholder="Décrivez la situation en détail..." required></textarea>
                            <div class="form-text">Votre description nous aide à comprendre la situation et à prendre les mesures appropriées.</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Tous les signalements sont traités avec la plus grande confidentialité, conformément à notre politique de protection des données.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le signalement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    var autreTypeDiv = document.getElementById('autreTypeDiv');
    if (this.value === 'Autre') {
        autreTypeDiv.style.display = 'block';
        document.getElementById('autreType').required = true;
    } else {
        autreTypeDiv.style.display = 'none';
        document.getElementById('autreType').required = false;
    }
});

document.getElementById('signalementForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const type = document.getElementById('type').value;
    const autreType = document.getElementById('autreType').value;
    const description = document.getElementById('description').value;
    const id_societe = <?php echo $id_societe ? $id_societe : 'null'; ?>;

    try {
        const response = await fetch('/api/employee/createSignalement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: type === 'Autre' ? autreType : type,
                description: description,
                id_societe: id_societe
            })
        });

        const data = await response.json();

        if (data.success) {
            // Réinitialiser le formulaire
            this.reset();
            
            // Afficher un message de succès
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success mt-3';
            alertDiv.role = 'alert';
            alertDiv.innerHTML = 'Votre signalement a été envoyé avec succès. Nous le traiterons dans les plus brefs délais.';
            this.insertAdjacentElement('afterend', alertDiv);

            // Faire disparaître le message après 5 secondes
            setTimeout(() => alertDiv.remove(), 5000);
        } else {
            throw new Error(data.message || 'Une erreur est survenue');
        }
    } catch (error) {
        console.error('Erreur:', error);
        
        // Afficher un message d'erreur
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger mt-3';
        alertDiv.role = 'alert';
        alertDiv.innerHTML = 'Une erreur est survenue lors de l\'envoi du signalement. Veuillez réessayer.';
        this.insertAdjacentElement('afterend', alertDiv);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
