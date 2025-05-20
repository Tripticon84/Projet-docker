<?php
$title = "Créer un prestataire";
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
                    <h1 class="h2">Créer un prestataire</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="provider.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations du prestataire</h5>
                    </div>
                    <div class="card-body">
                        <form id="createProviderForm">
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
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
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
                                    <input type="number" class="form-control" id="tarif" name="tarif" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Disponible à partir de</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut_disponibilite">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Disponible jusqu'à</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin_disponibilite">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="estCandidat" name="est_candidat" value="1" checked>
                                    <label class="form-check-label" for="estCandidat">
                                        Marquer comme candidat (en attente de validation)
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary">Réinitialiser</button>
                                <button type="submit" class="btn btn-primary">Créer le prestataire</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Form submission
            const form = document.getElementById('createProviderForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validation
                if (!validateForm()) {
                    return;
                }

                const formData = new FormData(form);
                const providerData = {};

                formData.forEach((value, key) => {
                    providerData[key] = value;
                });

                // Convert checkbox value properly
                providerData.est_candidat = document.getElementById('estCandidat').checked ? 1 : 0;

                fetch('../../api/provider/create.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(providerData),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.prestataire_id) {
                        alert('Prestataire créé avec succès!');
                        window.location.href = 'provider.php';
                    } else {
                        alert('Erreur: ' + (data.error || 'Impossible de créer le prestataire'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la création du prestataire.');
                });
            });

            function validateForm() {
                // Validation du mot de passe
                if (password.value.length < 8) {
                    alert('Le mot de passe doit contenir au moins 8 caractères.');
                    password.focus();
                    return false;
                }

                // Validation de l'email
                const email = document.getElementById('email');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    alert('Veuillez entrer une adresse email valide.');
                    email.focus();
                    return false;
                }

                return true;
            }
        });
    </script>
</body>
</html>
