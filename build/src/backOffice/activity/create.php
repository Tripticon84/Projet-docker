<?php
$title = "Création d'une Activité";
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
                    <h1 class="h2">Création d'une Activité</h1>
                    <div>
                        <a href="activity.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>

                <!-- Activity Creation Form -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations de l'activité</h5>
                    </div>
                    <div class="card-body">
                        <form id="activityForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom de l'activité <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="type" class="form-label">Type d'activité <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Sélectionnez un type</option>
                                        <option value="Séminaire">Séminaire</option>
                                        <option value="Conférence">Conférence</option>
                                        <option value="Team Building">Team Building</option>
                                        <option value="Formation">Formation</option>
                                        <option value="Événement">Événement</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>

                                <!-- Lieu section -->
                                <div class="col-md-6">
                                    <label for="id_lieu" class="form-label">Lieu <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_lieu" name="id_lieu" required>
                                        <option value="">Sélectionnez un lieu</option>
                                    </select>
                                    <div id="lieu_details" class="mt-2 small text-muted"></div>
                                </div>
                            </div>

                            <!-- Section Prestataire -->
                            <div class="mb-4">
                                <label class="form-label">Prestataire</label>
                                <div class="provider-selector mb-3">
                                    <div class="d-flex justify-content-start align-items-center mb-2">
                                        <div>
                                            <input type="radio" class="form-check-input me-2" id="no_provider" name="provider_option" value="none" checked>
                                            <label for="no_provider" class="form-check-label">Aucun prestataire</label>
                                        </div>
                                        <div>
                                            <input type="radio" class="form-check-input ms-4" id="select_provider" name="provider_option" value="select">
                                            <label for="select_provider" class="form-check-label">Sélectionner un prestataire</label>
                                        </div>
                                    </div>

                                    <div id="provider_selection_container" class="mt-3" style="display: none;">
                                        <div class="mb-3">
                                            <input type="text" id="provider_search" class="form-control" placeholder="Rechercher un prestataire...">
                                        </div>
                                        <div id="provider_cards_container" class="row row-cols-1 row-cols-md-2 g-3">
                                            <!-- Les cartes des prestataires seront ajoutées ici dynamiquement -->
                                        </div>
                                        <input type="hidden" id="id_prestataire" name="id_prestataire" value="">
                                    </div>
                                </div>
                            </div>

                            <!-- Section Devis -->
                            <div class="mb-4">
                                <label class="form-label">Devis associé</label>
                                <div class="quote-selector mb-3">
                                    <div class="d-flex justify-content-start align-items-center mb-2">
                                        <div>
                                            <input type="radio" class="form-check-input me-2" id="no_quote" name="quote_option" value="none" checked>
                                            <label for="no_quote" class="form-check-label">Aucun devis</label>
                                        </div>
                                        <div>
                                            <input type="radio" class="form-check-input ms-4" id="select_quote" name="quote_option" value="select">
                                            <label for="select_quote" class="form-check-label">Sélectionner un devis</label>
                                        </div>
                                    </div>

                                    <div id="quote_selection_container" class="mt-3" style="display: none;">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="quotes_table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Date début</th>
                                                        <th>Date fin</th>
                                                        <th>Montant HT</th>
                                                        <th>Montant TVA</th>
                                                        <th>Montant TTC</th>
                                                        <th>Statut</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Les devis seront ajoutés ici dynamiquement -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <input type="hidden" id="id_devis" name="id_devis" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.href='activity.php'">Annuler</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Créer l'activité
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
            // Charger les lieux
            loadLocations();

            // Configuration des options pour prestataires
            document.querySelectorAll('input[name="provider_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('provider_selection_container').style.display =
                        this.value === 'select' ? 'block' : 'none';

                    if (this.value === 'none') {
                        document.getElementById('id_prestataire').value = '';
                    } else if (this.value === 'select' && document.getElementById('provider_cards_container').children.length === 0) {
                        loadProviders();
                    }
                });
            });

            // Configuration des options pour devis
            document.querySelectorAll('input[name="quote_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('quote_selection_container').style.display =
                        this.value === 'select' ? 'block' : 'none';

                    if (this.value === 'none') {
                        document.getElementById('id_devis').value = '';
                    } else if (this.value === 'select' && document.getElementById('quotes_table').querySelector('tbody').children.length === 0) {
                        loadQuotes();
                    }
                });
            });

            // Écouteur pour le champ de recherche des prestataires
            document.getElementById('provider_search').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('#provider_cards_container .provider-card');

                cards.forEach(card => {
                    const name = card.querySelector('.provider-name').textContent.toLowerCase();
                    const type = card.querySelector('.provider-type').textContent.toLowerCase();

                    if (name.includes(searchTerm) || type.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Écouteur de changement pour lieu sélectionné
            document.getElementById('id_lieu').addEventListener('change', function() {
                showLocationDetails(this.value);
            });

            // Gérer la soumission du formulaire
            document.getElementById('activityForm').addEventListener('submit', function(e) {
                e.preventDefault();
                createActivity();
            });
        });

        function loadLocations() {
            const locationSelect = document.getElementById('id_lieu');

            fetch('../../api/place/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    locationSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>';

                    // Stocker les détails du lieu pour affichage ultérieur
                    window.locationDetails = {};

                    data.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.lieu_id;
                        option.textContent = `${location.adresse}, ${location.ville} (${location.code_postal})`;
                        locationSelect.appendChild(option);

                        // Stocker les détails pour référence
                        window.locationDetails[location.lieu_id] = location;
                    });
                } else {
                    locationSelect.innerHTML = '<option value="" disabled>Aucun lieu disponible</option>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des lieux:', error);
                locationSelect.innerHTML = '<option value="" disabled>Erreur de chargement</option>';
            });
        }

        function showLocationDetails(locationId) {
            const detailsDiv = document.getElementById('lieu_details');

            if (!locationId || !window.locationDetails || !window.locationDetails[locationId]) {
                detailsDiv.innerHTML = '';
                return;
            }

            const location = window.locationDetails[locationId];
            detailsDiv.innerHTML = `
                <div class="card p-2 bg-light">
                    <p class="m-0"><strong>Adresse complète:</strong> ${location.adresse}</p>
                    <p class="m-0"><strong>Ville:</strong> ${location.ville}</p>
                    <p class="m-0"><strong>Code Postal:</strong> ${location.code_postal}</p>
                </div>
            `;
        }

        function loadProviders() {
            const container = document.getElementById('provider_cards_container');
            container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"></div></div>';

            fetch('../../api/provider/getVerifiedProviders.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                console.log('Provider data:', data); // Debug logging

                if (data && data.length > 0) {
                    data.forEach(provider => {
                        const col = document.createElement('div');
                        col.className = 'col';

                        const card = document.createElement('div');
                        card.className = 'card provider-card h-100';

                        // Extract name parts safely
                        const lastName = provider.name || '';
                        const firstName = provider.surname || '';
                        const fullName = lastName && firstName ? `${lastName} ${firstName}` : (lastName || firstName || 'Nom non spécifié');

                        const email = provider.email || '';
                        const type = provider.type || 'Type non spécifié';
                        const providerId = provider.prestataire_id || provider.id || '';

                        // Check for availability dates with fallbacks
                        const startDate = provider.date_debut_disponibilite || provider.start_date || null;
                        const endDate = provider.date_fin_disponibilite || provider.end_date || null;

                        const availability = startDate && endDate ?
                            `Disponible du ${formatDate(startDate)} au ${formatDate(endDate)}` :
                            'Disponibilité non spécifiée';

                        // Get rate with fallback
                        const rate = provider.tarif || provider.price || null;

                        card.innerHTML = `
                            <div class="card-body">
                                <h5 class="card-title provider-name">${fullName}</h5>
                                <p class="card-text provider-type text-muted mb-1">${type}</p>
                                <p class="card-text mb-1"><small>${email}</small></p>
                                <p class="card-text"><small class="text-muted">${availability}</small></p>
                                ${rate ? `<p class="card-text text-primary fw-bold">${rate}€ / jour</p>` : ''}
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2 select-provider"
                                        data-provider-id="${providerId}">
                                    Sélectionner
                                </button>
                            </div>
                        `;

                        // Ajouter un gestionnaire d'événements pour le bouton de sélection
                        card.querySelector('.select-provider').addEventListener('click', function() {
                            selectProvider(providerId, fullName);
                        });

                        col.appendChild(card);
                        container.appendChild(col);
                    });
                } else {
                    container.innerHTML = '<div class="col-12"><p class="text-center text-muted">Aucun prestataire disponible</p></div>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des prestataires:', error);
                container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Erreur lors du chargement des prestataires</p></div>';
            });
        }

        function selectProvider(providerId, providerName) {
            document.getElementById('id_prestataire').value = providerId;

            // Mise en évidence visuelle de la carte sélectionnée
            document.querySelectorAll('#provider_cards_container .provider-card').forEach(card => {
                card.classList.remove('border-primary');
                card.classList.remove('bg-light');
            });

            const selectedButton = document.querySelector(`.select-provider[data-provider-id="${providerId}"]`);
            if (selectedButton) {
                const card = selectedButton.closest('.provider-card');
                card.classList.add('border-primary');
                card.classList.add('bg-light');

                // Mettre à jour le texte du bouton
                selectedButton.textContent = 'Sélectionné';
                selectedButton.classList.remove('btn-outline-primary');
                selectedButton.classList.add('btn-primary');

                // Réinitialiser les autres boutons
                document.querySelectorAll('.select-provider').forEach(btn => {
                    if (btn !== selectedButton) {
                        btn.textContent = 'Sélectionner';
                        btn.classList.add('btn-outline-primary');
                        btn.classList.remove('btn-primary');
                    }
                });

            }
        }

        function loadQuotes() {
            const tableBody = document.querySelector('#quotes_table tbody');
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Chargement des devis...</td></tr>';

            fetch('../../api/estimate/getAllEstimate.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';

                if (data && data.length > 0) {
                    data.forEach(quote => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${quote.devis_id}</td>
                            <td>${formatDate(quote.date_debut)}</td>
                            <td>${formatDate(quote.date_fin)}</td>
                            <td>${formatMontant(quote.montant_ht)} €</td>
                            <td>${formatMontant(quote.montant_tva)} €</td>
                            <td>${formatMontant(quote.montant)} €</td>
                            <td><span class="badge ${getStatusBadgeClass(quote.statut)}">${quote.statut || 'N/A'}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary select-quote"
                                        data-quote-id="${quote.devis_id}">
                                    Sélectionner
                                </button>
                            </td>
                        `;

                        // Ajouter un gestionnaire d'événements pour le bouton de sélection
                        row.querySelector('.select-quote').addEventListener('click', function() {
                            selectQuote(quote.devis_id);
                        });

                        tableBody.appendChild(row);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun devis disponible</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des devis:', error);
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des devis</td></tr>';
            });
        }

        function formatMontant(montant) {
            if (!montant) return '0.00';
            return parseFloat(montant).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }

        function selectQuote(quoteId) {
            document.getElementById('id_devis').value = quoteId;

            // Mise en évidence visuelle de la ligne sélectionnée
            document.querySelectorAll('#quotes_table tbody tr').forEach(row => {
                row.classList.remove('table-primary');
            });

            const selectedButton = document.querySelector(`.select-quote[data-quote-id="${quoteId}"]`);
            if (selectedButton) {
                const row = selectedButton.closest('tr');
                row.classList.add('table-primary');

                // Mettre à jour le texte du bouton
                selectedButton.textContent = 'Sélectionné';
                selectedButton.classList.remove('btn-outline-primary');
                selectedButton.classList.add('btn-primary');

                // Réinitialiser les autres boutons
                document.querySelectorAll('.select-quote').forEach(btn => {
                    if (btn !== selectedButton) {
                        btn.textContent = 'Sélectionner';
                        btn.classList.add('btn-outline-primary');
                        btn.classList.remove('btn-primary');
                    }
                });

            }
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'Validé':
                case 'Approuvé':
                    return 'bg-success';
                case 'En attente':
                    return 'bg-warning';
                case 'Refusé':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        function createActivity() {
            const form = document.getElementById('activityForm');

            // Validate required fields
            const nom = form.nom.value.trim();
            const type = form.type.value.trim();
            const date = form.date.value.trim();
            const lieu = form.id_lieu.value ? parseInt(form.id_lieu.value) : null;

            // Parse optional fields with null values if not present
            const id_prestataire = document.getElementById('id_prestataire').value ?
                parseInt(document.getElementById('id_prestataire').value) : null;

            const id_devis = document.getElementById('id_devis').value ?
                parseInt(document.getElementById('id_devis').value) : null;

            if (!nom || !type || !date) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            const formData = {
                nom: nom,
                type: type,
                date: date
            };

            // N'ajouter les champs optionnels que s'ils ont des valeurs
            if (id_devis) formData.id_devis = id_devis;
            if (id_prestataire) formData.id_prestataire = id_prestataire;
            if (lieu) formData.id_lieu = lieu;

            console.log('Données envoyées:', formData);

            fetch('../../api/activity/create.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                console.log('Status code:', response.status);
                return response.json();
            })
            .then(data => {
                if (data && data.activity_id) {
                    alert('Activité créée avec succès!');
                    window.location.href = 'activity.php';
                } else {
                    alert('Erreur lors de la création de l\'activité: ' + (data.message || 'Veuillez vérifier les données saisies.'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la création de l\'activité.');
            });
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        }
    </script>

    <style>
        .form-label {
            font-weight: 500;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .provider-card {
            transition: all 0.2s ease;
            border: 1px solid #dee2e6;
        }

        .provider-card:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
            border-color: #adb5bd;
        }

        .provider-card.border-primary {
            border-width: 2px;
        }

        #provider_selection_container, #quote_selection_container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
        }

        #provider_search {
            max-width: 400px;
            margin-bottom: 1rem;
        }

        .table .badge {
            font-size: 0.75rem;
        }
    </style>
</body>
</html>
