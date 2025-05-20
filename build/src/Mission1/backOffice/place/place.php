<?php
$title = "Gestion des lieux";
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
                    <h1 class="h2">Gestion des lieux</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Ajouter un lieu
                        </a>
                    </div>
                </div>

                <!-- Search bar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un lieu...">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Places list -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Liste des lieux</h5>
                        <span id="totalPlaces" class="badge bg-primary">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Adresse</th>
                                        <th>Ville</th>
                                        <th>Code Postal</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="placesList">
                                    <!-- Places will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0" id="pagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentPage = 1;
            const itemsPerPage = 10;
            let searchTerm = '';

            // Load places on page load
            loadPlaces(currentPage, itemsPerPage, searchTerm);

            // Search functionality
            document.getElementById('searchButton').addEventListener('click', function() {
                searchTerm = document.getElementById('searchInput').value.trim();
                currentPage = 1;
                loadPlaces(currentPage, itemsPerPage, searchTerm);
            });

            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchTerm = document.getElementById('searchInput').value.trim();
                    currentPage = 1;
                    loadPlaces(currentPage, itemsPerPage, searchTerm);
                }
            });

            // Function to load places
            function loadPlaces(page, limit, search = '') {
                const offset = (page - 1) * limit;
                let url = `../../api/place/getAll.php?limit=${limit}&offset=${offset}`;

                if (search) {
                    url += `&adresse=${encodeURIComponent(search)}`;
                }

                fetch(url, {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Vérifier la structure de la réponse
                    const places = data.places || data.data || data || [];
                    const total = data.total || places.length || 0;

                    displayPlaces(places);
                    updatePagination(total, page, limit);
                    document.getElementById('totalPlaces').textContent = total;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('placesList').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="alert alert-danger mb-0">
                                    Erreur lors du chargement des lieux. Veuillez réessayer.
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }

            // Function to display places in table
            function displayPlaces(places) {
                const tableBody = document.getElementById('placesList');

                if (!places || places.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    Aucun lieu trouvé
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let html = '';
                places.forEach(place => {
                    html += `
                        <tr>
                            <td>${place.lieu_id}</td>
                            <td>${place.adresse || '-'}</td>
                            <td>${place.ville || '-'}</td>
                            <td>${place.code_postal || '-'}</td>
                            <td class="text-end">
                                <a href="modify.php?id=${place.lieu_id}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });

                tableBody.innerHTML = html;
            }

            // Function to update pagination
            function updatePagination(total, currentPage, limit) {
                const pagination = document.getElementById('pagination');
                const totalPages = Math.ceil(total / limit);

                let html = '';

                // Previous button
                html += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                `;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    html += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                // Next button
                html += `
                    <li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                `;

                pagination.innerHTML = html;

                // Add event listeners to pagination links
                document.querySelectorAll('#pagination .page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (!this.parentElement.classList.contains('disabled')) {
                            const page = parseInt(this.getAttribute('data-page'));
                            loadPlaces(page, itemsPerPage, searchTerm);
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
