<?php
$title = "Créer une Association";
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
                    <h1 class="h2">Créer une Association</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="association.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Create Association Form -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Formulaire d'ajout d'association</h5>
                    </div>
                    <div class="card-body">
                        <form id="associationForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom de l'association *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="form-text">Entrez le nom officiel de l'association.</div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                <div class="form-text">Décrivez brièvement les activités et la mission de l'association.</div>
                            </div>
                            <div class="mb-3">
                                <label for="logo" class="form-label">Logo</label>
                                <input type="text" class="form-control" id="logo" name="logo">
                                <div class="form-text">URL du logo de l'association.</div>
                            </div>
                            <div class="mb-3">
                                <label for="banniere" class="form-label">Bannière</label>
                                <input type="text" class="form-control" id="banniere" name="banniere">
                                <div class="form-text">URL de la bannière de l'association.</div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='association.php'">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('submitBtn').addEventListener('click', function() {
                createAssociation();
            });
        });

        function createAssociation() {
            const name = document.getElementById('name').value.trim();
            const description = document.getElementById('description').value.trim();

            if (!name || !description) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            // Désactiver le bouton pendant la soumission
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';

            fetch('../../api/association/create.php', {
                method: 'PUT', // Assurez-vous que la méthode est POST
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify({
                    name: name,
                    description: description
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.associationid) {
                    alert('Association créée avec succès !');
                    window.location.href = 'association.php';
                } else {
                    alert('Erreur lors de la création de l\'association. Veuillez réessayer.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Enregistrer';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite. Veuillez réessayer plus tard.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Enregistrer';
            });
        }
    </script>
</body>
</html>
