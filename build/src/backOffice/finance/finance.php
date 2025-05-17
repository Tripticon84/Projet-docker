<?php
$title = "Finance";
include_once "../includes/head.php"
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php" ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion Financière</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar"></i> Cette année
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="#">Ce mois</a></li>
                                <li><a class="dropdown-item" href="#">Ce trimestre</a></li>
                                <li><a class="dropdown-item" href="#">Cette année</a></li>
                                <li><a class="dropdown-item" href="#">Personnalisé</a></li>
                            </ul>
                        </div>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Cards -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <h3>684.2K€</h3>
                            <p class="text-muted mb-0">Revenus annuels</p>
                            <div class="mt-2 text-success small">
                                <i class="fas fa-arrow-up"></i> +12% par rapport à l'année précédente
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h3>57.2K€</h3>
                            <p class="text-muted mb-0">Revenus mensuels</p>
                            <div class="mt-2 text-success small">
                                <i class="fas fa-arrow-up"></i> +8% par rapport au mois précédent
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-coins"></i>
                            </div>
                            <h3>348.7K€</h3>
                            <p class="text-muted mb-0">Dépenses annuelles</p>
                            <div class="mt-2 text-danger small">
                                <i class="fas fa-arrow-up"></i> +5% par rapport à l'année précédente
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>49.2%</h3>
                            <p class="text-muted mb-0">Marge bénéficiaire</p>
                            <div class="mt-2 text-success small">
                                <i class="fas fa-arrow-up"></i> +3% par rapport à l'année précédente
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue and Expenses Charts Row -->
                <div class="row mt-4">
                    <!-- Revenue and Expenses Chart -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Revenus et Dépenses (K€)</h5>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active">Mensuel</button>
                                    <button type="button" class="btn btn-outline-secondary">Trimestriel</button>
                                    <button type="button" class="btn btn-outline-secondary">Annuel</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueExpenseChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Financial KPIs -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Indicateurs Clés</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Croissance du CA</span>
                                        <span class="text-success">12%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 12%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Taux de renouvellement</span>
                                        <span class="text-success">88%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 88%" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Coût d'acquisition client</span>
                                        <span>2.8K€</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Valeur client sur la durée</span>
                                        <span>18.5K€</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Tous les indicateurs sont en hausse par rapport au dernier trimestre.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue by Category and Recent Invoices -->
                <div class="row mt-4">
                    <!-- Revenue by Category -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Répartition des revenus</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                        2025
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <li><a class="dropdown-item" href="#">2025</a></li>
                                        <li><a class="dropdown-item" href="#">2024</a></li>
                                        <li><a class="dropdown-item" href="#">2023</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueCategoryChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Invoices -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Factures récentes</h5>
                                <a href="#" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Nouvelle facture
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Client</th>
                                                <th>Date</th>
                                                <th>Montant</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>F-2025-142</td>
                                                <td>TechInnov</td>
                                                <td>02/03/2025</td>
                                                <td>12,500€</td>
                                                <td><span class="badge bg-success">Payée</span></td>
                                            </tr>
                                            <tr>
                                                <td>F-2025-141</td>
                                                <td>EcoSolutions</td>
                                                <td>28/02/2025</td>
                                                <td>8,750€</td>
                                                <td><span class="badge bg-warning">En attente</span></td>
                                            </tr>
                                            <tr>
                                                <td>F-2025-140</td>
                                                <td>DigitalWave</td>
                                                <td>25/02/2025</td>
                                                <td>22,500€</td>
                                                <td><span class="badge bg-success">Payée</span></td>
                                            </tr>
                                            <tr>
                                                <td>F-2025-139</td>
                                                <td>GreenLife</td>
                                                <td>20/02/2025</td>
                                                <td>5,400€</td>
                                                <td><span class="badge bg-success">Payée</span></td>
                                            </tr>
                                            <tr>
                                                <td>F-2025-138</td>
                                                <td>SmartRetail</td>
                                                <td>15/02/2025</td>
                                                <td>17,800€</td>
                                                <td><span class="badge bg-danger">En retard</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-sm btn-outline-primary">Voir toutes les factures</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Third Row - Cash Flow and Expenses Breakdown -->
                <div class="row mt-4">
                    <!-- Cash Flow Projection -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Prévision de trésorerie (K€)</h5>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active">3 mois</button>
                                    <button type="button" class="btn btn-outline-secondary">6 mois</button>
                                    <button type="button" class="btn btn-outline-secondary">12 mois</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="cashFlowChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Breakdown -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Répartition des dépenses</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="expensesChart" height="245"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fourth Row - Recent Transactions & Revenue Forecast -->
                <div class="row mt-4">
                    <!-- Recent Transactions -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Transactions récentes</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                        Filtrer
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                        <li><a class="dropdown-item" href="#">Toutes</a></li>
                                        <li><a class="dropdown-item" href="#">Entrées</a></li>
                                        <li><a class="dropdown-item" href="#">Sorties</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Catégorie</th>
                                                <th>Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>T-10584</td>
                                                <td>03/03/2025</td>
                                                <td>Paiement facture TechInnov</td>
                                                <td>Abonnement Premium</td>
                                                <td class="text-success">+12,500€</td>
                                            </tr>
                                            <tr>
                                                <td>T-10583</td>
                                                <td>02/03/2025</td>
                                                <td>Salaires Mars 2025</td>
                                                <td>Ressources humaines</td>
                                                <td class="text-danger">-42,650€</td>
                                            </tr>
                                            <tr>
                                                <td>T-10582</td>
                                                <td>28/02/2025</td>
                                                <td>Paiement facture DigitalWave</td>
                                                <td>Abonnement Basic</td>
                                                <td class="text-success">+22,500€</td>
                                            </tr>
                                            <tr>
                                                <td>T-10581</td>
                                                <td>25/02/2025</td>
                                                <td>Location bureaux Mars 2025</td>
                                                <td>Loyer</td>
                                                <td class="text-danger">-8,500€</td>
                                            </tr>
                                            <tr>
                                                <td>T-10580</td>
                                                <td>23/02/2025</td>
                                                <td>Paiement facture GreenLife</td>
                                                <td>Abonnement Starter</td>
                                                <td class="text-success">+5,400€</td>
                                            </tr>
                                            <tr>
                                                <td>T-10579</td>
                                                <td>20/02/2025</td>
                                                <td>Achat matériel informatique</td>
                                                <td>Équipement</td>
                                                <td class="text-danger">-3,850€</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-sm btn-outline-primary">Voir toutes les transactions</a>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Forecast & Financial Calendar -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Prévision des revenus</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Mars 2025</span>
                                    <span>65K€ <small class="text-success">(+5%)</small></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Avril 2025</span>
                                    <span>68K€ <small class="text-success">(+4%)</small></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Mai 2025</span>
                                    <span>72K€ <small class="text-success">(+6%)</small></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">T2 2025 (prév.)</span>
                                    <span class="fw-bold">205K€ <small class="text-success">(+8%)</small></span>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Calendrier financier</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Clôture mensuelle</h6>
                                            <small>31/03/2025</small>
                                        </div>
                                        <p class="mb-1 small text-muted">Finalisation des comptes de mars</p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Facturation récurrente</h6>
                                            <small>01/04/2025</small>
                                        </div>
                                        <p class="mb-1 small text-muted">Génération automatique des factures</p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Versement TVA</h6>
                                            <small>15/04/2025</small>
                                        </div>
                                        <p class="mb-1 small text-muted">Paiement trimestriel de la TVA</p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Revue budgétaire</h6>
                                            <small>20/04/2025</small>
                                        </div>
                                        <p class="mb-1 small text-muted">Analyse des écarts T1 et prévisions</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Cards -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Actions rapides</h5>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-file-invoice fa-2x text-primary"></i>
                            </div>
                            <h6>Créer une facture</h6>
                            <a href="#" class="btn btn-sm btn-outline-primary mt-2">Générer</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-receipt fa-2x text-success"></i>
                            </div>
                            <h6>Enregistrer une dépense</h6>
                            <a href="#" class="btn btn-sm btn-outline-success mt-2">Ajouter</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-file-export fa-2x text-warning"></i>
                            </div>
                            <h6>Exporter un rapport</h6>
                            <a href="#" class="btn btn-sm btn-outline-warning mt-2">Exporter</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-calculator fa-2x text-info"></i>
                            </div>
                            <h6>Calculer des projections</h6>
                            <a href="#" class="btn btn-sm btn-outline-info mt-2">Calculer</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Exporter les données financières</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="exportPeriod" class="form-label">Période</label>
                            <select class="form-select" id="exportPeriod">
                                <option selected>Ce mois</option>
                                <option>Ce trimestre</option>
                                <option>Cette année</option>
                                <option>Personnalisé</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="exportFormat" class="form-label">Format</label>
                            <select class="form-select" id="exportFormat">
                                <option selected>Excel (.xlsx)</option>
                                <option>CSV (.csv)</option>
                                <option>PDF (.pdf)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="exportContent" class="form-label">Contenu</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="checkRevenues" checked>
                                <label class="form-check-label" for="checkRevenues">
                                    Revenus
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="checkExpenses" checked>
                                <label class="form-check-label" for="checkExpenses">
                                    Dépenses
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="checkInvoices" checked>
                                <label class="form-check-label" for="checkInvoices">
                                    Factures
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="checkTransactions">
                                <label class="form-check-label" for="checkTransactions">
                                    Transactions
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary">Exporter</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts pour les graphiques -->
    <script>
        // Revenue & Expenses Chart
        const revenueExpenseCtx = document.getElementById('revenueExpenseChart').getContext('2d');
        const revenueExpenseChart = new Chart(revenueExpenseCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                        label: 'Revenus (K€)',
                        data: [42.8, 45.3, 51.2, 52.7, 55.6, 57.2, 59.8, 61.5, 63.2, 64.7, 68.1, 72.5],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    },
                    {
                        label: 'Dépenses (K€)',
                        data: [24.5, 26.2, 27.1, 28.3, 29.5, 30.2, 31.4, 32.1, 33.5, 35.2, 36.8, 37.9],
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' K€';
                            }
                        }
                    }
                }
            }
        });

        // Revenue Category Chart
        const revenueCategoryCtx = document.getElementById('revenueCategoryChart').getContext('2d');
        const revenueCategoryChart = new Chart(revenueCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Plan Premium', 'Plan Basic', 'Plan Starter', 'Services additionnels', 'Formations'],
                datasets: [{
                    data: [45, 30, 15, 7, 3],
                    backgroundColor: [
                        '#3a86ff',
                        '#8338ec',
                        '#ff006e',
                        '#fb5607',
                        '#ffbe0b'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Cash Flow Chart
        const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
        const cashFlowChart = new Chart(cashFlowCtx, {
            type: 'line',
            data: {
                labels: ['Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août'],
                datasets: [{
                        label: 'Entrées (K€)',
                        data: [58, 61, 65, 68, 71, 73],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Sorties (K€)',
                        data: [32, 33, 34, 35, 36, 37],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.05)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Solde (K€)',
                        data: [26, 28, 31, 33, 35, 36],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.05)',
                        tension: 0.3,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' K€';
                            }
                        }
                    }
                }
            }
        });

        // Expenses Chart
        const expensesCtx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(expensesCtx, {
            type: 'pie',
            data: {
                labels: ['Salaires', 'Loyer', 'Marketing', 'Technologie', 'Administratif', 'Autres'],
                datasets: [{
                    data: [62, 12, 10, 8, 5, 3],
                    backgroundColor: [
                        '#ff006e',
                        '#fb5607',
                        '#ffbe0b',
                        '#3a86ff',
                        '#8338ec',
                        '#38b000'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>

</html>
