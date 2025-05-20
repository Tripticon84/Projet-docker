<?php
$title = "Frais et Abonnements";

include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Frais et Abonnements</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Abonnements -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vos Abonnements</h5>
                    <span class="badge bg-light text-primary" id="total-subscriptions">0</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Montant</th>
                                    <th>Description</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="subscriptions-table">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement des abonnements...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Autres frais -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vos Autres Frais</h5>
                    <span class="badge bg-light text-info" id="total-costs">0</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Montant</th>
                                    <th>Description</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="costs-table">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-info" role="status">
                                            <span class="visually-hidden">Chargement des frais...</span>
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

<!-- Modal pour visualiser un frais ou abonnement -->
<div class="modal fade" id="viewCostModal" tabindex="-1" aria-labelledby="viewCostModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCostModalLabel">Détails</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cost-details">
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

    // Fonction pour charger toutes les données
    function loadAllData() {
        loadSubscriptions(societyId);
        loadOtherCosts(societyId);
    }

    // Fonction pour charger les abonnements
    function loadSubscriptions(societyId) {
        document.getElementById('subscriptions-table').innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des abonnements...</span>
                    </div>
                </td>
            </tr>
        `;
        
        fetch(`/api/company/getSubscriptions.php?societe_id=${societyId}`)
            .then(response => response.json())
            .then(data => {
                if (!data || data.error || data.length === 0) {
                    document.getElementById('subscriptions-table').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">Aucun abonnement trouvé</td>
                        </tr>
                    `;
                    document.getElementById('total-subscriptions').textContent = '0';
                    return;
                }
                
                let html = '';
                data.forEach(subscription => {
                    html += `
                        <tr>
                            <td>${subscription.nom}</td>
                            <td>${parseFloat(subscription.montant).toFixed(2)}€</td>
                            <td>${subscription.description || 'Non spécifié'}</td>
                            <td>${subscription.date_creation}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-cost" data-id="${subscription.frais_id}" data-bs-toggle="modal" data-bs-target="#viewCostModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                document.getElementById('subscriptions-table').innerHTML = html;
                document.getElementById('total-subscriptions').textContent = data.length;
                
                // Ajouter les écouteurs d'événements
                document.querySelectorAll('.view-cost').forEach(button => {
                    button.addEventListener('click', function() {
                        const costId = this.getAttribute('data-id');
                        showCostDetails(costId);
                    });
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des abonnements:', error);
                document.getElementById('subscriptions-table').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Erreur lors du chargement des abonnements</td>
                    </tr>
                `;
                document.getElementById('total-subscriptions').textContent = '0';
            });
    }

    // Fonction pour charger les autres frais (non-abonnements)
    function loadOtherCosts(societyId) {
        document.getElementById('costs-table').innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Chargement des frais...</span>
                    </div>
                </td>
            </tr>
        `;
        
        fetch(`/api/company/getFees.php?societe_id=${societyId}`)
            .then(response => response.json())
            .then(data => {
                // Filtrer pour garder tous les frais qui ne sont PAS des abonnements (est_abonnement != 1)
                const otherCosts = data.filter(fee => fee.est_abonnement !== 1 && fee.est_abonnement !== '1');
                
                if (!otherCosts || otherCosts.length === 0) {
                    document.getElementById('costs-table').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">Aucun frais trouvé</td>
                        </tr>
                    `;
                    document.getElementById('total-costs').textContent = '0';
                    return;
                }
                
                let html = '';
                otherCosts.forEach(cost => {
                    html += `
                        <tr>
                            <td>${cost.nom}</td>
                            <td>${parseFloat(cost.montant).toFixed(2)}€</td>
                            <td>${cost.description || 'Non spécifié'}</td>
                            <td>${cost.date_creation}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-cost" data-id="${cost.frais_id}" data-bs-toggle="modal" data-bs-target="#viewCostModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                document.getElementById('costs-table').innerHTML = html;
                document.getElementById('total-costs').textContent = otherCosts.length;
                
                // Ajouter les écouteurs d'événements
                document.querySelectorAll('.view-cost').forEach(button => {
                    button.addEventListener('click', function() {
                        const costId = this.getAttribute('data-id');
                        showCostDetails(costId);
                    });
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des frais:', error);
                document.getElementById('costs-table').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Erreur lors du chargement des frais</td>
                    </tr>
                `;
                document.getElementById('total-costs').textContent = '0';
            });
    }

    // Fonction pour afficher les détails d'un frais
    function showCostDetails(costId) {
        // D'abord essayer de récupérer tous les frais
        fetch(`/api/company/getFees.php?societe_id=${societyId}`)
            .then(response => response.json())
            .then(data => {
                // Trouver le frais spécifique par son ID
                const cost = data.find(item => item.frais_id == costId);
                
                if (!cost) {
                    document.getElementById('cost-details').innerHTML = `<p>Aucune information disponible</p>`;
                    return;
                }
                
                const detailsHTML = `
                    <div class="mb-3">
                        <strong>Nom:</strong> ${cost.nom}
                    </div>
                    <div class="mb-3">
                        <strong>Montant:</strong> ${parseFloat(cost.montant).toFixed(2)}€
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong> ${cost.description || 'Non spécifié'}
                    </div>
                    <div class="mb-3">
                        <strong>Date de création:</strong> ${cost.date_creation}
                    </div>
                    <div class="mb-3">
                        <strong>Type:</strong> ${cost.est_abonnement == 1 ? 'Abonnement' : 'Frais ponctuel'}
                    </div>
                    ${cost.devis && cost.devis.devis_id ? 
                        `<div class="mb-3">
                            <strong>Lié au devis:</strong> #${cost.devis.devis_id}
                            ${cost.devis.date_debut ? `<br><strong>Période:</strong> Du ${cost.devis.date_debut} au ${cost.devis.date_fin}` : ''}
                            ${cost.devis.statut ? `<br><strong>Statut:</strong> ${cost.devis.statut}` : ''}
                        </div>` : ''}
                `;
                
                document.getElementById('cost-details').innerHTML = detailsHTML;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails:', error);
                document.getElementById('cost-details').innerHTML = `<p>Erreur lors du chargement des détails</p>`;
            });
    }

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Charger les données
        loadAllData();

        // Configuration des événements
        document.getElementById('refreshData').addEventListener('click', function() {
            loadAllData();
        });
    });
</script>
