<?php
$title = "Gestion des Collaborateurs";

include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Collaborateurs</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addEmployeeBtn">
                            <i class="fas fa-plus"></i> Nouveau collaborateur
                        </button>
                    </div>
                </div>
            </div>

            <!-- Affichage de la limite d'employés -->
            <div class="alert alert-info mb-4" id="employee-limit-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-users me-2"></i>
                        <span id="employee-count-message">Chargement des informations sur votre abonnement...</span>
                    </div>
                    <div>
                        <a href="/frontOffice/societe/estimates/new_estimate.php" class="btn btn-sm btn-primary" id="upgrade-subscription-btn" style="display: none;">
                            <i class="fas fa-arrow-up"></i> Augmenter votre limite
                        </a>
                    </div>
                </div>
            </div>

            <!-- Onglets pour afficher les employés actifs ou désactivés -->
            <ul class="nav nav-tabs mb-4" id="employeeStatusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true">
                        <i class="fas fa-user-check"></i> Collaborateurs actifs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive" type="button" role="tab" aria-controls="inactive" aria-selected="false">
                        <i class="fas fa-user-slash"></i> Collaborateurs désactivés
                    </button>
                </li>
            </ul>

            <!-- Filtres de recherche -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filtres</h5>
                </div>
                <div class="card-body">
                    <form id="employeeFilterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="nameFilter" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nameFilter" placeholder="Rechercher par nom">
                        </div>
                        <div class="col-md-3">
                            <label for="roleFilter" class="form-label">Rôle</label>
                            <select id="roleFilter" class="form-select">
                                <option value="">Tous</option>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="responsable_rh">Responsable RH</option>
                                <option value="directeur">Directeur</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateFilter" class="form-label">Date d'ajout</label>
                            <input type="date" class="form-control" id="dateFilter">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="applyFilters" class="btn btn-primary">Appliquer</button>
                            <button type="button" id="resetFilters" class="btn btn-secondary ms-2">Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des collaborateurs -->
            <div class="card">
                <div class="card-header">
                    <h5 id="employeeListTitle">Liste des collaborateurs actifs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Date d'ajout</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employees-table">
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement des collaborateurs...</span>
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

