<?php
$title = "Créer un lieu";
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
                    <h1 class="h2">Créer un lieu</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="place.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations du lieu</h5>
                    </div>
                    <div class="card-body">
                        <form id="createPlaceForm">
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
                                <button type="reset" class="btn btn-outline-secondary">Réinitialiser</button>
                                <button type="submit" class="btn btn-primary">Créer le lieu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission
            const form = document.getElementById('createPlaceForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validation
                if (!validateForm()) {
                    return;
                }

                const formData = new FormData(form);
                const placeData = {};

                formData.forEach((value, key) => {
                    // Convert code_postal to integer
                    if (key === 'code_postal') {
                        placeData[key] = parseInt(value, 10);
                    } else {
                        placeData[key] = value;
                    }
                });

                fetch('../../api/place/create.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(placeData),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.lieu_id) {
                        alert('Lieu créé avec succès!');
                        window.location.href = 'place.php';
                    } else {
                        alert('Erreur: ' + (data.error || 'Impossible de créer le lieu'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la création du lieu.');
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
