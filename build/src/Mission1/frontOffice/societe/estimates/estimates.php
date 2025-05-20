<?php

$title = "Gestion des Devis";

include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Devis</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                        <a href="new_estimate.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Demander un nouveau devis
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtres de recherche -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filtres</h5>
                </div>
                <div class="card-body">
                    <form id="estimateFilterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Statut</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">Tous</option>
                                <option value="brouillon">Brouillon</option>
                                <option value="envoyé">Envoyé</option>
                                <option value="accepté">Accepté</option>
                                <option value="refusé">Refusé</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateStartFilter" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="dateStartFilter">
                        </div>
                        <div class="col-md-3">
                            <label for="dateEndFilter" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="dateEndFilter">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="applyFilters" class="btn btn-primary">Appliquer</button>
                            <button type="button" id="resetFilters" class="btn btn-secondary ms-2">Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des devis -->
            <div class="card">
                <div class="card-header">
                    <h5>Liste des devis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date de début</th>
                                    <th>Date de fin</th>
                                    <th>Statut</th>
                                    <th>Montant TTC</th>
                                    <th>Montant HT</th>
                                    <th>Montant TVA</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="estimates-table">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement des devis...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>



<!-- Modal pour visualiser/modifier un devis -->
<div class="modal fade" id="viewEstimateModal" tabindex="-1" aria-labelledby="viewEstimateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEstimateModalLabel">Détails du devis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="estimate-details">
                <!-- Le contenu sera injecté dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-warning" id="editEstimate">Modifier</button>
                <button type="button" class="btn btn-success" id="convertToContract">Convertir en contrat</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let societyId = <?php echo $_SESSION['societe_id']; ?>;

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Charger tous les devis
        loadEstimates(societyId);

        // Configuration des événements
        document.getElementById('refreshData').addEventListener('click', function() {
            loadEstimates(societyId);
        });

        document.getElementById('applyFilters').addEventListener('click', function() {
            applyEstimateFilters();
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            resetEstimateFilters();
        });

        // Réinitialiser le modal lorsqu'il est ouvert pour un nouvel ajout
        document.querySelector('[data-bs-target="#addEstimateModal"]').addEventListener('click', function() {
            document.getElementById('addEstimateForm').reset();
            resetEstimateModal();
        });

        // Réinitialiser le modal lorsqu'il est fermé
        document.getElementById('addEstimateModal').addEventListener('hidden.bs.modal', function () {
            resetEstimateModal();
        });

        document.getElementById('saveEstimate').addEventListener('click', function() {
            addNewEstimate();
        });

        // Écouter l'événement de clic sur le bouton d'édition
        document.getElementById('editEstimate').addEventListener('click', function() {
            const estimateId = this.getAttribute('data-id');
            editEstimateDetails(estimateId);
        });

        // Écouter l'événement de clic sur le bouton de conversion en contrat
        document.getElementById('convertToContract').addEventListener('click', function() {
            const estimateId = this.getAttribute('data-id');
            convertEstimateToContract(estimateId);
        });

        // Calcul automatique du montant TVA et TTC
        document.getElementById('montant_ht').addEventListener('input', calculateAmounts);

        function calculateAmounts() {
            const montantHT = parseFloat(document.getElementById('montant_ht').value) || 0;
            const montantTVA = montantHT * 0.2; // 20% TVA
            const montantTTC = (montantHT + montantTVA) * 1.15; // HT + TVA + 15%

            document.getElementById('montant_tva').value = montantTVA.toFixed(2);
            document.getElementById('montant').value = montantTTC.toFixed(2);
        }

        // Simuler la création d'un nouveau devis
        document.getElementById('saveEstimate').addEventListener('click', function() {
            const formData = {
                date_debut: document.getElementById('start_date').value,
                date_fin: document.getElementById('end_date').value,
                montant_ht: parseFloat(document.getElementById('montant_ht').value),
                montant_tva: parseFloat(document.getElementById('montant_tva').value),
                montant: parseFloat(document.getElementById('montant').value),
            };

            console.log('Simulation de création de devis :', formData);
            alert('Simulation réussie : Le devis a été simulé avec succès.');
        });
    });

    // Fonction pour charger les devis
    function loadEstimates(societyId, filters = {}) {
        // Afficher le spinner pendant le chargement
        document.getElementById('estimates-table').innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des devis...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Add a timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Construire l'URL avec les filtres
        let url = `/api/company/getEstimate.php?societe_id=${societyId}&_t=${timestamp}`;
        
        // Ajouter les filtres à l'URL s'ils sont définis
        if (filters.status) {
            url += `&status=${encodeURIComponent(filters.status)}`;
        }
        if (filters.dateStart) {
            url += `&date_debut=${encodeURIComponent(filters.dateStart)}`;
        }
        if (filters.dateEnd) {
            url += `&date_fin=${encodeURIComponent(filters.dateEnd)}`;
        }
        
        // Appel AJAX pour récupérer les devis avec les filtres
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Check for both error formats
                if (data && (data.error === "Estimates not found" || 
                    (data.error === true && data.message === "Estimates not found"))) {
                    document.getElementById('estimates-table').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-info mb-0" role="alert">
                                    Aucun devis pour l'instant
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                if (data && Array.isArray(data)) {
                    if (data.length > 0) {
                        // Si des devis sont trouvés, les afficher
                        let html = '';
                        data.forEach(estimate => {
                            // Débogage pour voir la structure exacte
                            console.log('Structure du devis:', estimate);
                            
                            // Utiliser les propriétés correctes avec vérification
                            const estimateId = estimate.id || estimate.devis_id || estimate.id_devis || '';
                            const status = estimate.status || estimate.statut || 'inconnu';
                            const montantTTC = estimate.montant_ttc || estimate.montant || 0;
                            const montantHT = estimate.montant_ht || 0;
                            const montantTVA = estimate.montant_tva || 0;
                            
                            html += `
                                <tr>
                                    <td>${estimateId}</td>
                                    <td>${estimate.date_debut || ''}</td>
                                    <td>${estimate.date_fin || ''}</td>
                                    <td><span class="badge bg-${getStatusBadgeColor(status)}">${status}</span></td>
                                    <td>${montantTTC} €</td>
                                    <td>${montantHT} €</td>
                                    <td>${montantTVA} €</td>
                                    <td>
                                        ${status === 'envoyé' ? 
                                            `<div class="btn-group">
                                                <button class="btn btn-sm btn-success accept-estimate" data-id="${estimateId}">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                                <button class="btn btn-sm btn-danger refuse-estimate" data-id="${estimateId}">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                             </div>` : 
                                            '<span class="text-muted">Aucune action disponible</span>'
                                        }
                                    </td>
                                </tr>
                            `;
                        });
                        document.getElementById('estimates-table').innerHTML = html;
                        
                        // Ajouter les écouteurs d'événements pour les actions
                        addEstimateEventListeners();
                    } else {
                        // Si aucun devis n'est trouvé, afficher un message simple
                        document.getElementById('estimates-table').innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info mb-0" role="alert">
                                        Aucun devis
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                } else {
                    // En cas d'erreur de format de données (autres erreurs)
                    document.getElementById('estimates-table').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Une erreur est survenue lors du chargement des devis.
                                </div>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des devis:', error);
                document.getElementById('estimates-table').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="alert alert-warning mb-0" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Une erreur est survenue lors du chargement des devis. Veuillez réessayer.
                            </div>
                        </td>
                    </tr>
                `;
            });
    }

    // Fonction pour obtenir la couleur du badge en fonction du statut
    function getStatusBadgeColor(status) {
        switch (status) {
            case 'brouillon': return 'secondary';
            case 'envoyé': return 'primary';
            case 'accepté': return 'success';
            case 'refusé': return 'danger';
            default: return 'info';
        }
    }

    // Fonction pour ajouter les écouteurs d'événements aux boutons d'actions
    function addEstimateEventListeners() {
        // Accept estimate button listeners
        document.querySelectorAll('.accept-estimate').forEach(button => {
            button.addEventListener('click', function() {
                const estimateId = this.getAttribute('data-id');
                acceptEstimate(estimateId);
            });
        });
        
        // Refuse estimate button listeners
        document.querySelectorAll('.refuse-estimate').forEach(button => {
            button.addEventListener('click', function() {
                const estimateId = this.getAttribute('data-id');
                refuseEstimate(estimateId);
            });
        });
    }

    // Fonction pour appliquer les filtres
    function applyEstimateFilters() {
        const filters = {
            status: document.getElementById('statusFilter').value,
            dateStart: document.getElementById('dateStartFilter').value,
            dateEnd: document.getElementById('dateEndFilter').value
        };
        
        console.log('Filtres appliqués:', filters);
        loadEstimates(societyId, filters); // Passer les filtres à loadEstimates
    }

    // Fonction pour réinitialiser les filtres
    function resetEstimateFilters() {
        document.getElementById('estimateFilterForm').reset();
        loadEstimates(societyId); // Appeler sans filtres
    }

    // Function to accept an estimate
    function acceptEstimate(estimateId) {
        if (confirm('Êtes-vous sûr de vouloir accepter ce devis?')) {
            fetch('/api/estimate/modifyState.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    devis_id: estimateId,
                    statut: 'accepté'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Rediriger vers le script qui traitera les opérations post-acceptation
                    window.location.href = `process_accepted_estimate.php?devis_id=${estimateId}&societe_id=${societyId}`;
                } else {
                    alert('Erreur: ' + (data.error || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'acceptation du devis.');
            });
        }
    }

    // Function to refuse an estimate
    function refuseEstimate(estimateId) {
        if (confirm('Êtes-vous sûr de vouloir refuser ce devis?')) {
            fetch('/api/estimate/modifyState.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    devis_id: estimateId,
                    statut: 'refusé'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Le devis a été refusé avec succès!');
                    loadEstimates(societyId); // Reload the estimates list
                } else {
                    alert('Erreur: ' + (data.error || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors du refus du devis.');
            });
        }
    }
</script>