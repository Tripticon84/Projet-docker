<?php
$title = "Profil de la société";
include_once "../includes/head.php";
include_once "../../api/dao/company.php";

// Récupération de l'ID de la société depuis l'URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupération des détails de la société
$company = getSociety($id);
if (!$company) {
    header("Location: company.php");
    exit;
}

// Récupération des employés de la société
$employees = getSocietyEmployees($id);

// Récupération des devis (non-contrats)
$estimates = getCompanyEstimate($id, 0);

// Récupération des contrats
$contracts = getCompanyEstimate($id, 1);

// Récupération des factures
$invoices = getCompanyInvoices($id, 5);
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php" ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Profil de la société</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="modify.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="company.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Informations de la société -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informations de la société</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Nom</span>
                                            <h6><?php echo htmlspecialchars($company['nom'] ?? 'Non renseigné'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Email</span>
                                            <h6><?php echo htmlspecialchars($company['email'] ?? 'Non renseigné'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Téléphone</span>
                                            <h6><?php echo htmlspecialchars($company['telephone'] ?? 'Non renseigné'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Personne de contact</span>
                                            <h6><?php echo htmlspecialchars($company['contact_person'] ?? 'Non renseigné'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">SIRET</span>
                                            <h6><?php echo htmlspecialchars($company['siret'] ?? 'Non renseigné'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Date de création</span>
                                            <h6><?php echo $company['date_creation'] ? date('d/m/Y H:i', strtotime($company['date_creation'])) : 'Non renseignée'; ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Adresse</span>
                                            <h6><?php echo htmlspecialchars($company['adresse'] ?? 'Non renseignée'); ?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collaborateurs de la société -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Collaborateurs</h5>
                                <span class="badge bg-primary"><?php echo count($employees); ?> collaborateurs</span>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($employees)): ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted mb-0">Aucun collaborateur trouvé</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nom</th>
                                                    <th>Prénom</th>
                                                    <th>Username</th>
                                                    <th>Rôle</th>
                                                    <th>Email</th>
                                                    <th>Téléphone</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($employees as $employee): ?>
                                                    <tr>
                                                        <td><?php echo $employee['collaborateur_id']; ?></td>
                                                        <td><?php echo htmlspecialchars($employee['nom'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['prenom'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['username'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['role'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['email'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['telephone'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devis & Contrats -->
                <div class="row">
                    <!-- Devis -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Devis</h5>
                                <span class="badge bg-info"><?php echo count($estimates); ?> devis</span>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($estimates)): ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted mb-0">Aucun devis trouvé</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date début</th>
                                                    <th>Date fin</th>
                                                    <th>Statut</th>
                                                    <th>Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($estimates as $estimate): ?>
                                                    <tr>
                                                        <td><?php echo $estimate['devis_id']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($estimate['date_debut'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($estimate['date_fin'])); ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = '';
                                                            $status_text = '';
                                                            switch ($estimate['statut']) {
                                                                case 0: $status_class = 'bg-warning'; $status_text = 'En attente'; break;
                                                                case 1: $status_class = 'bg-success'; $status_text = 'Accepté'; break;
                                                                case 2: $status_class = 'bg-danger'; $status_text = 'Refusé'; break;
                                                                default: $status_class = 'bg-secondary'; $status_text = 'Inconnu';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                        </td>
                                                        <td><?php echo number_format($estimate['montant'], 2, ',', ' '); ?> €</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contrats -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Contrats</h5>
                                <span class="badge bg-success"><?php echo count($contracts); ?> contrats</span>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($contracts)): ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted mb-0">Aucun contrat trouvé</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date début</th>
                                                    <th>Date fin</th>
                                                    <th>Statut</th>
                                                    <th>Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contracts as $contract): ?>
                                                    <tr>
                                                        <td><?php echo $contract['devis_id']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($contract['date_debut'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($contract['date_fin'])); ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = '';
                                                            $status_text = '';
                                                            switch ($contract['statut']) {
                                                                case 0: $status_class = 'bg-warning'; $status_text = 'En attente'; break;
                                                                case 1: $status_class = 'bg-success'; $status_text = 'Actif'; break;
                                                                case 2: $status_class = 'bg-danger'; $status_text = 'Terminé'; break;
                                                                default: $status_class = 'bg-secondary'; $status_text = 'Inconnu';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                        </td>
                                                        <td><?php echo number_format($contract['montant'], 2, ',', ' '); ?> €</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Factures -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Factures récentes</h5>
                                <span class="badge bg-primary"><?php echo count($invoices); ?> factures</span>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($invoices)): ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted mb-0">Aucune facture trouvée</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date émission</th>
                                                    <th>Date échéance</th>
                                                    <th>Montant HT</th>
                                                    <th>TVA</th>
                                                    <th>Montant TTC</th>
                                                    <th>Statut</th>
                                                    <th>Méthode paiement</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($invoices as $invoice): ?>
                                                    <tr>
                                                        <td><?php echo $invoice['facture_id']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($invoice['date_emission'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($invoice['date_echeance'])); ?></td>
                                                        <td><?php echo number_format($invoice['montant_ht'], 2, ',', ' '); ?> €</td>
                                                        <td><?php echo number_format($invoice['montant_tva'], 2, ',', ' '); ?> €</td>
                                                        <td><?php echo number_format($invoice['montant'], 2, ',', ' '); ?> €</td>
                                                        <td>
                                                            <?php
                                                            $status_class = '';
                                                            $status_text = '';
                                                            switch ($invoice['statut']) {
                                                                case 0: $status_class = 'bg-warning'; $status_text = 'En attente'; break;
                                                                case 1: $status_class = 'bg-success'; $status_text = 'Payée'; break;
                                                                case 2: $status_class = 'bg-danger'; $status_text = 'En retard'; break;
                                                                case 3: $status_class = 'bg-secondary'; $status_text = 'Annulée'; break;
                                                                default: $status_class = 'bg-secondary'; $status_text = 'Inconnu';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($invoice['methode_paiement'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <style>
        .stat-card-mini {
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>
</body>
</html>
