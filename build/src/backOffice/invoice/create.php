<?php
$title = "Créer une facture";
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
                    <h1 class="h2">Créer une facture</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="invoice.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations de la facture</h5>
                    </div>
                    <div class="card-body">
                        <form id="createInvoiceForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_emission" name="date_emission" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_echeance" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_echeance" name="date_echeance" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="id_prestataire" class="form-label">Prestataire <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_prestataire" name="id_prestataire" required>
                                        <option value="">Sélectionner un prestataire</option>
                                        <!-- Les options seront chargées dynamiquement -->
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_devis" class="form-label">Devis associé <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_devis" name="id_devis" required>
                                        <option value="">Sélectionner un devis</option>
                                        <!-- Les options seront chargées dynamiquement -->
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="montant_ht" class="form-label">Montant HT (€)</label>
                                    <input type="number" class="form-control" id="montant_ht" name="montant_ht" step="0.01" min="0" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="montant_tva" class="form-label">Montant TVA (€)</label>
                                    <input type="number" class="form-control" id="montant_tva" name="montant_tva" step="0.01" min="0" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="montant" class="form-label">Montant TTC (€)</label>
                                    <input type="number" class="form-control" id="montant" name="montant" step="0.01" min="0" readonly>
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
                                    <label for="methode_paiement" class="form-label">Méthode de paiement</label>
                                    <select class="form-select" id="methode_paiement" name="methode_paiement">
                                        <option value="">Sélectionner</option>
                                        <option value="Carte bancaire">Carte bancaire</option>
                                        <option value="Virement">Virement bancaire</option>
                                        <option value="Chèque">Chèque</option>
                                        <option value="Espèces">Espèces</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary">Réinitialiser</button>
                                <button type="submit" class="btn btn-primary">Créer la facture</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation de la date d'émission à aujourd'hui
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date_emission').value = today;

            // Par défaut, la date d'échéance est à today + 30 jours
            const defaultDueDate = new Date();
            defaultDueDate.setDate(defaultDueDate.getDate() + 30);
            document.getElementById('date_echeance').value = defaultDueDate.toISOString().split('T')[0];

            // Chargement des prestataires
            fetchProviders();

            // Ajout d'un événement pour charger les devis lorsqu'un prestataire est sélectionné
            document.getElementById('id_prestataire').addEventListener('change', fetchEstimates);

            // Ajout d'un événement pour mettre à jour les montants lorsqu'un devis est sélectionné
            document.getElementById('id_devis').addEventListener('change', updateAmountsFromEstimate);

            // Gestion de la soumission du formulaire
            document.getElementById('createInvoiceForm').addEventListener('submit', submitForm);
        });

        // Fonction pour récupérer la liste des prestataires
        function fetchProviders() {
            fetch('/api/provider/getVerifiedProviders.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la récupération des prestataires');
                    }
                    return response.json();
                })
                .then(data => {
                    const select = document.getElementById('id_prestataire');
                    data.forEach(provider => {
                        const option = document.createElement('option');
                        option.value = provider.id;
                        option.textContent = `${provider.name} ${provider.surname} (${provider.type})`;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showAlert('danger', 'Erreur lors du chargement des prestataires');
                });
        }

        // Fonction pour récupérer la liste des devis/contrats associés au prestataire sélectionné
        function fetchEstimates() {
            const providerId = document.getElementById('id_prestataire').value;
            const estimateSelect = document.getElementById('id_devis');

            // Vider la liste actuelle des devis
            estimateSelect.innerHTML = '<option value="">Sélectionner un devis</option>';

            // Si aucun prestataire n'est sélectionné, on ne fait rien
            if (!providerId) {
                return;
            }

            // Récupérer les contrats (devis) pour ce prestataire
            fetch(`/api/estimate/getContractByProvider.php?provider_id=${providerId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des devis');
                }
                return response.json();
            })
            .then(data => {
                // Filtrer pour ne garder que les contrats acceptés
                const acceptedContracts = data.filter(contract =>
                    contract.is_contract === 1 && contract.statut === 'accepté'
                );

                // Ajouter chaque contrat accepté à la liste déroulante
                acceptedContracts.forEach(contract => {
                    const option = document.createElement('option');
                    option.value = contract.devis_id;
                    option.textContent = `Contrat #${contract.devis_id} - ${contract.montant}€`;

                    // Stocker les montants comme attributs de données
                    option.dataset.montantHt = contract.montant_ht;
                    option.dataset.montantTva = contract.montant_tva;
                    option.dataset.montant = contract.montant;

                    estimateSelect.appendChild(option);
                });

                // Si aucun contrat accepté n'a été trouvé
                if (acceptedContracts.length === 0) {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "Aucun contrat disponible pour ce prestataire";
                    option.disabled = true;
                    estimateSelect.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('danger', 'Erreur lors du chargement des contrats');
            });
        }

        // Fonction pour mettre à jour les montants en fonction du devis sélectionné
        function updateAmountsFromEstimate() {
            const select = document.getElementById('id_devis');
            const selectedOption = select.options[select.selectedIndex];

            if (!select.value) {
                // Réinitialiser les valeurs si aucun devis n'est sélectionné
                document.getElementById('montant_ht').value = '';
                document.getElementById('montant_tva').value = '';
                document.getElementById('montant').value = '';
                return;
            }

            // Récupérer les montants associés au devis sélectionné
            document.getElementById('montant_ht').value = selectedOption.dataset.montantHt;
            document.getElementById('montant_tva').value = selectedOption.dataset.montantTva;
            document.getElementById('montant').value = selectedOption.dataset.montant;
        }

        // Fonction pour soumettre le formulaire
        function submitForm(event) {
            event.preventDefault();

            // Collecte des données du formulaire
            const formData = {
                date_emission: document.getElementById('date_emission').value,
                date_echeance: document.getElementById('date_echeance').value,
                id_prestataire: document.getElementById('id_prestataire').value,
                id_devis: document.getElementById('id_devis').value,
                montant_ht: document.getElementById('montant_ht').value,
                montant_tva: document.getElementById('montant_tva').value,
                montant: document.getElementById('montant').value,
                statut: document.getElementById('statut').value,
                methode_paiement: document.getElementById('methode_paiement').value
            };

            // Validation basique côté client
            if (new Date(formData.date_emission) > new Date(formData.date_echeance)) {
                showAlert('danger', 'La date d\'émission doit être antérieure à la date d\'échéance');
                return;
            }

            // Envoi des données à l'API
            fetch('/api/invoice/create.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Erreur lors de la création de la facture');
                    });
                }
                return response.json();
            })
            .then(data => {
                showAlert('success', 'Facture créée avec succès');
                // Redirection vers la page de détail de la facture après 2 secondes
                setTimeout(() => {
                    window.location.href = `detail.php?id=${data.id}`;
                }, 2000);
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('danger', error.message);
            });
        }

        // Fonction pour afficher une alerte
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // Insérer l'alerte au début du formulaire
            const form = document.getElementById('createInvoiceForm');
            form.parentNode.insertBefore(alertDiv, form);

            // Faire disparaître l'alerte après 5 secondes
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }, 5000);
        }
    </script>
</body>
</html>
