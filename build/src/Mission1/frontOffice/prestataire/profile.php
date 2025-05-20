<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
// Session déjà démarrée dans header.php 

// Inclure l'en-tête
require_once 'includes/head.php';

// Vérif si le prestataire est connecté 
if (!isset($_SESSION['prestataire_id'])) {
    header('Location: /login.php'); // Redirection si pas connecté
    exit; // Super important! Toujours exit après une redirection
}

$prestaireId = $_SESSION['prestataire_id']; // Récupère l'ID depuis la session
$profile = getProviderById($prestaireId); 

if (!$profile) {
    header('Location: /error.php'); // Redirection si profil non trouvé 
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - BusinessCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 60px;
            border: 4px solid white;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/header.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="profile-img">
                        <i class="fas fa-user-tie fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col">
                    <h1 class="mb-0"><?php echo htmlspecialchars($profile['prenom'] . ' ' . $profile['nom']); ?></h1>
                    <p class="mb-0"><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($profile['type']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Informations personnelles</h4>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
                <form id="profileForm" onsubmit="updateProfile(event)">
                    <input type="hidden" id="prestataire_id" value="<?php echo $profile['prestataire_id']; ?>">
                    
                    <div class="row mb-3">
                        <!--  champs du formulaire profil -->
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" value="<?php echo htmlspecialchars($profile['nom']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" value="<?php echo htmlspecialchars($profile['prenom']); ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($profile['email']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type de service</label>
                            <input type="text" class="form-control" id="type" value="<?php echo htmlspecialchars($profile['type']); ?>" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" disabled><?php echo htmlspecialchars($profile['description']); ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tarif" class="form-label">Tarif horaire (€)</label>
                            <input type="number" class="form-control" id="tarif" value="<?php echo htmlspecialchars($profile['tarif']); ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_debut" class="form-label">Début de disponibilité</label>
                            <input type="date" class="form-control" id="date_debut" value="<?php echo htmlspecialchars($profile['date_debut_disponibilite']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="date_fin" class="form-label">Fin de disponibilité</label>
                            <input type="date" class="form-control" id="date_fin" value="<?php echo htmlspecialchars($profile['date_fin_disponibilite']); ?>" disabled>
                        </div>
                    </div>

                    
                    <div id="editButtons" class="mt-4" style="display: none;">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4">Mes activités</h4>
                <div id="activities-container">
                    <div class="text-center my-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
    
    <script src="/data/static/js/provider.js"></script>
    <script>
        // Active/désactive le mode édition des champs du formulaire
        function toggleEditMode() {
            // Récup tous les champs du form sauf l'ID 
            const fields = ['nom', 'prenom', 'email', 'type', 'description', 'tarif', 'date_debut', 'date_fin'];
            const editButtons = document.getElementById('editButtons');
            
            // Boucle sur tous les champs pour changer leur état
            fields.forEach(field => {
                const element = document.getElementById(field);
                if (element) {
                    // Si le champ est disabled, on l'active et vice versa
                    element.disabled = !element.disabled;
                }
            });
            
            // Affiche/masque les boutons d'action
            editButtons.style.display = editButtons.style.display === 'none' ? 'block' : 'none';
        }
        
        // Fonction pour envoyer les données du profil au serveur
        async function updateProfile(event) {
            event.preventDefault(); // Empêche le refresh de la page
            
            // Récupère les valeurs du form
            const prestataireId = document.getElementById('prestataire_id').value;
            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const email = document.getElementById('email').value;
            const type = document.getElementById('type').value;
            const description = document.getElementById('description').value;
            const tarif = document.getElementById('tarif').value;
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            
            try {
                // Prépare les données à envoyer en JSON
                const data = {
                    prestataire_id: prestataireId,
                    nom: nom,
                    prenom: prenom,
                    email: email,
                    type: type,
                    description: description,
                    tarif: tarif,
                    date_debut_disponibilite: dateDebut,
                    date_fin_disponibilite: dateFin
                };
                
                // Appel AJAX via fetch API (meilleur que XMLHttpRequest)
                const response = await fetch('/api/provider/update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    // Si tout va bien, on désactive l'édition et affiche un message
                    alert('Profil mis à jour avec succès!');
                    toggleEditMode();
                } else {
                    // Sinon on affiche une erreur
                    const error = await response.json();
                    alert('Erreur: ' + (error.message || 'Une erreur est survenue'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion au serveur');
            }
        }
        
        // Load provider activities
        document.addEventListener('DOMContentLoaded', function() {
            const prestataire_id = document.getElementById('prestataire_id').value;
            
            fetch(`/api/provider/getActivite.php?id=${prestataire_id}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('activities-container');
                container.innerHTML = '';
                
                if (data.length === 0) {
                    container.innerHTML = '<p class="text-center">Aucune activité programmée.</p>';
                    return;
                }
                
                const table = document.createElement('table');
                table.className = 'table table-striped';
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activities-table-body"></tbody>
                `;
                container.appendChild(table);
                
                const tbody = document.getElementById('activities-table-body');
                data.forEach(activity => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${activity.name}</td>
                        <td>${activity.type}</td>
                        <td>${new Date(activity.date).toLocaleDateString('fr-FR')}</td>
                        <td>${activity.place}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editActivity(${activity.activite_id})">
                                <i class="fas fa-calendar-alt"></i> Changer la date
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('activities-container').innerHTML = 
                    '<p class="text-center text-danger">Erreur lors du chargement des activités.</p>';
            });
        });
        
        // Function to edit activity date
        function editActivity(activityId) {
            // Fetch current activity details
            fetch(`/api/provider/getActivite.php?id=${activityId}`)
            .then(response => response.json())
            .then(activities => {
                // Since getActivite.php returns an array, we need to get the first item
                const activity = Array.isArray(activities) && activities.length > 0 ? activities[0] : null;
                
                if (!activity) {
                    alert('Activité non trouvée');
                    return;
                }
                
                // Create modal for date editing
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'editActivityModal';
                modal.setAttribute('tabindex', '-1');
                modal.setAttribute('aria-labelledby', 'editActivityModalLabel');
                modal.setAttribute('aria-hidden', 'true');
                
                const currentDate = new Date(activity.date).toISOString().split('T')[0];
                
                modal.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editActivityModalLabel">Modifier la date: ${activity.name}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editActivityForm">
                                    <div class="mb-3">
                                        <label for="activityDate" class="form-label">Nouvelle date</label>
                                        <input type="date" class="form-control" id="activityDate" value="${currentDate}" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-primary" onclick="saveActivityDate(${activityId})">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Initialize and show the modal
                const bsModal = new bootstrap.Modal(document.getElementById('editActivityModal'));
                bsModal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la récupération des détails de l\'activité');
            });
        }
        
        // Function to save new activity date
        function saveActivityDate(activityId) {
            const newDate = document.getElementById('activityDate').value;
            
            fetch('/api/provider/updateActivity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activite_id: activityId,
                    date: newDate
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Erreur: ' + data.error);
                } else {
                    alert('Date mise à jour avec succès');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editActivityModal'));
                    modal.hide();
                    document.getElementById('editActivityModal').remove();
                    
                    // Reload activities
                    const prestataire_id = document.getElementById('prestataire_id').value;
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur s\'est produite lors de la mise à jour de la date');
            });
        }
    </script>
</body>
</html>
