<?php
$title = "Accueil";
include_once "includes/head.php";
include_once "../api/dao/company.php";
include_once "../api/dao/estimate.php";

// Récupération des statistiques
$companyStats = getCompaniesStats();
if (!$companyStats) {
    $companyStats = [
        'total' => 0,
        'new' => 0,
        'totalVariation' => 0,
        'newVariation' => 0,
    ];
}

$contractStats = getContractStats();
if (!$contractStats) {
    $contractStats = [
        'devis_totaux' => 0,
        'contrats_actifs' => 0,
        'montant_total_contrats_mois' => 0,
        'taux_conversion' => 0,
    ];
}

// Pour l'exemple, nous allons utiliser des valeurs fictives pour les événements et prestataires
// Ces valeurs pourraient être remplacées par des appels API réels ultérieurement
$evenementsActifs = 126;
$prestatairesActifs = 89;
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "includes/sidebar.php" ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tableau de bord</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar"></i> Cette semaine
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="#">Aujourd'hui</a></li>
                                <li><a class="dropdown-item" href="#">Cette semaine</a></li>
                                <li><a class="dropdown-item" href="#">Ce mois</a></li>
                                <li><a class="dropdown-item" href="#">Ce trimestre</a></li>
                                <li><a class="dropdown-item" href="#">Cette année</a></li>
                            </ul>
                        </div>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exporter</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Imprimer</button>
                        </div>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3><?php echo number_format($companyStats['total'], 0, ',', ' '); ?></h3>
                            <p class="text-muted mb-0">Sociétés clientes</p>
                            <div class="mt-2 <?php echo $companyStats['totalVariation'] >= 0 ? 'text-success' : 'text-danger'; ?> small">
                                <i class="fas fa-arrow-<?php echo $companyStats['totalVariation'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo ($companyStats['totalVariation'] >= 0 ? '+' : '') . $companyStats['totalVariation']; ?>% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <h3><?php echo number_format($contractStats['montant_total_contrats_mois'], 0, ',', ' '); ?>€</h3>
                            <p class="text-muted mb-0">Revenus mensuels</p>
                            <div class="mt-2 text-success small">
                                <i class="fas fa-arrow-up"></i> +8% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3><?php echo $evenementsActifs; ?></h3>
                            <p class="text-muted mb-0">Événements actifs</p>
                            <div class="mt-2 text-danger small">
                                <i class="fas fa-arrow-down"></i> -2% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h3><?php echo $prestatairesActifs; ?></h3>
                            <p class="text-muted mb-0">Prestataires actifs</p>
                            <div class="mt-2 text-success small">
                                <i class="fas fa-arrow-up"></i> +10% depuis le mois dernier
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="row">
                    <!-- Revenue Chart -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Revenus mensuels (K€)</h5>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active">Revenus</button>
                                    <button type="button" class="btn btn-outline-secondary">Dépenses</button>
                                    <button type="button" class="btn btn-outline-secondary">Marge</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Leads and Activities -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Événements à venir</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Conférence Bien-être</h6>
                                            <small class="text-muted">Aujourd'hui</small>
                                        </div>
                                        <p class="mb-1 small">Paris 1er, 14h00 - 16h00</p>
                                        <small class="text-muted">15 participants inscrits</small>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Webinar Stress Management</h6>
                                            <small class="text-muted">Demain</small>
                                        </div>
                                        <p class="mb-1 small">En ligne, 10h00 - 11h30</p>
                                        <small class="text-muted">28 participants inscrits</small>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Atelier Yoga</h6>
                                            <small class="text-muted">03/03/2025</small>
                                        </div>
                                        <p class="mb-1 small">Paris 9ème, 12h30 - 13h30</p>
                                        <small class="text-muted">12 participants inscrits</small>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Course solidaire</h6>
                                            <small class="text-muted">05/03/2025</small>
                                        </div>
                                        <p class="mb-1 small">Troyes, 9h00 - 12h00</p>
                                        <small class="text-muted">35 participants inscrits</small>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="#" class="btn btn-sm btn-primary">Voir tous les événements</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row -->
                <div class="row">
                    <!-- Recent Contracts -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Contrats récents</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                        Filtrer
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <li><a class="dropdown-item" href="#">Tous</a></li>
                                        <li><a class="dropdown-item" href="#">Starter</a></li>
                                        <li><a class="dropdown-item" href="#">Basic</a></li>
                                        <li><a class="dropdown-item" href="#">Premium</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Société</th>
                                                <th>Plan</th>
                                                <th>Date signature</th>
                                                <th>Valeur (€)</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>TechInnov</td>
                                                <td>Premium</td>
                                                <td>02/03/2025</td>
                                                <td>45,000</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                            </tr>
                                            <tr>
                                                <td>EcoSolutions</td>
                                                <td>Basic</td>
                                                <td>01/03/2025</td>
                                                <td>28,500</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                            </tr>
                                            <tr>
                                                <td>DigitalWave</td>
                                                <td>Basic</td>
                                                <td>28/02/2025</td>
                                                <td>22,500</td>
                                                <td><span class="badge bg-warning">En attente</span></td>
                                            </tr>
                                            <tr>
                                                <td>GreenLife</td>
                                                <td>Starter</td>
                                                <td>25/02/2025</td>
                                                <td>5,400</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                            </tr>
                                            <tr>
                                                <td>SmartRetail</td>
                                                <td>Premium</td>
                                                <td>20/02/2025</td>
                                                <td>87,500</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-sm btn-primary">Voir tous les contrats</a>
                            </div>
                        </div>
                    </div>

                    <!-- Top Rated Providers -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Prestataires les mieux notés</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                        Par catégorie
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                        <li><a class="dropdown-item" href="#">Tous</a></li>
                                        <li><a class="dropdown-item" href="#">Santé mentale</a></li>
                                        <li><a class="dropdown-item" href="#">Sport & Bien-être</a></li>
                                        <li><a class="dropdown-item" href="#">Conférenciers</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="/api/placeholder/50/50" alt="Avatar" class="user-avatar me-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Sophie Martin</h6>
                                                <p class="mb-0 small text-muted">Psychologue</p>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">4.9</span>
                                                    <div>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                    </div>
                                                </div>
                                                <small class="text-muted">142 séances</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="/api/placeholder/50/50" alt="Avatar" class="user-avatar me-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Laurent Dubois</h6>
                                                <p class="mb-0 small text-muted">Coach sportif</p>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">4.8</span>
                                                    <div>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                    </div>
                                                </div>
                                                <small class="text-muted">97 séances</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="/api/placeholder/50/50" alt="Avatar" class="user-avatar me-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Émilie Bernard</h6>
                                                <p class="mb-0 small text-muted">Nutritionniste</p>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">4.8</span>
                                                    <div>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                    </div>
                                                </div>
                                                <small class="text-muted">84 séances</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="/api/placeholder/50/50" alt="Avatar" class="user-avatar me-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Thomas Leroy</h6>
                                                <p class="mb-0 small text-muted">Conférencier</p>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">4.7</span>
                                                    <div>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    </div>
                                                </div>
                                                <small class="text-muted">32 conférences</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="/api/placeholder/50/50" alt="Avatar" class="user-avatar me-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Julie Moreau</h6>
                                                <p class="mb-0 small text-muted">Prof de yoga</p>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">4.7</span>
                                                    <div>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star text-warning"></i>
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    </div>
                                                </div>
                                                <small class="text-muted">68 séances</small>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-sm btn-primary">Voir tous les prestataires</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Third Row -->
                <div class="row">
                    <!-- Performance Metrics -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Performance par formule</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Premium</span>
                                        <span>68%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 68%" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Basic</span>
                                        <span>45%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Starter</span>
                                        <span>22%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 22%" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Le plan Premium représente 68% du chiffre d'affaires total ce mois-ci.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Activités récentes</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Aujourd'hui, 10:45</small>
                                        </div>
                                        <p class="mb-1">Nouveau contrat signé avec <strong>TechInnov</strong></p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Aujourd'hui, 09:30</small>
                                        </div>
                                        <p class="mb-1">Facturation mensuelle envoyée à 34 clients</p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Hier, 16:20</small>
                                        </div>
                                        <p class="mb-1">Nouveau prestataire ajouté : <strong>Marc Durand</strong> (Coach mental)</p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">Hier, 14:15</small>
                                        </div>
                                        <p class="mb-1">Événement créé : <strong>Webinar Gestion du Stress</strong></p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">28/02/2025, 11:05</small>
                                        </div>
                                        <p class="mb-1">Mise à jour du contrat avec <strong>EcoSolutions</strong></p>
                                    </div>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="text-muted">27/02/2025, 15:30</small>
                                        </div>
                                        <p class="mb-1">Paiement reçu de <strong>DigitalWave</strong> (22,500€)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-sm btn-primary">Voir toutes les activités</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fourth Row -->
                <div class="row">
                    <!-- Sales by Region -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Répartition des clients par région</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="regionChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Services Usage -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Services les plus utilisés</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Ateliers bien-être</span>
                                        <span>78%</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>Conférences thématiques</span>
                                            <span>65%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>Cours de yoga</span>
                                            <span>52%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 52%" aria-valuenow="52" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>Consultations psy</span>
                                            <span>48%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 48%" aria-valuenow="48" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>Team building</span>
                                            <span>34%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 34%" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100"></div>
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
                                    <i class="fas fa-user-plus fa-2x text-primary"></i>
                                </div>
                                <h6>Nouveau client</h6>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-2">Ajouter</a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center p-3 mb-3">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-plus fa-2x text-success"></i>
                                </div>
                                <h6>Nouvel événement</h6>
                                <a href="#" class="btn btn-sm btn-outline-success mt-2">Créer</a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center p-3 mb-3">
                                <div class="mb-3">
                                    <i class="fas fa-file-invoice fa-2x text-warning"></i>
                                </div>
                                <h6>Nouvelle facture</h6>
                                <a href="#" class="btn btn-sm btn-outline-warning mt-2">Générer</a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center p-3 mb-3">
                                <div class="mb-3">
                                    <i class="fas fa-chart-pie fa-2x text-info"></i>
                                </div>
                                <h6>Rapport analytique</h6>
                                <a href="#" class="btn btn-sm btn-outline-info mt-2">Exporter</a>
                            </div>
                        </div>
                    </div>
            </main>
        </div>
    </div>


</body>

</html>