<!-- Modal pour ajouter un collaborateur -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Ajouter un collaborateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="responsable_rh">Responsable RH</option>
                                <option value="directeur">Directeur</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="form-text">Le mot de passe doit comporter au moins 8 caractères.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveEmployee">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier un collaborateur -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Modifier un collaborateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <input type="hidden" id="edit_employee_id" name="id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="edit_nom" name="nom" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="edit_prenom" name="prenom" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_role" class="form-label">Rôle</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="employe">Employé</option>
                                <option value="manager">Manager</option>
                                <option value="responsable_rh">Responsable RH</option>
                                <option value="directeur">Directeur</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="edit_telephone" name="telephone" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Nouveau mot de passe (optionnel)</label>
                        <input type="password" class="form-control" id="edit_password" name="password" minlength="8">
                        <div class="form-text">Laissez vide pour conserver le mot de passe actuel.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="updateEmployee">Enregistrer les modifications</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les détails d'un collaborateur -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEmployeeModalLabel">Détails du collaborateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="employee-details">
                <!-- Le contenu sera injecté dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-warning" id="editEmployee">Modifier</button>
                <button type="button" class="btn btn-danger" id="deactivateEmployee">Désactiver</button>
                <button type="button" class="btn btn-success" id="reactivateEmployee" style="display: none;">Réactiver</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let societyId = <?php echo $_SESSION['societe_id']; ?>;
    let currentEmployeeStatus = 'active'; // Pour suivre quel type d'employés est affiché
    let employeeLimitInfo = null; // Pour stocker les informations sur la limite d'employés

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Intercepter toutes les erreurs globales et les transformer en messages bleus
        window.addEventListener('error', function(event) {
            event.preventDefault();
            console.error('Erreur capturée:', event.error || event.message);
            showBlueMessage(currentEmployeeStatus === 'active' ? 'actif' : 'désactivé');
            return false;
        });

        // Charger les informations sur la limite d'employés
        checkEmployeeLimit();

        // Remplacer l'événement direct par une vérification de la limite
        document.getElementById('addEmployeeBtn').addEventListener('click', function() {
            if (employeeLimitInfo && employeeLimitInfo.status) {
                // Si la société peut ajouter un employé, ouvrir le modal
                const modal = new bootstrap.Modal(document.getElementById('addEmployeeModal'));
                modal.show();
            } else if (employeeLimitInfo) {
                // Sinon, afficher un message d'erreur
                alert(employeeLimitInfo.message);
                // Mettre en évidence le bouton de mise à niveau
                document.getElementById('upgrade-subscription-btn').classList.add('btn-animate');
                setTimeout(() => {
                    document.getElementById('upgrade-subscription-btn').classList.remove('btn-animate');
                }, 2000);
            } else {
                // Si les informations ne sont pas encore chargées
                alert("Chargement des informations sur votre limite d'employés. Veuillez réessayer dans un instant.");
            }
        });

        // Charger les collaborateurs actifs par défaut
        loadActiveEmployees(societyId);

        // Configuration des événements
        document.getElementById('refreshData').addEventListener('click', function() {
            refreshEmployeeList();
        });

        document.getElementById('applyFilters').addEventListener('click', function() {
            applyEmployeeFilters();
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('employeeFilterForm').reset();
            refreshEmployeeList();
        });

        document.getElementById('saveEmployee').addEventListener('click', function() {
            addEmployee(societyId);
        });

        document.getElementById('updateEmployee').addEventListener('click', function() {
            updateEmployee();
        });

        // Événement pour le bouton de désactivation
        document.getElementById('deactivateEmployee').addEventListener('click', function() {
            const employeeId = this.getAttribute('data-id');
            deactivateEmployee(employeeId);
        });

        // Événement pour le bouton de réactivation
        document.getElementById('reactivateEmployee').addEventListener('click', function() {
            const employeeId = this.getAttribute('data-id');
            reactivateEmployee(employeeId);
        });

        // Événement pour le bouton d'édition dans la vue détails
        document.getElementById('editEmployee').addEventListener('click', function() {
            const employeeId = this.getAttribute('data-id');
            const employee = this.getAttribute('data-employee');
            if (employee) {
                const employeeData = JSON.parse(employee);
                openEditModal(employeeData);
            } else {
                // Si les données ne sont pas disponibles, charger depuis l'API
                fetchEmployeeDetails(employeeId);
            }
        });

        // Configurer les événements pour générer automatiquement le nom d'utilisateur
        document.getElementById('nom').addEventListener('input', updateUsername);
        document.getElementById('prenom').addEventListener('input', updateUsername);

        // Événements pour les onglets
        document.getElementById('active-tab').addEventListener('click', function() {
            currentEmployeeStatus = 'active';
            document.getElementById('employeeListTitle').textContent = 'Liste des collaborateurs actifs';
            loadActiveEmployees(societyId);
        });

        document.getElementById('inactive-tab').addEventListener('click', function() {
            currentEmployeeStatus = 'inactive';
            document.getElementById('employeeListTitle').textContent = 'Liste des collaborateurs désactivés';
            loadInactiveEmployees(societyId);
        });
    });

    // Fonction utilitaire pour afficher le message bleu
    function showBlueMessage(type) {
        document.getElementById('employees-table').innerHTML = `
            <tr>
                <td colspan="9" class="text-center">
                    <div class="alert alert-info mb-0" role="alert">
                        Aucun collaborateur ${type}
                    </div>
                </td>
            </tr>
        `;
    }

    // Fonction pour charger les collaborateurs actifs
    function loadActiveEmployees(societyId) {
        // Afficher le spinner pendant le chargement
        document.getElementById('employees-table').innerHTML = `
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des collaborateurs...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Add a timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Corrected API endpoint - change getEmployees.php to getAllEmployee.php
        fetch(`/api/company/getAllEmployee.php?societe_id=${societyId}&desactivate=0&_t=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                // Vérification spécifique pour l'erreur "No employees found"
                if (data && data.error === "No employees found") {
                    showBlueMessage('actif');
                    return;
                }
                
                // Pour les autres types d'erreurs ou absence de données
                if (!data || data.error || !Array.isArray(data) || data.length === 0) {
                    showBlueMessage('actif');
                    return;
                }
                
                // Si des collaborateurs sont trouvés, les afficher
                let html = '';
                data.forEach(employee => {
                    // Utiliser les propriétés correctes avec vérification
                    const employeeId = employee.id || employee.employee_id || '';
                    const role = employee.role || employee.poste || 'inconnu';
                    
                    html += `
                        <tr>
                            <td>${employeeId}</td>
                            <td>${employee.nom || ''}</td>
                            <td>${employee.prenom || ''}</td>
                            <td>${employee.username || ''}</td>
                            <td>${role}</td>
                            <td>${employee.email || ''}</td>
                            <td>${employee.telephone || ''}</td>
                            <td>${employee.date_ajout || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-employee" data-id="${employeeId}" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-employee" data-id="${employeeId}" data-bs-toggle="modal" data-bs-target="#editEmployeeModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger deactivate-employee" data-id="${employeeId}">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                document.getElementById('employees-table').innerHTML = html;
                
                // Ajouter les écouteurs d'événements pour les actions
                addEmployeeEventListeners();
            })
            .catch(error => {
                console.error('Erreur lors du chargement des collaborateurs:', error);
                showBlueMessage('actif');
            });
    }

    // Fonction pour charger les collaborateurs inactifs
    function loadInactiveEmployees(societyId) {
        // Afficher le spinner pendant le chargement
        document.getElementById('employees-table').innerHTML = `
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des collaborateurs...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Add a timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Corrected API endpoint - change getEmployees.php to getAllEmployee.php
        fetch(`/api/company/getAllEmployee.php?societe_id=${societyId}&desactivate=1&_t=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                // Vérification spécifique pour l'erreur "No employees found"
                if (data && data.error === "No employees found") {
                    showBlueMessage('désactivé');
                    return;
                }
                
                // Pour les autres types d'erreurs ou absence de données
                if (!data || data.error || !Array.isArray(data) || data.length === 0) {
                    showBlueMessage('désactivé');
                    return;
                }
                
                // Si des collaborateurs sont trouvés, les afficher
                let html = '';
                data.forEach(employee => {
                    // Utiliser les propriétés correctes avec vérification
                    const employeeId = employee.id || employee.employee_id || '';
                    const role = employee.role || employee.poste || 'inconnu';
                    
                    html += `
                        <tr>
                            <td>${employeeId}</td>
                            <td>${employee.nom || ''}</td>
                            <td>${employee.prenom || ''}</td>
                            <td>${employee.username || ''}</td>
                            <td>${role}</td>
                            <td>${employee.email || ''}</td>
                            <td>${employee.telephone || ''}</td>
                            <td>${employee.date_ajout || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-employee" data-id="${employeeId}" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-success reactivate-employee" data-id="${employeeId}">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                document.getElementById('employees-table').innerHTML = html;
                
                // Ajouter les écouteurs d'événements pour les actions
                addEmployeeEventListeners();
            })
            .catch(error => {
                console.error('Erreur lors du chargement des collaborateurs désactivés:', error);
                showBlueMessage('désactivé');
            });
    }

    // Fonction pour vérifier la limite d'employés
    function checkEmployeeLimit() {
        fetch(`/api/company/checkEmployeeLimit.php?societe_id=${societyId}`, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            employeeLimitInfo = data;
            
            // Mettre à jour l'affichage de la limite
            const limitInfoElement = document.getElementById('employee-count-message');
            const upgradeButton = document.getElementById('upgrade-subscription-btn');
            
            if (data.status) {
                // Si la société peut ajouter des employés
                limitInfoElement.innerHTML = `Vous avez actuellement <strong>${data.current}</strong> collaborateurs actifs sur un maximum de <strong>${data.max}</strong> (${data.remaining} restants).`;
                upgradeButton.style.display = data.remaining < 3 ? 'inline-block' : 'none'; // Afficher le bouton si moins de 3 places restantes
            } else {
                // Si la limite est atteinte
                limitInfoElement.innerHTML = `<strong>Limite atteinte :</strong> ${data.message}`;
                upgradeButton.style.display = 'inline-block';
                document.getElementById('employee-limit-info').classList.remove('alert-info');
                document.getElementById('employee-limit-info').classList.add('alert-warning');
            }
            
            // Afficher les informations sur l'abonnement si disponibles
            if (data.subscription) {
                const subscriptionInfo = `<br><small>Abonnement actuel : ${data.subscription.nom} - Validité : ${new Date(data.subscription.date_debut).toLocaleDateString('fr-FR')} au ${new Date(data.subscription.date_fin).toLocaleDateString('fr-FR')}</small>`;
                limitInfoElement.innerHTML += subscriptionInfo;
            }
        })
        .catch(error => {
            console.error('Erreur lors de la vérification de la limite d\'employés:', error);
            document.getElementById('employee-count-message').innerHTML = 'Erreur lors du chargement des informations sur votre limite d\'employés.';
        });
    }

    // Fonction pour ajouter un employé avec vérification de la limite
    function addEmployee(societyId) {
        // Vérifier d'abord si la société peut ajouter un employé
        fetch(`/api/company/checkEmployeeLimit.php?societe_id=${societyId}`, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                alert(data.message);
                return;
            }
            
            // Si la limite n'est pas atteinte, continuer avec l'ajout de l'employé
            const formData = {
                nom: document.getElementById("nom").value,
                prenom: document.getElementById("prenom").value,
                username: document.getElementById("username").value,
                role: document.getElementById("role").value,
                email: document.getElementById("email").value,
                telephone: document.getElementById("telephone").value,
                password: document.getElementById("password").value,
                id_societe: societyId,
            };

            fetch("/api/employee/create.php", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: "Bearer " + getToken(),
                },
                body: JSON.stringify(formData),
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.id) {
                    // Succès
                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById("addEmployeeModal")
                    );
                    modal.hide();

                    // Réinitialiser le formulaire
                    document.getElementById("addEmployeeForm").reset();

                    // Recharger la liste des employés
                    loadActiveEmployees(societyId);
                    
                    // Mettre à jour les informations sur la limite
                    checkEmployeeLimit();

                    // Afficher un message de succès
                    alert("Collaborateur ajouté avec succès!");
                } else {
                    // Erreur
                    alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
                }
            })
            .catch((error) => {
                console.error("Erreur lors de l'ajout du collaborateur:", error);
                alert("Une erreur est survenue lors de l'ajout du collaborateur");
            });
        })
        .catch(error => {
            console.error('Erreur lors de la vérification de la limite d\'employés:', error);
            alert('Erreur lors de la vérification de votre limite d\'employés.');
        });
    }

    // Fonction pour actualiser la liste des employés en fonction de l'onglet actif
    function refreshEmployeeList() {
        if (currentEmployeeStatus === 'active') {
            loadActiveEmployees(societyId);
        } else {
            loadInactiveEmployees(societyId);
        }
    }

    // Add the missing addEmployeeEventListeners function
    function addEmployeeEventListeners() {
        // Add event listeners to view buttons
        document.querySelectorAll('.view-employee').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                viewEmployeeDetails(id);
            });
        });
        
        // Add event listeners to edit buttons
        document.querySelectorAll('.edit-employee').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                fetchEmployeeDetails(id);
            });
        });
        
        // Add event listeners to deactivate buttons
        document.querySelectorAll('.deactivate-employee').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                confirmDeactivateEmployee(id);
            });
        });
        
        // Add event listeners to reactivate buttons
        document.querySelectorAll('.reactivate-employee').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                reactivateEmployee(id);
            });
        });
    }
</script>

<!-- Ajouter le style pour animer le bouton de mise à niveau -->
<style>
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .btn-animate {
        animation: pulse 0.5s infinite;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
    }
</style>

<!-- Ajouter le script societe.js qui contient toutes les fonctions nécessaires -->
<script src="/data/static/js/societe.js"></script>
</body>
</html>

