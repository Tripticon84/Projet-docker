<?php
$title = "Créer un devis/contrat";
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
                    <h1 class="h2">Créer un devis/contrat</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="contract.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <div id="contractForm" class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations du devis/contrat</h5>
                    </div>
                    <div class="card-body">
                        <form id="createContractForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statut" class="form-label">Statut</label>
                                    <select class="form-select" id="statut" name="statut">
                                        <option value="brouillon" selected>Brouillon</option>
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
                                    <label for="montant_ht" class="form-label">Montant HT (€) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="montant_ht" name="montant_ht" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="frais_ids" class="form-label">Frais associés</label>
                                    <select class="form-select" id="frais_ids" name="frais_ids[]" multiple>
                                        <!-- Les options seront ajoutées dynamiquement -->
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_contract" name="is_contract" value="1">
                                        <label class="form-check-label" for="is_contract">
                                            Marquer comme contrat
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Créer le devis/contrat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Charger la liste des sociétés
            loadCompanies();

            // Charger la liste des frais
            loadFrais();

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

            // Fonction pour charger la liste des frais
            function loadFrais() {
                fetch('../../api/fees/getAll.php', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => response.json())
                .then(frais => {
                    const selectFrais = document.getElementById('frais_ids');

                    // Vider les options existantes
                    while (selectFrais.options.length > 0) {
                        selectFrais.remove(0);
                    }

                    // Ajouter les frais
                    frais.forEach(f => {
                        const option = document.createElement('option');
                        option.value = f.frais_id;
                        option.textContent = `${f.nom} (${f.montant} €)`;
                        if (f.est_abonnement == 1) {
                            option.textContent += ' [Abonnement]';
                        }
                        selectFrais.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des frais:', error);
                });
            }

            // Gestion de la soumission du formulaire
            const form = document.getElementById('createContractForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const contractData = {};

                formData.forEach((value, key) => {
                    if (key === 'frais_ids[]') {
                        if (!contractData.frais_ids) {
                            contractData.frais_ids = [];
                        }
                        contractData.frais_ids.push(value);
                    } else {
                        contractData[key] = value;
                    }
                });

                // Convert checkbox value properly
                contractData.is_contract = document.getElementById('is_contract').checked ? 1 : 0;

                console.log(contractData);
                fetch('../../api/estimate/create.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(contractData),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.id) {
                        alert('Devis/Contrat créé avec succès!');
                        // Rediriger vers la page de modification du devis créé
                        window.location.href = `modify.php?id=${data.id}`;
                    } else {
                        alert('Erreur: ' + (data.error || 'Impossible de créer le devis/contrat'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la création du devis/contrat.');
                });
            });
        });
    </script>
</body>

</html>
