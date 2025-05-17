<?php
$title = "Gestion des Contrats";

include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Contrats</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                            <i class="fas fa-sync-alt"></i> Actualiser
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
                    <form id="contractFilterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Statut</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">Tous</option>
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

            <!-- Liste des contrats -->
            <div class="card">
                <div class="card-header">
                    <h5>Liste des contrats</h5>
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
                                    <th>Montant</th>
                                    <th>Montant HT</th>
                                    <th>Montant TVA</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="contracts-table">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement des contrats...</span>
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

<!-- Modal pour visualiser/modifier un contrat -->
<div class="modal fade" id="viewContractModal" tabindex="-1" aria-labelledby="viewContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContractModalLabel">Détails du contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contract-details">
                <!-- Le contenu sera injecté dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let societyId = <?php echo $_SESSION['societe_id']; ?>;

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Charger tous les contrats
        loadContracts(societyId);

        // Configuration des événements
        document.getElementById('refreshData').addEventListener('click', function() {
            loadContracts(societyId);
        });

        document.getElementById('applyFilters').addEventListener('click', function() {
            applyContractFilters(societyId);
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            resetContractFilters(societyId);
        });
    });
</script>
