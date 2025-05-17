<?php
$title = "Gestion des Activités";
include_once "../includes/head.php";

// Placeholder pour les statistiques d'activités
$activityStats = [
    'total' => 0,
    'new' => 0,
    'totalVariation' => 0,
    'newVariation' => 0,
];
// TODO: Implémenter la fonction pour obtenir les statistiques réelles
// $activityStats = getActivitiesStats();
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Activités</h1>
                </div>

                <!-- Status Cards -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h3><?php echo number_format($activityStats['total'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Activités totales</p>
                            <div class="mt-2 <?php echo $activityStats['totalVariation'] >= 0 ? 'text-success' : 'text-danger'; ?> small">
                                <i class="fas fa-arrow-<?php echo $activityStats['totalVariation'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo ($activityStats['totalVariation'] >= 0 ? '+' : '') . $activityStats['totalVariation']; ?>% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h3><?php echo number_format($activityStats['new'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Nouvelles ce mois</p>
                            <div class="mt-2 <?php echo $activityStats['newVariation'] >= 0 ? 'text-success' : 'text-danger'; ?> small">
                                <i class="fas fa-arrow-<?php echo $activityStats['newVariation'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo ($activityStats['newVariation'] >= 0 ? '+' : '') . $activityStats['newVariation']; ?>% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des Activités Actives -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="card-title mb-0">Activités Actives</h5>
                        <div class="d-flex mt-2 mt-sm-0 align-items-center">
                            <div class="input-group me-2 mb-2 mb-sm-0 p-2" style="max-width: 210px;">
                                <input type="text" id="searchActiveInput" class="form-control form-control-sm" placeholder="Rechercher..." aria-label="Search">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="fetchActiveActivities(document.getElementById('searchActiveInput').value)">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <a type="button" class="btn btn-sm btn-primary me-2" href="create.php">
                                <i class="fas fa-plus"></i> Nouvelle Activité
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nom</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Lieu</th>
                                        <th scope="col">Prestataire</th>
                                        <th scope="col">Devis</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="activeActivityList">
                                    <!-- Les activités actives seront insérées ici par JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small" id="activePaginationInfo">Chargement...</span>
                        </div>
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="activePaginationList"></ul>
                        </nav>
                    </div>
                </div>

                <!-- Liste des Activités Inactives -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="card-title mb-0">Activités Désactivées</h5>
                        <div class="d-flex mt-2 mt-sm-0 align-items-center">
                            <div class="input-group me-2 mb-2 mb-sm-0 p-2" style="max-width: 210px;">
                                <input type="text" id="searchInactiveInput" class="form-control form-control-sm" placeholder="Rechercher..." aria-label="Search">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="fetchInactiveActivities(document.getElementById('searchInactiveInput').value)">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nom</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Lieu</th>
                                        <th scope="col">Prestataire</th>
                                        <th scope="col">Devis</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inactiveActivityList">
                                    <!-- Les activités inactives seront insérées ici par JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small" id="inactivePaginationInfo">Chargement...</span>
                        </div>
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="inactivePaginationList"></ul>
                        </nav>
                    </div>
                </div>

                <!-- Quick Action Cards -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Actions rapides</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-plus fa-2x text-primary"></i>
                            </div>
                            <h6>Nouvelle Activité</h6>
                            <a href="create.php" class="btn btn-sm btn-outline-primary mt-2">Ajouter</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour afficher les détails d'une activité -->
    <div class="modal fade" id="activityDetailsModal" tabindex="-1" aria-labelledby="activityDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activityDetailsModalLabel">Détails de l'activité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="activityDetailsContent">
                    <!-- Les détails seront insérés ici par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="editActivityBtn">Modifier</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les détails d'un devis -->
    <div class="modal fade" id="quoteDetailsModal" tabindex="-1" aria-labelledby="quoteDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quoteDetailsModalLabel">Détails du devis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quoteDetailsContent">
                    <!-- Les détails du devis seront insérés ici par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchActiveActivities();
            fetchInactiveActivities();

            // Recherche par l'input de recherche, touche Enter pour activités actives
            document.getElementById('searchActiveInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    fetchActiveActivities(this.value);
                }
            });

            // Recherche par l'input de recherche, touche Enter pour activités inactives
            document.getElementById('searchInactiveInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    fetchInactiveActivities(this.value);
                }
            });
        });

        let activeCurrentPage = 1;
        let inactiveCurrentPage = 1;
        const LIMIT = 5;

        // Fonction pour récupérer les activités actives
        function fetchActiveActivities(search = '', page = 1) {
            activeCurrentPage = page;
            fetchActivities(search, page, 0, 'activeActivityList', 'activePaginationInfo', 'activePaginationList', fetchActiveActivities);
        }

        // Fonction pour récupérer les activités inactives
        function fetchInactiveActivities(search = '', page = 1) {
            inactiveCurrentPage = page;
            fetchActivities(search, page, 1, 'inactiveActivityList', 'inactivePaginationInfo', 'inactivePaginationList', fetchInactiveActivities);
        }

        // Fonction générique pour récupérer les activités
        function fetchActivities(search = '', page = 1, desactivate = 0, listId, paginationInfoId, paginationListId, fetchFunction) {
            const activityList = document.getElementById(listId);
            activityList.innerHTML = '<tr><td colspan="8" class="text-center">Chargement des activités...</td></tr>';

            let offset = (page - 1) * LIMIT;
            let url = '../../api/activity/getAll.php?limit=' + LIMIT + '&offset=' + offset + '&desactivate=' + desactivate;

            if (search && search.trim() !== '') {
                // Assurez-vous que la recherche est correctement encodée
                url += '&search=' + encodeURIComponent(search.trim());
                console.log('URL de recherche: ' + url); // Log pour débogage
            }

            fetch(url, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la récupération des activités');
                    }
                    return response.json();
                })
                .then(data => {
                    activityList.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(activity => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${activity.id}</td>
                                <td>${activity.nom || '-'}</td>
                                <td>${activity.type || '-'}</td>
                                <td>${formatDate(activity.date)}</td>
                                <td>${activity.id_lieu || '-'}</td>
                                <td>${activity.id_prestataire || '-'}</td>
                                <td>${activity.id_devis ? `<a href="#" onclick="viewQuote(${activity.id_devis}); return false;">Devis #${activity.id_devis}</a>` : '-'}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="viewActivityDetails(${activity.id}); return false;"><i class="fas fa-eye me-2"></i>Voir détails</a></li>
                                            <li><a class="dropdown-item" href="modify.php?id=${activity.id}"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-${activity.desactivate ? 'success' : 'danger'}" href="#" onclick="toggleActivityStatus(${activity.id}, ${activity.desactivate ? 'false' : 'true'}); return false;"><i class="fas fa-${activity.desactivate ? 'check-circle' : 'ban'} me-2"></i>${activity.desactivate ? 'Activer' : 'Désactiver'}</a></li>
                                        </ul>
                                    </div>
                                </td>
                            `;
                            activityList.appendChild(row);
                        });
                        document.getElementById(paginationInfoId).textContent = `Affichage de ${offset + 1}-${offset + data.length} activités`;
                        updatePagination(data.length === LIMIT, search, page, paginationListId, fetchFunction);
                    } else {
                        activityList.innerHTML = '<tr><td colspan="8" class="text-center">Liste vide</td></tr>';
                        document.getElementById(paginationInfoId).textContent = 'Aucun élément à afficher';
                        document.getElementById(paginationListId).innerHTML = '';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    activityList.innerHTML = '<tr><td colspan="8" class="text-center">Liste vide</td></tr>';
                    document.getElementById(paginationInfoId).textContent = '';
                });
        }

        function updatePagination(hasMore, search = '', currentPage, paginationListId, fetchFunction) {
            const paginationList = document.getElementById(paginationListId);
            paginationList.innerHTML = '';

            // Bouton Précédent
            let prevItem = document.createElement('li');
            prevItem.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
            prevItem.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); ${fetchFunction.name}('${search}', ${currentPage - 1});">Précédent</a>`;
            paginationList.appendChild(prevItem);

            // Bouton Suivant
            let nextItem = document.createElement('li');
            nextItem.className = 'page-item ' + (!hasMore ? 'disabled' : '');
            nextItem.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); ${fetchFunction.name}('${search}', ${currentPage + 1});">Suivant</a>`;
            paginationList.appendChild(nextItem);
        }

        function toggleActivityStatus(activityId, desactivate) {
            const action = desactivate ? 'désactiver' : 'activer';
            if (confirm(`Êtes-vous sûr de vouloir ${action} cette activité?`)) {
                // Choose the appropriate endpoint and method based on the action
                const endpoint = desactivate 
                    ? '../../api/activity/delete.php' 
                    : '../../api/activity/activate.php';
                
                // Use POST instead of PUT for the 'update' operation
                const method = 'PATCH';
                
                fetch(endpoint, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify({
                        activite_id: activityId,
                        // Only include desactivate parameter for deactivation
                        ...(desactivate ? { desactivate: desactivate } : {})
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur de réponse du serveur');
                        }
                        return response.json();
                    })
                    .then(data => {
                        alert(`Activité ${action}e avec succès.`);
                        
                        // Rafraîchir les deux listes pour s'assurer que les données sont à jour
                        fetchActiveActivities(document.getElementById('searchActiveInput').value, activeCurrentPage);
                        fetchInactiveActivities(document.getElementById('searchInactiveInput').value, inactiveCurrentPage);
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la mise à jour du statut: ' + error.message);
                    });
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        }

        function viewActivityDetails(activityId) {
            const detailsContent = document.getElementById('activityDetailsContent');
            detailsContent.innerHTML = '<p class="text-center">Chargement des détails...</p>';

            const modal = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
            modal.show();

            fetch(`../../api/activity/getOne.php?activite_id=${activityId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la récupération des détails');
                    }
                    return response.json();
                })
                .then(activity => {
                    if (activity) {
                        detailsContent.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> ${activity.id}</p>
                                    <p><strong>Nom:</strong> ${activity.nom || '-'}</p>
                                    <p><strong>Type:</strong> ${activity.type || '-'}</p>
                                    <p><strong>Date:</strong> ${formatDate(activity.date)}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Lieu:</strong> ${activity.id_lieu || '-'}</p>
                                    <p><strong>Devis:</strong> ${activity.id_devis ? `<a href="#" onclick="viewQuote(${activity.id_devis}); return false;">Voir Devis #${activity.id_devis}</a>` : 'Aucun'}</p>
                                    <p><strong>Prestataire:</strong> ${activity.id_prestataire || 'Non assigné'}</p>
                                    <p><strong>Statut:</strong> ${activity.desactivate ? '<span class="badge bg-danger">Désactivé</span>' : '<span class="badge bg-success">Actif</span>'}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Informations complémentaires</h6>
                                    <p>Pour ajouter des informations complémentaires, vous pouvez modifier les détails de l'activité.</p>
                                </div>
                            </div>
                        `;

                        // Mettre à jour le bouton d'édition avec l'ID de l'activité
                        document.getElementById('editActivityBtn').onclick = function() {
                            window.location.href = `modify.php?id=${activity.id}`;
                        };
                    } else {
                        detailsContent.innerHTML = '<p class="text-center">Aucune information disponible</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    detailsContent.innerHTML = '<p class="text-center">Aucune information disponible</p>';
                });
        }

        function viewQuote(quoteId) {
            // Ouvrir un modal avec les détails du devis
            const detailsContent = document.getElementById('quoteDetailsContent');
            detailsContent.innerHTML = '<p class="text-center">Chargement des détails du devis...</p>';

            const modal = new bootstrap.Modal(document.getElementById('quoteDetailsModal'));
            modal.show();

            // Utiliser le bon endpoint pour récupérer les données du devis
            fetch(`../../api/estimate/getOne.php?devis_id=${quoteId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la récupération du devis');
                    }
                    return response.json();
                })
                .then(quote => {
                    if (quote) {
                        detailsContent.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> ${quote.devis_id || quote.id}</p>
                                    <p><strong>Date de début:</strong> ${formatDate(quote.date_debut)}</p>
                                    <p><strong>Date de fin:</strong> ${formatDate(quote.date_fin)}</p>
                                    <p><strong>Type:</strong> ${quote.is_contract ? '<span class="badge bg-info">Contrat</span>' : '<span class="badge bg-primary">Devis</span>'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Montant TTC:</strong> ${quote.montant ? quote.montant + ' €' : '-'}</p>
                                    <p><strong>Montant HT:</strong> ${quote.montant_ht ? quote.montant_ht + ' €' : '-'}</p>
                                    <p><strong>TVA:</strong> ${quote.montant_tva ? quote.montant_tva + ' €' : '-'}</p>
                                    <p><strong>Statut:</strong> ${getQuoteStatusBadge(quote.statut)}</p>
                                </div>
                            </div>
                            ${quote.fichier ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Fichier</h6>
                                    <p><a href="${quote.fichier}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf me-2"></i>Consulter le document</a></p>
                                </div>
                            </div>` : ''}
                        `;
                    } else {
                        detailsContent.innerHTML = '<p class="text-center">Aucune information disponible</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    detailsContent.innerHTML = '<p class="text-center">Aucune information disponible</p>';
                });
        }

        function getQuoteStatusBadge(status) {
            if (!status) return '<span class="badge bg-secondary">Inconnu</span>';
            
            switch(status.toLowerCase()) {
                case 'en attente':
                    return '<span class="badge bg-warning">En attente</span>';
                case 'accepté':
                    return '<span class="badge bg-success">Accepté</span>';
                case 'refusé':
                    return '<span class="badge bg-danger">Refusé</span>';
                default:
                    return `<span class="badge bg-secondary">${status}</span>`;
            }
        }
    </script>

    <style>
        .stat-card {
            position: relative;
            padding: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            margin-top: 0.5rem;
        }

        .table tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</body>

</html>
