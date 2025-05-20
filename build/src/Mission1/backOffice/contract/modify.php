<?php
$title = "Modifier un devis/contrat";
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
                    <h1 class="h2">Modifier un devis/contrat</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="contract.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données du devis/contrat...</p>
                </div>

                <!-- Form -->
                <div id="contractForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations du devis/contrat</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyContractForm">
                            <input type="hidden" id="devis_id" name="devis_id">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statut" class="form-label">Statut</label>
                                    <select class="form-select" id="statut" name="statut">
                                        <option value="brouillon">Brouillon</option>
                                        <option value="envoyé">Envoyé</option>
                                        <option value="accepté">Accepté</option>
                                        <option value="refusé">Refusé</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_societe" class="form-label">Société <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_societe" name="id_societe" required>
                                        <option value="">Sélectionnez une société</option>
                                        <!-- Les options seront ajoutées dynamiquement -->
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="montant" class="form-label">Montant total (€)</label>
                                    <input type="number" class="form-control" id="montant" name="montant" step="0.01" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="montant_ht" class="form-label">Montant HT (€)</label>
                                    <input type="number" class="form-control" id="montant_ht" name="montant_ht" step="0.01" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="montant_tva" class="form-label">Montant TVA (€)</label>
                                    <input type="number" class="form-control" id="montant_tva" name="montant_tva" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_contract" name="is_contract" value="1">
                                    <label class="form-check-label" for="is_contract">
                                        Marquer comme contrat
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" id="deleteButton" class="btn btn-danger me-auto">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                                <button type="button" id="generatePdfButton" class="btn btn-secondary">
                                    <i class="fas fa-file-pdf"></i> Générer PDF
                                </button>
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Devis/Contrat non trouvé</h4>
                    <p>Le devis ou contrat demandé n'existe pas ou a été supprimé.</p>
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
            // Récupération de l'ID du devis/contrat depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const contractId = urlParams.get('id');

            // Charger la liste des sociétés
            loadCompanies();

            if (!contractId) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            // Fonction pour charger la liste des sociétés
            function loadCompanies() {
                fetch('../../api/company/getAll.php', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => response.json())
                .then(companies => {
                    const selectCompany = document.getElementById('id_societe');

                    // Vider les options existantes sauf la première
                    while (selectCompany.options.length > 1) {
                        selectCompany.remove(1);
                    }

                    // Ajouter les sociétés
                    companies.forEach(company => {
                        const option = document.createElement('option');
                        option.value = company.societe_id;
                        option.textContent = company.nom;
                        // Ajouter une information supplémentaire pour les sociétés désactivées
                        if (company.desactivate == 1) {
                            option.textContent += ' [Désactivée]';
                            option.classList.add('text-muted');
                        }
                        selectCompany.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des sociétés:', error);
                });
            }

            // Récupération des données du devis/contrat
            fetch(`../../api/estimate/getOne.php?devis_id=${contractId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Devis/Contrat non trouvé');
                    }
                    return response.json();
                })
                .then(contract => {
                    if (!contract) {
                        throw new Error('Devis/Contrat non trouvé');
                    }
                    console.log(contract);

                    // Remplir le formulaire avec les données du devis/contrat
                    document.getElementById('devis_id').value = contract.devis_id;

                    if (contract.date_debut) {
                        const dateDebut = new Date(contract.date_debut);
                        document.getElementById('date_debut').value = dateDebut.toISOString().split('T')[0];
                    }

                    if (contract.date_fin) {
                        const dateFin = new Date(contract.date_fin);
                        document.getElementById('date_fin').value = dateFin.toISOString().split('T')[0];
                    }

                    document.getElementById('statut').value = contract.statut || 'brouillon';

                    // Attendre un moment pour que les sociétés soient chargées avant de définir la valeur
                    setTimeout(() => {
                        document.getElementById('id_societe').value = contract.id_societe || '';
                    }, 500);

                    document.getElementById('montant').value = contract.montant || '';
                    document.getElementById('montant_ht').value = contract.montant_ht || '';
                    document.getElementById('montant_tva').value = contract.montant_tva || '';
                    document.getElementById('is_contract').checked = contract.is_contract === '1';

                    // Afficher le formulaire
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('contractForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });

            // Gestion du bouton de génération de PDF
            document.getElementById('generatePdfButton').addEventListener('click', function() {
                const devisId = document.getElementById('devis_id').value;
                openPDF(devisId);
            });

            // Fonction pour ouvrir le PDF
            function openPDF(devisId) {
                const token = getToken();
                const url = `../../api/estimate/generatePDF.php?devis_id=${devisId}`;
                window.open(url, '_blank');
            }

            // Gestion de la soumission du formulaire
            const form = document.getElementById('modifyContractForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const contractData = {};

                formData.forEach((value, key) => {
                    contractData[key] = value;
                });

                // Convert checkbox value properly
                contractData.is_contract = document.getElementById('is_contract').checked ? 1 : 0;

                console.log(contractData);
                fetch('../../api/estimate/update.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify(contractData),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Devis/Contrat modifié avec succès!');
                            // Recharger les données pour afficher les modifications
                            window.location.reload();
                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de modifier le devis/contrat'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la modification du devis/contrat.');
                    });
            });

            // Gestion du bouton de suppression
            document.getElementById('deleteButton').addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce devis/contrat ?')) {
                    const devisId = document.getElementById('devis_id').value;

                    fetch('../../api/estimate/delete.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify({ devis_id: devisId }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Devis/Contrat supprimé avec succès!');
                            window.location.href = 'contract.php';
                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de supprimer le devis/contrat'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la suppression du devis/contrat.');
                    });
                }
            });
        });
    </script>
</body>

</html>
