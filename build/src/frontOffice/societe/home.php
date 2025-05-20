<?php
$title = "Accueil - Société";

include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2" id="company-name">Tableau de bord</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Informations de la société</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="editCompanyInfo" data-bs-toggle="modal" data-bs-target="#editCompanyModal">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
                <div class="card-body" id="company-info">
                    <p class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </p>
                </div>
            </div>

            <!-- Recent Invoices Section -->
            <section id="invoices" class="mb-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Dernières factures</h2>
                    <a href="/frontOffice/societe/factures.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list"></i> Voir toutes les factures
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date d'émission</th>
                                <th>Date d'échéance</th>
                                <th>Montant TTC</th>
                                <th>TVA</th>
                                <th>Montant HT</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invoices-table">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement des factures...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Contracts Section -->
            <section id="contracts" class="mb-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Contrats en cours</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Statut</th>
                                <th>Montant</th>
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
            </section>

            <!-- Estimates Section -->
            <section id="estimates" class="mb-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Devis</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Statut</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="estimates-table">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement des devis...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Other Costs Section -->
            <section id="costs" class="mb-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Autres frais</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Montant</th>
                                <th>Facture ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="costs-table">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement des frais...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Employees Section -->
            <section id="employees" class="mb-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Collaborateurs</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fas fa-plus"></i> Ajouter un collaborateur
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Username</th>
                                <th>Rôle</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employees-table">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement des collaborateurs...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- Modal pour ajouter un employé -->
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
                                <option value="admin">Administrateur</option>
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

<!-- Modal pour modifier un employé -->
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
                                <option value="admin">Administrateur</option>
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

