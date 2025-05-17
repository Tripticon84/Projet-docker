<?php
$title = "Modifier un contrat ou un devis";
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
                        <a href="/backoffice/contract/contract.php" class="btn btn-link text-decoration-none me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="h2">Modifier un devis</h1>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données...</p>
                </div>

                <!-- Formulaire pour Devis -->
                <div id="estimateForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Modifier un Devis</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyEstimateForm">
                            <input type="hidden" id="devis_id" name="devis_id" value="">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Date début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Date fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="form-select" id="statut" name="statut" required>
                                        <option value="brouillon">Brouillon</option>
                                        <option value="envoyé">Envoyé</option>
                                        <option value="accepté">Accepté</option>
                                        <option value="refusé">Refusé</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="montant" class="form-label">Montant <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="montant" name="montant" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="montant_ht" class="form-label">Montant HT</label>
                                    <input type="number" class="form-control" id="montant_ht" name="montant_ht">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="montant_tva" class="form-label">Montant TVA</label>
                                    <input type="number" class="form-control" id="montant_tva" name="montant_tva">
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Non trouvé</h4>
                    <p>Le devis demandé n'existe pas ou a été supprimé.</p>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <a href="contract.php" class="btn btn-outline-danger">Retour à la liste</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const devisId = urlParams.get('id');

            if (!devisId) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            fetch(`../../api/estimate/getOne.php?devis_id=${devisId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Non trouvé');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) {
                        throw new Error('Non trouvé');
                    }

                    document.getElementById('devis_id').value = data.devis_id;
                    document.getElementById('date_debut').value = data.date_debut || '';
                    document.getElementById('date_fin').value = data.date_fin || '';
                    document.getElementById('statut').value = data.statut || '';
                    document.getElementById('montant').value = data.montant || '';
                    document.getElementById('montant_ht').value = data.montant_ht || '';
                    document.getElementById('montant_tva').value = data.montant_tva || '';
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('estimateForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });

            const estimateForm = document.getElementById('modifyEstimateForm');

            estimateForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(estimateForm);
                const data = {};

                formData.forEach((value, key) => {
                    data[key] = value;
                });

                fetch('../../api/estimate/update.php', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken(),
                    },
                    body: JSON.stringify(data),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Modification réussie!');
                            window.location.href = 'contract.php';
                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de modifier'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la modification.');
                    });
            });
        });
    </script>
</body>

</html>