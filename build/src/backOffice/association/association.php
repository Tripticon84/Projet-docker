<?php
$title = "Gestion des Associations";
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
                    <h1 class="h2">Gestion des Associations</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Ajouter une association
                        </a>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <p class="text-muted mb-0">Associations enregistrées</p>
                            <h3 id="associationsCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="text-muted mb-0">Associations actives</p>
                            <h3 id="activeAssociationsCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <p class="text-muted mb-0">Évènements associés</p>
                            <h3 id="eventsCount">-</h3>
                        </div>
                    </div>
                </div>

                <!-- Associations Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Liste des Associations</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshAssociations()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nom</th>
                                        <th scope="col">Description</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="associationsList">
                                    <tr>
                                        <td colspan="4" class="text-center">Chargement des associations...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                        </nav>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour afficher les détails d'une association -->
    <div class="modal fade" id="associationDetailModal" tabindex="-1" aria-labelledby="associationDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="associationDetailModalLabel">Détails de l'association</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <span id="modal-association-id"></span></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal-association-name" class="form-label">Nom:</label>
                        <input type="text" class="form-control" id="modal-association-name">
                    </div>
                    <div class="mb-3">
                        <label for="modal-association-description" class="form-label">Description:</label>
                        <textarea class="form-control" id="modal-association-description" rows="4"></textarea>
                    </div>

                    <!-- Nouvelle section pour les employés de l'association -->
                    <div class="mt-4">
                        <h5>Employés de l'association</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                    </tr>
                                </thead>
                                <tbody id="employeesList">
                                    <tr>
                                        <td colspan="5" class="text-center">Chargement des employés...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="saveChangesBtn">Enregistrer les modifications</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentModal = null;

        document.addEventListener('DOMContentLoaded', function() {
            fetchAssociations();

            // Configurer le bouton de sauvegarde des modifications
            document.getElementById('saveChangesBtn').addEventListener('click', function() {
                saveAssociation();
            });

            // Configurer le bouton de suppression
            document.getElementById('deleteAssociationBtn').addEventListener('click', function() {
                deleteAssociation();
            });
        });

        function fetchAssociations(page = 1) {
            currentPage = page;
            const associationsList = document.getElementById('associationsList');
            associationsList.innerHTML = '<tr><td colspan="4" class="text-center">Chargement des associations...</td></tr>';

            // Récupérer toutes les associations
            fetch('../../api/association/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                associationsList.innerHTML = '';

                // Mettre à jour les compteurs
                document.getElementById('associationsCount').textContent = data.length;
                document.getElementById('activeAssociationsCount').textContent = data.length; // À adapter si nécessaire
                document.getElementById('eventsCount').textContent = '-'; // À implémenter si nécessaire

                if (data.length > 0) {
                    data.forEach(association => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${association.id}</td>
                            <td>${association.name}</td>
                            <td>${truncateText(association.description, 50)}</td>
                            <td class="text-end">
                                <a href="edit.php?id=${association.id}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="profil.php?id=${association.id}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </td>
                        `;
                        associationsList.appendChild(row);
                    });
                } else {
                    associationsList.innerHTML = '<tr><td colspan="4" class="text-center">Aucune association trouvée</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                associationsList.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Erreur lors du chargement des associations</td></tr>';
            });
        }

        function viewAssociationDetails(associationId) {
            // Récupérer les détails de l'association
            fetch(`../../api/association/getAll.php`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(associations => {
                const association = associations.find(a => a.id == associationId);

                if (association) {
                    document.getElementById('modal-association-id').textContent = association.id;
                    document.getElementById('modal-association-name').value = association.name;
                    document.getElementById('modal-association-description').value = association.description;

                    // Charger les employés de l'association
                    fetchEmployeesByAssociation(associationId);

                    currentModal = new bootstrap.Modal(document.getElementById('associationDetailModal'));
                    currentModal.show();
                } else {
                    alert('Association non trouvée');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des détails de l\'association');
            });
        }

        function fetchEmployeesByAssociation(associationId) {
            const employeesList = document.getElementById('employeesList');
            employeesList.innerHTML = '<tr><td colspan="5" class="text-center">Chargement des employés...</td></tr>';

            fetch(`../../api/association/getEmplyeesByAssociation.php?association_id=${associationId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                employeesList.innerHTML = '';

                if (data.employees && data.employees.length > 0) {
                    data.employees.forEach(employee => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${employee.collaborateur_id}</td>
                            <td>${employee.nom}</td>
                            <td>${employee.prenom}</td>
                            <td>${employee.email}</td>
                            <td>${employee.role}</td>
                        `;
                        employeesList.appendChild(row);
                    });
                } else {
                    employeesList.innerHTML = '<tr><td colspan="5" class="text-center">Aucun employé trouvé pour cette association</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                employeesList.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur lors du chargement des employés</td></tr>';
            });
        }

        function saveAssociation() {
            const associationId = document.getElementById('modal-association-id').textContent;
            const associationName = document.getElementById('modal-association-name').value;
            const associationDescription = document.getElementById('modal-association-description').value;

            if (!associationName) {
                alert('Le nom de l\'association est obligatoire');
                return;
            }

            fetch('../../api/association/update.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify({
                    association_id: associationId,
                    name: associationName,
                    description: associationDescription
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.association_id) {
                    alert('Association mise à jour avec succès');
                    currentModal.hide();
                    fetchAssociations(currentPage);
                } else {
                    alert('Erreur lors de la mise à jour de l\'association');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour de l\'association');
            });
        }

        function refreshAssociations() {
            fetchAssociations(currentPage);
        }

        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
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
