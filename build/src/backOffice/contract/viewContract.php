<?php
$title = "Détails du Contrat";
include_once "../includes/head.php";
include_once "../../api/dao/estimate.php";

// Vérification de l'ID dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: contract.php");
    exit;
}

$contractId = intval($_GET['id']);
$contractDetails = getContractDetailsById($contractId);

if (!$contractDetails) {
    header("Location: contract.php");
    exit;
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
                    <h1 class="h2">Détails du Contrat</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="contract.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Informations du contrat -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informations du Contrat</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">ID</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['devis_id']); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Date de début</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['date_debut']); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Date de fin</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['date_fin']); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Montant Total</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['montant']); ?> €</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Montant HT</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['montant_ht']); ?> €</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Montant TVA</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['montant_tva']); ?> €</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Société</span>
                                            <h6><?php echo htmlspecialchars($contractDetails['id_societe']); ?></h6>
                                        </div>
                                    </div>
                                </div>
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