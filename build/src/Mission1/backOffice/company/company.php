<?php
$title = "Gestion des Sociétés";
include_once "../includes/head.php";
include_once "../../api/dao/company.php";

// Exemple de stats
$companyStats = [
    'total' => 0,
    'new' => 0,
    'totalVariation' => 0,
    'newVariation' => 0,
];
$companyStats = getCompaniesStats();

?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php" ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Sociétés</h1>
                </div>

                <!-- Status Cards (inspiré de employee.php) -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3><?php echo number_format($companyStats['total'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Sociétés inscrites</p>
                            <div class="mt-2 <?php echo $companyStats['totalVariation'] >= 0 ? 'text-success' : 'text-danger'; ?> small">
                                <i class="fas fa-arrow-<?php echo $companyStats['totalVariation'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo ($companyStats['totalVariation'] >= 0 ? '+' : '') . $companyStats['totalVariation']; ?>% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h3><?php echo number_format($companyStats['new'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Nouvelles ce mois</p>
                            <div class="mt-2 <?php echo $companyStats['newVariation'] >= 0 ? 'text-success' : 'text-danger'; ?> small">
                                <i class="fas fa-arrow-<?php echo $companyStats['newVariation'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo ($companyStats['newVariation'] >= 0 ? '+' : '') . $companyStats['newVariation']; ?>% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des Sociétés -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="card-title mb-0">Liste des Sociétés</h5>
                        <div class="d-flex mt-2 mt-sm-0 align-items-center">
                            <div class="input-group me-2 mb-2 mb-sm-0 p-2" style="max-width: 210px;">
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Rechercher une société..." aria-label="Search">
                                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="fetchCompanies(document.getElementById('searchInput').value)">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <a type="button" class="btn btn-sm btn-primary me-2" href="create.php">
                                <i class="fas fa-plus"></i> Nouvelle Société
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
                                        <th scope="col">Email</th>
                                        <th scope="col">Adresse</th>
                                        <th scope="col">Contact</th>
                                        <th scope="col">Téléphone</th>
                                        <th scope="col">Date création</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="companyList">
                                    <!-- Les sociétés seront insérées ici par JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small" id="paginationInfo">Chargement...</span>
                        </div>
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
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
                            <h6>Nouvelle Société</h6>
                            <a href="create.php" class="btn btn-sm btn-outline-primary mt-2">Ajouter</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Script pour charger la liste des sociétés
        document.addEventListener('DOMContentLoaded', function() {
            fetchCompanies();

            // Recherche par l'input de recherche, touche Enter
            document.getElementById('searchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    fetchCompanies(this.value);
                }
            });
        });


        let currentPage = 1;

        function fetchCompanies(name = '', page = 1) {
            currentPage = page;
            const companyList = document.getElementById('companyList');
            companyList.innerHTML = '<tr><td colspan="8" class="text-center">Chargement des sociétés...</td></tr>';

            let limit = 5;
            let offset = (page - 1) * limit;
            let url = '../../api/company/getAll.php?limit=' + limit + '&offset=' + offset;
            if (name) {
                url += '&name=' + encodeURIComponent(name);
            }

            fetch(url, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors de la récupération des sociétés');
                    }
                    return response.json();
                })
                .then(data => {
                    companyList.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(company => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${company.societe_id}</td>
                                <td>${company.nom || '-'}</td>
                                <td>${company.email || '-'}</td>
                                <td>${company.adresse || '-'}</td>
                                <td>${company.contact_person || '-'}</td>
                                <td>${company.telephone || '-'}</td>
                                <td>${new Date(company.date_creation).toLocaleString('fr-FR') || '-'}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="profil.php?id=${company.societe_id}"><i class="fas fa-eye me-2"></i>Voir profil</a></li>
                                            <li><a class="dropdown-item" href="modify.php?id=${company.societe_id}"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                            <!-- <li><a class="dropdown-item text-danger" href="#" onclick="deleteCompany(${company.societe_id}); return false;"><i class="fas fa-user-slash me-2"></i>Supprimer</a></li> -->
                                        </ul>
                                    </div>
                                </td>
                            `;
                            companyList.appendChild(row);
                        });
                        document.getElementById('paginationInfo').textContent = `Affichage de 1-${data.length} sociétés`;
                        updatePagination(data.length === limit);
                    } else {
                        companyList.innerHTML = '<tr><td colspan="8" class="text-center">Aucune société trouvée</td></tr>';
                        document.getElementById('paginationInfo').textContent = 'Aucune société trouvée';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    companyList.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des données</td></tr>';
                    document.getElementById('paginationInfo').textContent = 'Erreur lors du chargement des données';
                });
        }

        function updatePagination(hasMore) {
            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';

            // Bouton Précédent
            let prevItem = document.createElement('li');
            prevItem.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
            prevItem.innerHTML = '<a class="page-link" href="#" onclick="fetchCompanies(document.getElementById(\'searchInput\').value, ' + (currentPage - 1) + ')">Précédent</a>';
            paginationList.appendChild(prevItem);

            // Bouton Suivant
            let nextItem = document.createElement('li');
            nextItem.className = 'page-item ' + (!hasMore ? 'disabled' : '');
            nextItem.innerHTML = '<a class="page-link" href="#" onclick="fetchCompanies(document.getElementById(\'searchInput\').value, ' + (currentPage + 1) + ')">Suivant</a>';
            paginationList.appendChild(nextItem);
        }

        // Exemple pour supprimer une société (si un endpoint delete est créé)
        function deleteCompany(companyId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette société?')) {
                fetch('../../api/company/delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify({ societe_id: companyId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Société supprimée avec succès.');
                        fetchCompanies(); // Rafraîchir la liste
                    } else {
                        alert('Erreur lors de la désactivation/suppression.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la suppression.');
                });
            }
        }
    </script>
</body>
</html>
