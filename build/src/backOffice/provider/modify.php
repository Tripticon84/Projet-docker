<?php
$title = "Modifier un prestataire";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Modifier un prestataire</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="provider.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données du prestataire...</p>
                </div>

                <!-- Form -->
                <div id="providerForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations du prestataire</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyProviderForm">
                            <input type="hidden" id="prestataire_id" name="prestataire_id">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Type de prestataire</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">Sélectionnez un type</option>
                                        <option value="Photographe">Photographe</option>
                                        <option value="Vidéaste">Vidéaste</option>
                                        <option value="DJ">DJ</option>
                                        <option value="Traiteur">Traiteur</option>
                                        <option value="Animateur">Animateur</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tarif" class="form-label">Tarif (€/heure)</label>
                                    <input type="number" class="form-control" id="tarif" name="tarif" step="1" min="10">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut_disponibilite" class="form-label">Disponible à partir de</label>
                                    <input type="date" class="form-control" id="date_debut_disponibilite" name="date_debut_disponibilite">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Disponible jusqu'à</label>
                                    <input type="date" class="form-control" id="date_fin_disponibilite" name="date_fin_disponibilite">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="estCandidat" name="est_candidat" value="1">
                                    <label class="form-check-label" for="estCandidat">
                                        Marquer comme candidat (en attente de validation)
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" id="deleteButton" class="btn btn-danger me-auto">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Prestataire non trouvé</h4>
                    <p>Le prestataire demandé n'existe pas ou a été supprimé.</p>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <a href="provider.php" class="btn btn-outline-danger">Retour à la liste</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération de l'ID du prestataire depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('prestataire_id');

            if (!id) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            // Récupération des données du prestataire
            fetch(`../../api/provider/getOne.php?prestataire_id=${id}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Prestataire non trouvé');
                    }
                    return response.json();
                })
                .then(provider => {
                    if (!provider) {
                        throw new Error('Prestataire non trouvé');
                    }
                    console.log(provider);

                    // Remplir le formulaire avec les données du prestataire
                    document.getElementById('prestataire_id').value = provider.prestataire_id;
                    document.getElementById('nom').value = provider.nom || '';
                    document.getElementById('prenom').value = provider.prenom || '';
                    document.getElementById('email').value = provider.email || '';
                    document.getElementById('type').value = provider.type || '';
                    document.getElementById('tarif').value = provider.tarif || '';

                    if (provider.date_debut_disponibilite) {
                        const dateDebut = new Date(provider.date_debut_disponibilite);
                        document.getElementById('date_debut_disponibilite').value = dateDebut.toISOString().split('T')[0]; // Format YYYY-MM-DD
                    }

                    if (provider.date_fin_disponibilite) {
                        const dateFin = new Date(provider.date_fin_disponibilite);
                        document.getElementById('date_fin_disponibilite').value = dateFin.toISOString().split('T')[0]; // Format YYYY-MM-DD
                    }

                    document.getElementById('estCandidat').checked = provider.est_candidat === '1';

                    // Afficher le formulaire
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('providerForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });
            // Gestion de la soumission du formulaire
            const form = document.getElementById('modifyProviderForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const providerData = {};


                formData.forEach((value, key) => {
                    providerData[key] = value;
                });

                // Convert checkbox value properly
                providerData.est_candidat = document.getElementById('estCandidat').checked ? 1 : 0;

                console.log(providerData);
                fetch('../../api/provider/modify.php', {
                        method: 'PATCH', // Utiliser PATCH pour les mises à jour partielles
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify(providerData),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Prestataire modifié avec succès!');
                            // Recharger les données pour afficher les modifications
                            window.location.reload();
                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de modifier le prestataire'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la modification du prestataire.');
                    });
            });

        });
    </script>
</body>

</html>
