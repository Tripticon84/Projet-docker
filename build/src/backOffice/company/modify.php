<?php
$title = "Modifier une société";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Entête -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="/backoffice/company/company.php" class="btn btn-link text-decoration-none me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="h2">Modifier une société</h1>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données de la société...</p>
                </div>

                <!-- Form -->
                <div id="companyForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations de la société</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyCompanyForm">
                            <input type="hidden" id="id" name="id" value="">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_person" class="form-label">Personne de contact <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="telephone" name="telephone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Laissez vide si vous ne souhaitez pas changer le mot de passe.</div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Société non trouvée</h4>
                    <p>La société demandée n'existe pas ou a été supprimée.</p>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <a href="company.php" class="btn btn-outline-danger">Retour à la liste</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération de l'ID de la société depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const companyId = urlParams.get('id');

            if (!companyId) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            // Récupération des données de la société
            fetch(`../../api/company/getOne.php?societe_id=${companyId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Société non trouvée');
                    }
                    return response.json();
                })
                .then(company => {
                    if (!company) {
                        throw new Error('Société non trouvée');
                    }
                    console.log(company);

                    // Remplir le formulaire avec les données de la société
                    document.getElementById('id').value = company.societe_id;
                    document.getElementById('nom').value = company.nom || '';
                    document.getElementById('email').value = company.email || '';
                    document.getElementById('adresse').value = company.adresse || '';
                    document.getElementById('contact_person').value = company.contact_person || '';
                    document.getElementById('telephone').value = company.telephone || '';

                    // Afficher le formulaire
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('companyForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });
            // Gestion de la soumission du formulaire
            const form = document.getElementById('modifyCompanyForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const companyData = {};

                formData.forEach((value, key) => {
                    companyData[key] = value;
                    console.log(value);
                });

                fetch('../../api/company/update.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken(),
                        },
                        body: JSON.stringify(companyData),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.empty) {
                            alert('Société modifiée avec succès!');
                            // Recharger les données pour afficher les modifications
                            window.location.reload();
                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de modifier la société'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la modification de la société.');
                    });
            });

        });
    </script>
</body>

</html>
