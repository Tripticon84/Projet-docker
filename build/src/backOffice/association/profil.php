<?php
$title = "Profil de l'association";
include_once "../includes/head.php";
include_once "../../api/dao/association.php";

// Récupération de l'ID de l'association depuis l'URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupération des détails de l'association
$association = getAssociationById($id);
if (!$association) {
    header("Location: association.php");
    exit;
}

// Récupération des employés de l'association
$employees = getEmployeesByAssociation($id);
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php" ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Profil de l'association</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="association.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Informations de l'association -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informations de l'association</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Nom</span>
                                            <h6><?php echo htmlspecialchars($association['name'] ?? 'Non renseigné'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Description</span>
                                            <h6><?php echo htmlspecialchars($association['description'] ?? 'Non renseignée'); ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Date de création</span>
                                            <h6><?php echo $association['date_creation'] ? date('d/m/Y H:i', strtotime($association['date_creation'])) : 'Non renseignée'; ?></h6>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Logo</span>
                                            <img src="<?php echo htmlspecialchars($association['logo'] ?? ''); ?>" alt="Logo" class="img-fluid">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="stat-card-mini">
                                            <span class="text-muted">Bannière</span>
                                            <img src="<?php echo htmlspecialchars($association['banniere'] ?? ''); ?>" alt="Bannière" class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collaborateurs de l'association -->
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
                                                    <th>Email</th>
                                                    <th>Rôle</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($employees as $employee): ?>
                                                    <tr>
                                                        <td><?php echo $employee['collaborateur_id']; ?></td>
                                                        <td><?php echo htmlspecialchars($employee['nom'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['prenom'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['email'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['role'] ?? '-'); ?></td>
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