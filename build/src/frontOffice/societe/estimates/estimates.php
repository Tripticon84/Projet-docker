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
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEstimateModal">
                            <i class="fas fa-plus"></i> Nouveau devis
                        </button>
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

<!-- Modal pour ajouter un devis -->
<div class="modal fade" id="addEstimateModal" tabindex="-1" aria-labelledby="addEstimateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEstimateModalLabel">Ajouter un devis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEstimateForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="montant_ht" class="form-label">Montant HT</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="montant_ht" name="montant_ht" required>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="montant_tva" class="form-label">Montant TVA</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="montant_tva" name="montant_tva" readonly>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="montant" class="form-label">Montant TTC</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="montant" name="montant" readonly>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveEstimate">Enregistrer</button>
            </div>
        </div>
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
            applyEstimateFilters(societyId);
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            resetEstimateFilters(societyId);
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
</script>