<!-- Modal pour modifier les informations de la société -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCompanyModalLabel">Modifier les informations de la société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCompanyForm">
                    <input type="hidden" id="company_id" name="id" value="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_nom" class="form-label">Nom de la société</label>
                            <input type="text" class="form-control" id="company_nom" name="nom" required>
                        </div>
                        <div class="col-md-6">
                            <label for="company_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="company_email" name="email" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="company_telephone" name="telephone">
                        </div>
                        <div class="col-md-6">
                            <label for="company_contact_person" class="form-label">Personne à contacter</label>
                            <input type="text" class="form-control" id="company_contact_person" name="contact_person">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="company_adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="company_adresse" name="adresse" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_siret" class="form-label">SIRET</label>
                            <input type="text" class="form-control" id="company_siret" name="siret">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="company_password" class="form-label">Nouveau mot de passe (optionnel)</label>
                        <input type="password" class="form-control" id="company_password" name="password" minlength="12">
                        <div class="form-text">Laissez vide pour conserver le mot de passe actuel. Le mot de passe doit comporter au moins 12 caractères.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="updateCompany">Enregistrer les modifications</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let societyId = <?php echo $_SESSION['societe_id']; ?>;

    // Fonction d'initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Charger les données
        loadCompanyInfo(societyId);
        loadContracts(societyId);
        loadEstimates(societyId);
        loadEmployees(societyId);
        loadOtherCosts(societyId);
        loadRecentInvoices(societyId);

        // Configuration des événements
        document.getElementById('refreshData').addEventListener('click', function() {
            loadCompanyInfo(societyId);
            loadContracts(societyId);
            loadEstimates(societyId);
            loadEmployees(societyId);
            loadOtherCosts(societyId);
            loadRecentInvoices(societyId);
        });

        document.getElementById('saveEmployee').addEventListener('click', function() {
            addEmployee(societyId);
        });
        document.getElementById('updateEmployee').addEventListener('click', function() {
            updateEmployee(societyId);
        });
        // Ajouter l'événement du bouton de mise à jour des infos de la société
        document.getElementById('updateCompany').addEventListener('click', function() {
            updateCompanyInfo();
        });

        // Préremplir le formulaire quand on clique sur le bouton d'édition
        document.getElementById('editCompanyInfo').addEventListener('click', function() {
            fillCompanyForm(societyId);
        });
    });

    // Fonction pour charger les informations de l'entreprise
    function loadCompanyInfo(societyId) {
        // Afficher un état de chargement
        document.getElementById('company-info').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement des informations...</span>
                </div>
            </div>
        `;
        
        // Appel AJAX pour récupérer les informations de la société
        // Utiliser getOne.php qui existe déjà dans l'API
        fetch(`/api/company/getOne.php?societe_id=${societyId}`)
            .then(response => response.json())
            .then(data => {
                // Pour déboguer, afficher les données dans la console
                console.log('Données de la société:', data);
                
                if (data && !data.error) {
                    // Mettre à jour le nom de l'entreprise dans le titre
                    document.getElementById('company-name').textContent = data.nom || 'Tableau de bord';
                    
                    // Construire l'affichage des informations de la société
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nom:</strong> ${data.nom || 'Non spécifié'}</p>
                                <p><strong>Email:</strong> ${data.email || 'Non spécifié'}</p>
                                <p><strong>Téléphone:</strong> ${data.telephone || 'Non spécifié'}</p>
                                <p><strong>Adresse:</strong> ${data.adresse || 'Non spécifiée'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>SIRET:</strong> ${data.siret || 'Non spécifié'}</p>
                                <p><strong>Date de création:</strong> ${data.date_creation ? new Date(data.date_creation).toLocaleDateString() : 'Non spécifiée'}</p>
                                <p><strong>Contact:</strong> ${data.contact_person || 'Non spécifié'}</p>
                                <p><strong>Statut:</strong> <span class="badge ${data.desactivate == 1 ? 'bg-danger' : 'bg-success'}">${data.desactivate == 1 ? 'Désactivé' : 'Actif'}</span></p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('company-info').innerHTML = html;
                } else {
                    // Afficher un message d'erreur
                    document.getElementById('company-info').innerHTML = `
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Impossible de récupérer les informations de la société. ${data.error || 'Erreur inconnue.'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des informations de la société:', error);
                document.getElementById('company-info').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Une erreur s'est produite lors de la récupération des informations de la société.
                    </div>
                `;
            });
    }

    // Fonction pour charger les devis
    function loadEstimates(societyId) {
        // Afficher le spinner pendant le chargement
        document.getElementById('estimates-table').innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des devis...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Add a timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Appel AJAX pour récupérer les devis
        fetch(`/api/company/getEstimate.php?societe_id=${societyId}&_t=${timestamp}`)
            .then(response => response.json())
            .then(data => {
                // Pour déboguer, affiche la structure des données en console
                console.log('Données des devis reçues:', data);
                
                // Vérifier les deux formats d'erreur possibles
                if (data && (data.error === "Estimates not found" || 
                    (data.error === true && data.message === "Estimates not found"))) {
                    document.getElementById('estimates-table').innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">
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
                            // Utiliser différentes alternatives pour les propriétés qui peuvent avoir différents noms
                            const estimateId = estimate.devis_id || estimate.id_devis || estimate.id || '';
                            const estimateStatus = estimate.statut || estimate.status || 'inconnu';
                            const estimateMontant = estimate.montant || estimate.montant_ttc || estimate.montant_ht || 0;
                            
                            html += `
                                <tr>
                                    <td>${estimateId}</td>
                                    <td>${estimate.date_debut || ''}</td>
                                    <td>${estimate.date_fin || ''}</td>
                                    <td><span class="badge bg-${getStatusBadgeColor(estimateStatus)}">${estimateStatus}</span></td>
                                    <td>${estimateMontant} €</td>
                                    <td>
                                        <a href="/frontOffice/societe/estimates/view.php?id=${estimateId}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                        document.getElementById('estimates-table').innerHTML = html;
                    } else {
                        // Si aucun devis n'est trouvé, afficher un message simple
                        document.getElementById('estimates-table').innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center">
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
                            <td colspan="6" class="text-center">
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
                        <td colspan="6" class="text-center">
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
        switch (status.toLowerCase()) {
            case 'brouillon': return 'secondary';
            case 'envoyé': return 'primary';
            case 'accepté': return 'success';
            case 'refusé': return 'danger';
            default: return 'info';
        }
    }

    // Fonction pour charger les collaborateurs
    function loadEmployees(societyId) {
        // Afficher le spinner pendant le chargement
        document.getElementById('employees-table').innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des collaborateurs...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Add a timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // Appel AJAX pour récupérer les collaborateurs
        fetch(`/api/company/getEmployees.php?societe_id=${societyId}&_t=${timestamp}`)
            .then(response => response.json())
            .then(data => {
                // Cas spécial pour l'erreur "Employees not found" - afficher simplement "Aucun collaborateur"
                if (data && data.error === "Employees not found") {
                    document.getElementById('employees-table').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-info mb-0" role="alert">
                                    Aucun collaborateur
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                if (data && Array.isArray(data)) {
                    if (data.length > 0) {
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
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-employee" data-id="${employeeId}" data-bs-toggle="modal" data-bs-target="#editEmployeeModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-employee" data-id="${employeeId}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        document.getElementById('employees-table').innerHTML = html;
                        
                        // Ajouter les écouteurs d'événements pour les actions
                        addEmployeeEventListeners();
                    } else {
                        // Si aucun collaborateur n'est trouvé, afficher un message simple
                        document.getElementById('employees-table').innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info mb-0" role="alert">
                                        Aucun collaborateur
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                } else {
                    // En cas d'erreur de format de données (autres erreurs), afficher également "Aucun collaborateur"
                    document.getElementById('employees-table').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-info mb-0" role="alert">
                                    Aucun collaborateur
                                </div>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des collaborateurs:', error);
                // Même en cas d'erreur réseau, afficher "Aucun collaborateur" en bleu
                document.getElementById('employees-table').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="alert alert-info mb-0" role="alert">
                                Aucun collaborateur
                            </div>
                        </td>
                    </tr>
                `;
            });
    }

    // Fonction pour charger les autres frais
    function loadOtherCosts(societyId) {
        // Afficher le spinner pendant le chargement
        document.getElementById('costs-table').innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des frais...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Add timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        // API call to get all fees
        fetch(`/api/company/getFees.php?societe_id=${societyId}&_t=${timestamp}`)
            .then(response => response.json())
            .then(data => {
                // Case for when there are no fees or an error occurred
                if (!data || data.error || data.length === 0) {
                    document.getElementById('costs-table').innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="alert alert-info mb-0" role="alert">
                                    Aucun frais trouvé
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                // Display the fees
                let html = '';
                data.forEach(cost => {
                    const costId = cost.frais_id || cost.id || '';
                    const costName = cost.nom || 'Non spécifié';
                    const costAmount = parseFloat(cost.montant).toFixed(2) || '0.00';
                    const costInvoiceId = cost.facture_id || 'N/A';
                    
                    html += `
                        <tr>
                            <td>${costId}</td>
                            <td>${costName}</td>
                            <td>${costAmount}€</td>
                            <td>${costInvoiceId}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-cost" data-id="${costId}" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                document.getElementById('costs-table').innerHTML = html;
                
                // Add event listeners for the view buttons
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
                        <td colspan="5" class="text-center">
                            <div class="alert alert-warning mb-0" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Erreur lors du chargement des frais
                            </div>
                        </td>
                    </tr>
                `;
            });
    }

    // Fonction pour afficher les détails d'un frais spécifique (à implémenter si nécessaire)
    function showCostDetails(costId) {
        // Créer une modal ou utiliser une modal existante pour afficher les détails
        // Cela serait similaire à l'implémentation dans fees_and_subscription.php
        alert(`Détails du frais #${costId} - Fonctionnalité à implémenter`);
        
        // Pour une implémentation complète, vous pourriez récupérer les détails spécifiques du frais
        // et les afficher dans une modal similaire à fees_and_subscription.php
    }

    // Fonction pour remplir le formulaire avec les informations actuelles
    function fillCompanyForm(societyId) {
        fetch(`/api/company/getOne.php?societe_id=${societyId}`)
            .then(response => response.json())
            .then(data => {
                if (data && !data.error) {
                    // Remplir le formulaire avec les données existantes
                    document.getElementById('company_id').value = societyId;
                    document.getElementById('company_nom').value = data.nom || '';
                    document.getElementById('company_email').value = data.email || '';
                    document.getElementById('company_telephone').value = data.telephone || '';
                    document.getElementById('company_contact_person').value = data.contact_person || '';
                    document.getElementById('company_adresse').value = data.adresse || '';
                    document.getElementById('company_siret').value = data.siret || '';
                    // Le mot de passe reste vide car on ne reçoit pas le mot de passe actuel
                } else {
                    console.error('Erreur lors de la récupération des données:', data.error || 'Erreur inconnue');
                    alert('Impossible de récupérer les informations de la société.');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la récupération des informations.');
            });
    }

    // Fonction pour mettre à jour les informations de la société
    function updateCompanyInfo() {
        const form = document.getElementById('editCompanyForm');
        const formData = new FormData(form);
        
        // Convertir FormData en objet JSON
        const data = {};
        formData.forEach((value, key) => {
            // Ne pas inclure les champs vides, notamment le mot de passe s'il est vide
            if (value !== '') {
                data[key] = value;
            }
        });
        
        console.log('Données envoyées:', data); // Pour le débogage
        
        // Envoyer la requête de mise à jour avec PATCH
        fetch('/api/company/update.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            // Tenter de parser la réponse JSON
            return response.text().then(text => {
                try {
                    // Parser le texte de réponse en JSON
                    const jsonResponse = text ? JSON.parse(text) : {};
                    
                    // Vérifier si la réponse contient un ID (succès) malgré des erreurs éventuelles
                    if (jsonResponse.id) {
                        return { success: true, id: jsonResponse.id };
                    }
                    
                    // Si pas d'ID mais une erreur, renvoyer l'erreur
                    if (jsonResponse.error) {
                        throw new Error(jsonResponse.message || 'Erreur serveur');
                    }
                    
                    // Si aucun ID et pas d'erreur explicite, vérifier le status HTTP
                    if (!response.ok) {
                        throw new Error(response.statusText || 'Erreur lors de la mise à jour');
                    }
                    
                    return jsonResponse;
                } catch (e) {
                    // Si on ne peut pas parser le JSON mais qu'il y a "id" dans le texte
                    if (text.includes('"id"')) {
                        return { success: true };
                    }
                    throw new Error('Réponse invalide du serveur');
                }
            });
        })
        .then(result => {
            if (result.success || result.id) {
                alert('Les informations de la société ont été mises à jour avec succès!');
                // Fermer la modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editCompanyModal'));
                modal.hide();
                // Recharger les infos de la société
                loadCompanyInfo(societyId);
            } else {
                throw new Error('Mise à jour échouée');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            
            // Si le message contient un signe que l'opération a réussi malgré l'erreur
            if (error.message && (error.message.includes('certificate') || error.message.includes('SSL'))) {
                // Proposer à l'utilisateur de vérifier si la mise à jour a fonctionné malgré l'erreur
                if (confirm('Il y a eu un problème de certificat SSL, mais la mise à jour a peut-être réussi. Voulez-vous vérifier en rechargeant les données?')) {
                    // Fermer la modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCompanyModal'));
                    modal.hide();
                    // Recharger les infos de la société
                    loadCompanyInfo(societyId);
                    return;
                }
            }
            
            alert(`Erreur lors de la mise à jour: ${error.message || 'Une erreur est survenue'}`);
        });
    }

    // Autres fonctions (loadContracts, loadEmployees, etc.)
    // ...

</script>