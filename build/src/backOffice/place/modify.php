<?php
$title = "Modifier un lieu";
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
                    <h1 class="h2">Modifier un lieu</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="place.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données du lieu...</p>
                </div>

                <!-- Form -->
                <div id="placeForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations du lieu</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyPlaceForm">
                            <input type="hidden" id="lieu_id" name="lieu_id">

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ville" class="form-label">Ville <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ville" name="ville" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="code_postal" class="form-label">Code postal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="code_postal" name="code_postal" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Lieu non trouvé</h4>
                    <p>Le lieu demandé n'existe pas ou a été supprimé.</p>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <a href="place.php" class="btn btn-outline-danger">Retour à la liste</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération de l'ID du lieu depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const placeId = urlParams.get('id');

            if (!placeId) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            // Récupération des données du lieu
            fetch(`../../api/place/getOne.php?lieu_id=${placeId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Lieu non trouvé');
                    }
                    return response.json();
                })
                .then(place => {
                    if (!place) {
                        throw new Error('Lieu non trouvé');
                    }

                    // Remplir le formulaire avec les données du lieu
                    document.getElementById('lieu_id').value = place.lieu_id;
                    document.getElementById('adresse').value = place.adresse || '';
                    document.getElementById('ville').value = place.ville || '';
                    document.getElementById('code_postal').value = place.code_postal || '';

                    // Afficher le formulaire
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('placeForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });

            // Gestion de la soumission du formulaire
            const form = document.getElementById('modifyPlaceForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validation
                if (!validateForm()) {
                    return;
                }

                const formData = new FormData(form);
                const placeData = {};

                formData.forEach((value, key) => {
                    placeData[key] = value;
                });

                fetch('../../api/place/update.php', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(placeData),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.lieu_id) {
                        alert('Lieu modifié avec succès!');
                        // Recharger les données pour afficher les modifications
                        window.location.reload();
                    } else {
                        alert('Erreur: ' + (data.error || 'Impossible de modifier le lieu'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la modification du lieu.');
                });
            });

            function validateForm() {
                // Validation du code postal
                const codePostal = document.getElementById('code_postal');
                if (codePostal.value.length !== 5 || isNaN(codePostal.value)) {
                    alert('Le code postal doit contenir 5 chiffres.');
                    codePostal.focus();
                    return false;
                }

                // Validation de l'adresse
                const adresse = document.getElementById('adresse');
                if (adresse.value.trim().length < 3) {
                    alert('Veuillez entrer une adresse valide.');
                    adresse.focus();
                    return false;
                }

                // Validation de la ville
                const ville = document.getElementById('ville');
                if (ville.value.trim().length < 2) {
                    alert('Veuillez entrer un nom de ville valide.');
                    ville.focus();
                    return false;
                }

                return true;
            }
        });
    </script>
</body>
</html>
