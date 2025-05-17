<?php
$title = "Créer une société";
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
                        <h1 class="h2">Créer une société</h1>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations de la société</h5>
                    </div>
                    <div class="card-body">
                        <form id="createCompanyForm">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="adresse" name="adresse" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact_person" class="form-label">Personne de contact</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="telephone" name="telephone" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Créer la société</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('createCompanyForm');
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = {
                    nom: document.getElementById('nom').value,
                    email: document.getElementById('email').value,
                    adresse: document.getElementById('adresse').value,
                    contact_person: document.getElementById('contact_person').value,
                    password: document.getElementById('password').value,
                    telephone: document.getElementById('telephone').value
                };
                fetch('../../api/company/create.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.societe_id) {
                        alert("Société créée avec succès. ID: " + data.societe_id);
                        form.reset();
                    } else if (data.error) {
                        alert("Erreur : " + data.error);
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert("Une erreur est survenue : " + error.message);
                });
            });
        });
    </script>
</body>
</html>
