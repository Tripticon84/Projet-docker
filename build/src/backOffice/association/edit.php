<?php
$title = "Modifier une association";
include_once "../includes/head.php";
include_once "../../api/dao/association.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Entête -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="association.php" class="btn btn-link text-decoration-none me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="h2">Modifier une association</h1>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données de l'association...</p>
                </div>

                <!-- Form -->
                <div id="associationForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations de l'association</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyAssociationForm">
                            <input type="hidden" id="id" name="association_id" value="">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="text" class="form-control" id="logo" name="logo">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="banniere" class="form-label">Bannière</label>
                                    <input type="text" class="form-control" id="banniere" name="banniere">
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Association non trouvée</h4>
                    <p>L'association demandée n'existe pas ou a été supprimée.</p>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <a href="association.php" class="btn btn-outline-danger">Retour à la liste</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération de l'ID de l'association depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const associationId = urlParams.get('id');

            if (!associationId) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            // Récupération des données de l'association
            fetch(`/api/association/getOne.php?association_id=${associationId}`, { // Correction du paramètre utilisé
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Association non trouvée');
                    }
                    return response.json();
                })
                .then(association => {
                    if (!association) {
                        throw new Error('Association non trouvée');
                    }

                    // Remplir le formulaire avec les données de l'association
                    document.getElementById('id').value = association.id; // Correction du champ ID
                    document.getElementById('name').value = association.name || '';
                    document.getElementById('description').value = association.description || '';
                    document.getElementById('logo').value = association.logo || '';
                    document.getElementById('banniere').value = association.banniere || '';

                    // Afficher le formulaire
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('associationForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });

            // Gestion de la soumission du formulaire
            const form = document.getElementById('modifyAssociationForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const associationData = {};

                formData.forEach((value, key) => {
                    associationData[key] = value;
                });

                fetch('/api/association/update.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken(),
                        },
                        body: JSON.stringify(associationData),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error){
                            alert('Association modifiée avec succès!');

                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de modifier l\'association'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la modification de l\'association.');
                    });
            });

        });
    </script>
</body>