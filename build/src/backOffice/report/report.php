<?php
$title = "Gestion des Signalements";
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
                    <h1 class="h2">Gestion des Signalements</h1>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-danger-subtle text-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <p class="text-muted mb-0">Signalements en attente</p>
                            <h3 id="pendingCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <p class="text-muted mb-0">Signalements en cours</p>
                            <h3 id="inProgressCount">-</h3>
                        </div>
                    </div>
                    <div la class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p class="text-muted mb-0">Signalements traités</p>
                            <h3 id="resolvedCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="fas fa-building"></i>
                            </div>
                            <p class="text-muted mb-0">Entreprises concernées</p>
                            <h3 id="companiesCount">-</h3>
                        </div>
                    </div>
                </div>

                <!-- Pending Reports Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Signalements en attente</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Entreprise</th>
                                        <th scope="col">Titre</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingReportsList">
                                    <tr>
                                        <td colspan="6" class="text-center">Chargement des signalements...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- In Progress Reports Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Signalements en cours de traitement</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Entreprise</th>
                                        <th scope="col">Titre</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inProgressReportsList">
                                    <tr>
                                        <td colspan="6" class="text-center">Chargement des signalements...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Processed Reports Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Signalements traités</h5>
                        <div class="d-flex mt-2 mt-sm-0 align-items-center">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Date signalement</th>
                                        <th scope="col">Entreprise</th>
                                        <th scope="col">Titre</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="processedReportsList">
                                    <tr>
                                        <td colspan="6" class="text-center">Chargement des signalements...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small" id="paginationInfo">Chargement des données...</span>
                        </div>
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                        </nav>
                    </div>
                </div>

                <!-- Cancelled Reports Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Signalements annulés</h5>
                        <div class="d-flex mt-2 mt-sm-0 align-items-center">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Date signalement</th>
                                        <th scope="col">Entreprise</th>
                                        <th scope="col">Titre</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="cancelledReportsList">
                                    <tr>
                                        <td colspan="5" class="text-center">Chargement des signalements...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour afficher les détails d'un signalement -->
    <div class="modal fade" id="reportDetailModal" tabindex="-1" aria-labelledby="reportDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportDetailModalLabel">Détails du signalement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <span id="modal-report-id"></span></p>
                            <p><strong>Entreprise:</strong> <span id="modal-report-company"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date:</strong> <span id="modal-report-date"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Statut:</strong> <span id="modal-report-status"></span></p>
                        </div>
                    </div>
                    <div class="mb-3">

                        <h5 class="p-3" id="modal-report-title"></h5>
                        <h6>Description du problème:</h6>
                        <div class="p-3 bg-light rounded" id="modal-report-problem"></div>
                    </div>
                    <div id="actionSection">
                        <h6>Actions:</h6>
                        <div class="mb-3">
                            <label for="reportStatus" class="form-label">Changer le statut:</label>
                            <select class="form-select" id="reportStatus">
                                <option value="non_traite">En attente</option>
                                <option value="en_cours">En cours de traitement</option>
                                <option value="resolu">Résolu</option>
                                <option value="annuler">Archivé</option>
                            </select>
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
        document.addEventListener('DOMContentLoaded', function() {
            fetchPendingReports();
            fetchInProgressReports();
            fetchProcessedReports();
            fetchCancelledReports();
        });

        let currentPage = 1;
        // Variables pour stocker les données des signalements
        let pendingReports = [];
        let inProgressReports = [];
        let processedReports = [];
        let cancelledReports = [];
        let allCompanies = new Set();
        let currentReportStatus = ''; // Add this variable to track the current status

        // Fonction pour mettre à jour les compteurs statistiques
        function updateStatCounters() {
            document.getElementById('pendingCount').textContent = pendingReports.length;
            document.getElementById('inProgressCount').textContent = inProgressReports.length;
            document.getElementById('resolvedCount').textContent = processedReports.length;
            document.getElementById('companiesCount').textContent = allCompanies.size;
        }

        function fetchPendingReports() {
            const reportsList = document.getElementById('pendingReportsList');
            reportsList.innerHTML = '<tr><td colspan="6" class="text-center">Chargement des signalements...</td></tr>';

            fetch('../../api/report/getByStatus.php?statut=non_traite', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    reportsList.innerHTML = '';
                    pendingReports = data && Array.isArray(data) ? data : [];

                    // Mise à jour du compteur de signalements en attente
                    document.getElementById('pendingCount').textContent = pendingReports.length;

                    // Ajouter les entreprises uniques
                    pendingReports.forEach(report => {
                        if (report.id_societe) allCompanies.add(report.id_societe);
                    });

                    updateStatCounters();

                    if (pendingReports.length > 0) {
                        pendingReports.forEach(report => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td>${report.signalement_id}</td>
                            <td>${formatDate(report.date_signalement)}</td>
                            <td>${report.id_societe || 'Non spécifié'}</td>
                            <td>${truncateText(report.probleme, 50)}</td>
                            <td><span class="badge bg-warning">En attente</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary me-1" onclick="viewReportDetails(${report.signalement_id})">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                <button class="btn btn-sm btn-success" onclick="markAsInProgress(${report.signalement_id})">
                                    <i class="fas fa-spinner"></i> Traiter
                                </button>
                            </td>
                        `;
                            reportsList.appendChild(row);
                        });
                    } else {
                        reportsList.innerHTML = '<tr><td colspan="6" class="text-center">Aucun signalement en attente</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    reportsList.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des signalements</td></tr>';
                });
        }

        function fetchInProgressReports() {
            const reportsList = document.getElementById('inProgressReportsList');
            reportsList.innerHTML = '<tr><td colspan="6" class="text-center">Chargement des signalements...</td></tr>';

            fetch('../../api/report/getByStatus.php?statut=en_cours', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    reportsList.innerHTML = '';
                    inProgressReports = data && Array.isArray(data) ? data : [];

                    // Mise à jour du compteur de signalements en cours
                    document.getElementById('inProgressCount').textContent = inProgressReports.length;

                    // Ajouter les entreprises uniques
                    inProgressReports.forEach(report => {
                        if (report.id_societe) allCompanies.add(report.id_societe);
                    });

                    updateStatCounters();

                    if (inProgressReports.length > 0) {
                        inProgressReports.forEach(report => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td>${report.signalement_id}</td>
                            <td>${formatDate(report.date_signalement)}</td>
                            <td>${report.id_societe || 'Non spécifié'}</td>
                            <td>${truncateText(report.probleme, 50)}</td>
                            <td><span class="badge bg-info">En cours</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary me-1" onclick="viewReportDetails(${report.signalement_id})">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                <button class="btn btn-sm btn-success" onclick="markAsProcessed(${report.signalement_id})">
                                    <i class="fas fa-check"></i> Terminer
                                </button>
                            </td>
                        `;
                            reportsList.appendChild(row);
                        });
                    } else {
                        reportsList.innerHTML = '<tr><td colspan="6" class="text-center">Aucun signalement en cours de traitement</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    reportsList.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des signalements</td></tr>';
                });
        }

        function fetchProcessedReports(search = '', page = 1, filter = 'all') {
            currentPage = page;
            const reportsList = document.getElementById('processedReportsList');
            reportsList.innerHTML = '<tr><td colspan="6" class="text-center">Chargement des signalements...</td></tr>';

            let limit = 5;
            let offset = (page - 1) * limit;
            let url = `../../api/report/getByStatus.php?statut=resolu&limit=${limit}&offset=${offset}`;

            if (search) {
                url += '&search=' + encodeURIComponent(search);
            }

            if (filter && filter !== 'all') {
                url += '&filter=' + encodeURIComponent(filter);
            }

            fetch(url, {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => response.json())
                .then(data => {
                    reportsList.innerHTML = '';
                    processedReports = data && Array.isArray(data) ? data : [];

                    // Mise à jour du compteur de signalements traités
                    document.getElementById('resolvedCount').textContent = processedReports.length;

                    // Ajouter les entreprises uniques
                    processedReports.forEach(report => {
                        if (report.id_societe) allCompanies.add(report.id_societe);
                    });

                    updateStatCounters();

                    if (processedReports.length > 0) {
                        data.forEach(report => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td>${report.signalement_id}</td>
                            <td>${formatDate(report.date_signalement)}</td>
                            <td>${report.id_societe || 'Non spécifié'}</td>
                            <td>${truncateText(report.probleme, 50)}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="viewReportDetails(${report.signalement_id}); return false;"><i class="fas fa-eye me-2"></i>Voir détails</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="archiveReport(${report.signalement_id}); return false;"><i class="fas fa-archive me-2"></i>Archiver</a></li>
                                    </ul>
                                </div>
                            </td>
                        `;
                            reportsList.appendChild(row);
                        });

                        document.getElementById('paginationInfo').textContent = `Affichage de ${offset+1}-${offset+data.length} signalements`;
                        updatePagination(data.length === limit, search, filter);
                    } else {
                        reportsList.innerHTML = '<tr><td colspan="6" class="text-center">Aucun signalement traité trouvé</td></tr>';
                        document.getElementById('paginationInfo').textContent = 'Aucun signalement traité trouvé';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    reportsList.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des signalements</td></tr>';
                    document.getElementById('paginationInfo').textContent = 'Erreur lors du chargement des données';
                });
        }

        function fetchCancelledReports() {
            const reportsList = document.getElementById('cancelledReportsList');
            reportsList.innerHTML = '<tr><td colspan="5" class="text-center">Chargement des signalements annulés...</td></tr>';

            fetch('../../api/report/getByStatus.php?statut=annuler', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok && response.status !== 200) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    reportsList.innerHTML = '';
                    // Si la réponse est un objet unique avec signalement_id, la convertir en tableau
                    if (data && !Array.isArray(data) && data.signalement_id) {
                        data = [data];
                    }
                    cancelledReports = data && Array.isArray(data) ? data : [];

                    // Ajouter les entreprises uniques
                    cancelledReports.forEach(report => {
                        if (report.id_societe) allCompanies.add(report.id_societe);
                    });

                    updateStatCounters();

                    if (cancelledReports.length > 0) {
                        cancelledReports.forEach(report => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td>${report.signalement_id}</td>
                            <td>${formatDate(report.date_signalement)}</td>
                            <td>${report.id_societe || 'Non spécifié'}</td>
                            <td>${truncateText(report.probleme, 50)}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary me-1" onclick="viewReportDetails(${report.signalement_id})">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="restoreReport(${report.signalement_id})">
                                    <i class="fas fa-undo"></i> Restaurer
                                </button>
                            </td>
                        `;
                            reportsList.appendChild(row);
                        });
                    } else {
                        reportsList.innerHTML = '<tr><td colspan="5" class="text-center">Aucun signalement annulé trouvé</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    reportsList.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur lors du chargement des signalements annulés</td></tr>';
                });
        }

        function viewReportDetails(reportId) {
            fetch(`../../api/report/getOne.php?signalement_id=${reportId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok && response.status !== 200) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(report => {
                    // Si la réponse est un objet avec signalement_id, l'utiliser directement
                    if (report && report.signalement_id) {
                        document.getElementById('modal-report-id').textContent = report.signalement_id;
                        document.getElementById('modal-report-date').textContent = formatDate(report.date_signalement);
                        document.getElementById('modal-report-company').textContent = report.id_societe || 'Non spécifié';
                        document.getElementById('modal-report-title').textContent = report.probleme || 'Non spécifié';
                        document.getElementById('modal-report-problem').textContent = report.description || 'Aucune description fournie';

                        const status = report.statut || 'non_traite';
                        currentReportStatus = status; // Store current status
                        let statusText = 'En attente';
                        let statusClass = 'text-warning';

                        if (status === 'en_cours') {
                            statusText = 'En cours de traitement';
                            statusClass = 'text-info';
                        } else if (status === 'resolu') {
                            statusText = 'Résolu';
                            statusClass = 'text-success';
                        } else if (status === 'annuler') {
                            statusText = 'Archivé';
                            statusClass = 'text-secondary';
                        }

                        document.getElementById('modal-report-status').innerHTML = `<span class="${statusClass}">${statusText}</span>`;

                        // Préparer le formulaire
                        document.getElementById('reportStatus').value = status;

                        // Configurer le bouton de sauvegarde
                        document.getElementById('saveChangesBtn').onclick = function() {
                            updateReportStatus(reportId);
                        };

                        const modal = new bootstrap.Modal(document.getElementById('reportDetailModal'));
                        modal.show();
                    } else {
                        throw new Error('Format de réponse invalide');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des détails du signalement: ' + error.message);
                });
        }

        function updateReportStatus(reportId) {
            const status = document.getElementById('reportStatus').value;
        
            

            // Check if status has changed
            if (status === currentReportStatus) {
                bootstrap.Modal.getInstance(document.getElementById('reportDetailModal')).hide();
                return;
            }

            fetch('../../api/report/changeState.php', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify({
                        signalement_id: reportId,
                        statut: status
                    })
                })
                .then(response => {
                    if (!response.ok && response.status !== 200 ) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Considérer la réponse comme un succès si elle contient des données
                    if (data && (data.success || data.signalement_id)) {
                        alert('Statut du signalement mis à jour avec succès');
                        bootstrap.Modal.getInstance(document.getElementById('reportDetailModal')).hide();
                        fetchPendingReports();
                        fetchInProgressReports();
                        fetchProcessedReports();
                        fetchCancelledReports();

                    } else {
                        alert('Erreur lors de la mise à jour du statut du signalement');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    if (!navigator.onLine) {
                        alert('Erreur de connexion: Vérifiez votre connexion internet');
                    } else {
                        alert(`Une erreur est survenue: ${error.message}`);
                    }
                });
        }

        function markAsProcessed(reportId) {
            if (confirm('Êtes-vous sûr de vouloir marquer ce signalement comme traité?')) {
                fetch('../../api/report/changeState.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify({
                            signalement_id: reportId,
                            statut: 'resolu'
                        })
                    })
                    .then(response => {
                        if (!response.ok && response.status !== 200) {
                            throw new Error(`Erreur HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Considérer comme succès si on a des données, même sans la propriété success
                        if (data && (data.success || data.signalement_id || !data.empty)) {
                            alert('Signalement marqué comme traité avec succès');
                            fetchPendingReports();
                            fetchInProgressReports();
                            fetchProcessedReports();

                        } else {
                            alert('Erreur lors du traitement du signalement');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du traitement du signalement:', error);
                        if (!navigator.onLine) {
                            alert('Erreur de connexion: Vérifiez votre connexion internet');
                        } else {
                            alert('Une erreur est survenue lors du traitement du signalement: ' + error.message);
                        }
                    });
            }
        }

        function archiveReport(reportId) {
            if (confirm('Êtes-vous sûr de vouloir archiver ce signalement?')) {
                fetch('../../api/report/changeState.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify({
                            signalement_id: reportId,
                            statut: 'annuler'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Improved success detection matching other function conditions
                        if (data && (data.success || data.signalement_id || !data.empty)) {
                            alert('Signalement archivé avec succès');
                            fetchProcessedReports();
                            fetchCancelledReports();
                        } else {
                            alert('Erreur lors de l\'archivage du signalement');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de l\'archivage du signalement');
                    });
            }
        }

        function restoreReport(reportId) {
            if (confirm('Êtes-vous sûr de vouloir restaurer ce signalement?')) {
                fetch('../../api/report/changeState.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify({
                            signalement_id: reportId,
                            statut: 'non_traite'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.empty) {
                            alert('Signalement restauré avec succès');
                            fetchPendingReports();
                            fetchInProgressReports();
                            fetchCancelledReports();

                        } else {
                            alert('Erreur lors de la restauration du signalement');
                        }
                    })
            }
        }

        function markAsInProgress(reportId) {
            if (confirm('Êtes-vous sûr de vouloir prendre en charge ce signalement?')) {
                fetch('../../api/report/changeState.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken()
                        },
                        body: JSON.stringify({
                            signalement_id: reportId,
                            statut: 'en_cours'
                        })
                    })
                    .then(response => {
                        if (!response.ok && response.status !== 200) {
                            throw new Error(`Erreur HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Considérer comme succès si on a des données
                        if (data && (data.success || data.signalement_id || !data.empty)) {
                            alert('Signalement pris en charge avec succès');
                            fetchPendingReports();
                            fetchInProgressReports();
                        } else {
                            alert('Erreur lors de la prise en charge du signalement');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        if (!navigator.onLine) {
                            alert('Erreur de connexion: Vérifiez votre connexion internet');
                        } else {
                            alert('Une erreur est survenue lors de la prise en charge du signalement: ' + error.message);
                        }
                    });
            }
        }

        function updatePagination(hasMore, search = '', filter = 'all') {
            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';

            // Bouton précédent
            let prevItem = document.createElement('li');
            prevItem.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
            prevItem.innerHTML = `<a class="page-link" href="#" onclick="fetchProcessedReports('${search}', ${currentPage - 1}, '${filter}'); return false;">Précédent</a>`;
            paginationList.appendChild(prevItem);

            // Bouton suivant
            let nextItem = document.createElement('li');
            nextItem.className = 'page-item ' + (!hasMore ? 'disabled' : '');
            nextItem.innerHTML = `<a class="page-link" href="#" onclick="fetchProcessedReports('${search}', ${currentPage + 1}, '${filter}'); return false;">Suivant</a>`;
            paginationList.appendChild(nextItem);
        }

        function generateReport() {
            alert('Fonctionnalité de génération de rapport en cours de développement');
        }

        function exportData() {
            alert('Fonctionnalité d\'exportation de données en cours de développement');
        }

        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
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
