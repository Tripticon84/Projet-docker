<?php
$title = "Mes Factures";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';

// Vérifier si une session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prestataire_id'])) {
    header("Location: login/login.php?message=Veuillez vous connecter.");
    exit();
}

$prestataireId = $_SESSION['prestataire_id'];
$factures = getProviderInvoices($prestataireId);


$facturesEnAttente = [];
$facturesPayees = [];
$facturesAnnulees = [];

foreach ($factures as $facture) {
    switch ($facture['statut']) {
        case 'Attente':
            $facturesEnAttente[] = $facture;
            break;
        case 'Payee':
            $facturesPayees[] = $facture;
            break;
        case 'Annulee':
            $facturesAnnulees[] = $facture;
            break;
    }
}


function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}


function formatMontant($montant) {
    return number_format($montant, 2, ',', ' ') . ' €';
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Mes Factures</h1>
                    <p class="card-text">Consultez et gérez vos factures.</p>
                </div>
            </div>
        </div>
    </div>

   
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="card-title">En attente</h5>
                    <h2><?= count($facturesEnAttente) ?></h2>
                    <p class="card-text">Factures en attente de paiement</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Payées</h5>
                    <h2><?= count($facturesPayees) ?></h2>
                    <p class="card-text">Factures réglées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Annulées</h5>
                    <h2><?= count($facturesAnnulees) ?></h2>
                    <p class="card-text">Factures annulées</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h4>Factures en attente</h4>
        </div>
        <div class="card-body">
            <?php if (empty($facturesEnAttente)): ?>
                <div class="alert alert-info">Aucune facture en attente.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Date d'émission</th>
                                <th>Date d'échéance</th>
                                <th>Société</th>
                                <th>Montant HT</th>
                                <th>TVA</th>
                                <th>Montant TTC</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturesEnAttente as $facture): ?>
                                <tr>
                                    <td>FACT-<?= $facture['facture_id'] ?></td>
                                    <td><?= formatDate($facture['date_emission']) ?></td>
                                    <td><?= formatDate($facture['date_echeance']) ?></td>
                                    <td><?= htmlspecialchars($facture['nom_societe'] ?? 'N/A') ?></td>
                                    <td><?= formatMontant($facture['montant_ht']) ?></td>
                                    <td><?= formatMontant($facture['montant_tva']) ?></td>
                                    <td><?= formatMontant($facture['montant']) ?></td>
                                    <td>
                                        <?php if ($facture['fichier']): ?>
                                            <a href="/uploads/factures/<?= $facture['fichier'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pas de PDF</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4>Factures payées</h4>
        </div>
        <div class="card-body">
            <?php if (empty($facturesPayees)): ?>
                <div class="alert alert-info">Aucune facture payée.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Date d'émission</th>
                                <th>Date de paiement</th>
                                <th>Société</th>
                                <th>Montant TTC</th>
                                <th>Méthode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturesPayees as $facture): ?>
                                <tr>
                                    <td>FACT-<?= $facture['facture_id'] ?></td>
                                    <td><?= formatDate($facture['date_emission']) ?></td>
                                    <td><?= formatDate($facture['date_echeance']) ?></td>
                                    <td><?= htmlspecialchars($facture['nom_societe'] ?? 'N/A') ?></td>
                                    <td><?= formatMontant($facture['montant']) ?></td>
                                    <td>
                                        <?php if ($facture['methode_paiement']): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($facture['methode_paiement']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Non spécifié</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($facture['fichier']): ?>
                                            <a href="/uploads/factures/<?= $facture['fichier'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pas de PDF</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

   
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h4>Factures annulées</h4>
        </div>
        <div class="card-body">
            <?php if (empty($facturesAnnulees)): ?>
                <div class="alert alert-info">Aucune facture annulée.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Date d'émission</th>
                                <th>Société</th>
                                <th>Montant TTC</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturesAnnulees as $facture): ?>
                                <tr>
                                    <td>FACT-<?= $facture['facture_id'] ?></td>
                                    <td><?= formatDate($facture['date_emission']) ?></td>
                                    <td><?= htmlspecialchars($facture['nom_societe'] ?? 'N/A') ?></td>
                                    <td><?= formatMontant($facture['montant']) ?></td>
                                    <td>
                                        <?php if ($facture['fichier']): ?>
                                            <a href="/uploads/factures/<?= $facture['fichier'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pas de PDF</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
