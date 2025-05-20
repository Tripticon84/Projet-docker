<?php
// La session est déjà démarrée dans head.php
$title = "Création de conseil non autorisée";
include_once "../includes/head.php";

// Vérification de la session après inclusion de head.php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Création de conseil</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a type="button" class="btn btn-sm btn-secondary" href="advice.php">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Action non autorisée
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h4 class="alert-heading">Création de conseil non autorisée!</h4>
                            <p>Seuls les collaborateurs peuvent créer des demandes de conseil. Les administrateurs peuvent uniquement répondre, modifier ou supprimer les conseils existants.</p>
                            <hr>
                            <p class="mb-0">Pour consulter les conseils existants, veuillez retourner à la <a href="advice.php" class="alert-link">liste des conseils</a>.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
