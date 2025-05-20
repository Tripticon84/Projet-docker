<?php
$title = "Gestion des Devis et Contrats";
include_once "../includes/head.php";
include_once "../../api/dao/estimate.php";

// Récupération des statistiques
$contractStats = getContractStats();
if (!$contractStats) {
    $contractStats = [
        'devis_totaux' => 0,
        'contrats_actifs' => 0,
        'montant_total_contrats_mois' => 0,
        'taux_conversion' => 0,
    ];
}
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php" ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Devis et Contrats</h1>
                </div>

                <!-- Status Cards -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h3><?php echo number_format($contractStats['devis_totaux'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Devis totaux</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <h3><?php echo number_format($contractStats['contrats_actifs'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Contrats actifs</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <h3><?php echo number_format($contractStats['montant_total_contrats_mois'], 0, ',', ' '); ?>€</h3>
                            <p class="text-muted mb-0">Montant total des contrats</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <h3><?php echo $contractStats['taux_conversion']; ?>%</h3>
                            <p class="text-muted mb-0">Taux de conversion devis/contrat</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mt-4" id="contractTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="quotes-tab" data-bs-toggle="tab" href="#quotes" role="tab">Devis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contracts-tab" data-bs-toggle="tab" href="#contracts" role="tab">Contrats en cours</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="expired-tab" data-bs-toggle="tab" href="#expired" role="tab">Contrats expirés</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="contractTabContent">
                    <div class="tab-pane fade show active" id="quotes" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Liste des devis</h5>
                                <div class="d-flex">
                                    <a class="btn btn-sm btn-primary me-2" href="create.php">
                                        <i class="fas fa-plus"></i> Nouveau devis
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Date début</th>
                                                <th>Date fin</th>
                                                <th>Statut</th>
                                                <th>Montant Total</th>
                                                <th>Montant TVA</th>
                                                <th>Montant HT</th>
                                                <th>Société</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="quotesList"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Contrats en cours -->
                    <div class="tab-pane fade" id="contracts" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Contrats en cours</h5>
                                <div class="d-flex">
                                    <div class="input-group me-2" style="max-width: 210px;">
                                        <input type="text" id="searchContractInput" class="form-control form-control-sm" placeholder="Rechercher...">
                                        <button class="btn btn-sm btn-outline-secondary" type="button">
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
                                                <th>ID</th>
                                                <th>Date début</th>
                                                <th>Date fin</th>
                                                <th>Montant Total</th>
                                                <th>Montant TVA</th>
                                                <th>Montant HT</th>
                                                <th>Société</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="contractsList"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <nav aria-label="Table navigation">
                                    <ul class="pagination pagination-sm mb-0" id="contractsPagination"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Contrats expirés -->
                    <div class="tab-pane fade" id="expired" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Contrats expirés</h5>
                                <div class="d-flex">
                                    <div class="input-group me-2" style="max-width: 210px;">
                                        <input type="text" id="searchExpiredInput" class="form-control form-control-sm" placeholder="Rechercher...">
                                        <button class="btn btn-sm btn-outline-secondary" type="button">
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
                                                <th>ID</th>
                                                <th>Date début</th>
                                                <th>Date fin</th>
                                                <th>Montant Total</th>
                                                <th>Montant TVA</th>
                                                <th>Montant HT</th>
                                                <th>Société</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="expiredList"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <nav aria-label="Table navigation">
                                    <ul class="pagination pagination-sm mb-0" id="expiredPagination"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchQuotes();
            fetchContracts();
            fetchExpiredContracts();
        });

        function openPDF(devisId) {
            const token = getToken();
            const url = `../../api/estimate/generatePDF.php?devis_id=${devisId}`;

            // Ouvrir une nouvelle fenêtre pour le PDF
            window.open(url, '_blank');
        }

        function fetchQuotes() {
            const quotesList = document.getElementById('quotesList');
            quotesList.innerHTML = '<tr><td colspan="8" class="text-center">Chargement...</td></tr>';
            fetch('../../api/estimate/getAllEstimate.php', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur');
                    }
                    return response.json();
                })
                .then(data => {
                    quotesList.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            quotesList.innerHTML += `
                    <tr>
                        <td>${item.devis_id}</td>
                        <td>${formatDate(item.date_debut) || '-'}</td>
                        <td>${formatDate(item.date_fin) || '-'}</td>
                        <td>${item.statut || '-'}</td>
                        <td>${item.montant || '-'}</td>
                        <td>${item.montant_tva || '-'}</td>
                        <td>${item.montant_ht || '-'}</td>
                        <td>${item.id_societe || '-'}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="modify.php?id=${item.devis_id}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-outline-secondary" onclick="openPDF(${item.devis_id})"><i class="fas fa-file-pdf"></i></button>
                            </div>
                        <a href="viewDevis.php?id=${item.devis_id}" class="btn btn-sm btn-outline-secondary">Voir</a>
                        </td>
                    </tr>`;
                        });
                    } else {
                        quotesList.innerHTML = '<tr><td colspan="8" class="text-center">Aucun devis trouvé</td></tr>';
                    }
                })
                .catch(() => {
                    quotesList.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur</td></tr>';
                });
        }

        function fetchContracts() {
            const contractsList = document.getElementById('contractsList');
            contractsList.innerHTML = '<tr><td colspan="8" class="text-center">Chargement...</td></tr>';
            fetch('../../api/estimate/getAllContract.php', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur');
                    }
                    return response.json();
                })
                .then(data => {
                    contractsList.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            contractsList.innerHTML += `
                    <tr>
                        <td>${item.devis_id}</td>
                        <td>${formatDate(item.date_debut) || '-'}</td>
                        <td>${formatDate(item.date_fin) || '-'}</td>
                        <td>${item.montant || '-'}</td>
                        <td>${item.montant_tva || '-'}</td>
                        <td>${item.montant_ht || '-'}</td>
                        <td>${item.id_societe || '-'}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="modify.php?id=${item.devis_id}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-outline-secondary" onclick="openPDF(${item.devis_id})"><i class="fas fa-file-pdf"></i></button>
                            </div>
                        <a href="viewContract.php?id=${item.devis_id}" class="btn btn-sm btn-outline-secondary">Voir</a>
                        </td>
                    </tr>`;
                        });
                    } else {
                        contractsList.innerHTML = '<tr><td colspan="8" class="text-center">Aucun contrat trouvé</td></tr>';
                    }
                })
                .catch(() => {
                    contractsList.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur</td></tr>';
                });
        }

        function fetchExpiredContracts() {
            const expiredList = document.getElementById('expiredList');
            expiredList.innerHTML = '<tr><td colspan="8" class="text-center">Chargement...</td></tr>';
            fetch('../../api/estimate/getAllContractExpired.php', {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur');
                    }
                    return response.json();
                })
                .then(data => {
                    expiredList.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            expiredList.innerHTML += `
                    <tr>
                        <td>${item.devis_id}</td>
                        <td>${item.date_debut ? formatDate(item.date_debut) : '-'}</td>
                        <td>${item.date_fin ? formatDate(item.date_fin) : '-'}</td>
                        <td>${item.montant || '-'}</td>
                        <td>${item.montant_tva || '-'}</td>
                        <td>${item.montant_ht || '-'}</td>
                        <td>${item.id_societe || '-'}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="modify.php?id=${item.devis_id}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-outline-secondary" onclick="openPDF(${item.devis_id})"><i class="fas fa-file-pdf"></i></button>
                            </div>
                        <a href="viewContract.php?id=${item.devis_id}" class="btn btn-sm btn-outline-secondary">Voir</a>
                        </td>
                    </tr>`;
                        });
                    } else {
                        expiredList.innerHTML = '<tr><td colspan="8" class="text-center">Aucun contrat expiré</td></tr>';
                    }
                })
                .catch(() => {
                    expiredList.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Erreur</td></tr>';
                });
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        }
    </script>
</body>

</html>
