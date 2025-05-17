<?php
$title = "Profil de l'employé";
include_once "../includes/head.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

// Vérifier si l'ID est fourni dans l'URL
$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($employeeId <= 0) {
    header('Location: /backOffice/employee/employee.php');
    exit;
}

// Récupérer toutes les informations
$employee = getEmployeeProfile($employeeId);
$activities = getEmployeeActivities($employeeId);
$events = getEmployeeEvents($employeeId);
$chats = getEmployeeChats($employeeId);
$associations = getEmployeeAssociations($employeeId);
$evaluations = getEmployeeEvaluations($employeeId);

if (!$employee) {
    header('Location: /backOffice/employee/employee.php');
    exit;
}
?>

<body class="container mt-5">
    <a href="employee.php" class="btn btn-secondary mb-3">&larr; Retour</a>

    <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Profil de l'employé</h2>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nom</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['nom']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Prénom</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['prenom']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Rôle</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['role']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['email']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Téléphone</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['telephone']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de création</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['date_creation']); ?></p>
                </div>
                <div class="mb-3"></div>
                    <label class="form-label fw-bold">Dernière activité</label>
                    <p class="form-control-static"><?php echo htmlspecialchars($employee['date_activite']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Activités -->
    <div class="card p-4 shadow-sm mb-4">
        <h3>Activités</h3>
        <?php if ($activities): ?>
            <ul class="list-group">
                <?php foreach ($activities as $activity): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($activity['nom']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune activité trouvée</p>
        <?php endif; ?>
    </div>

    <!-- Événements -->
    <div class="card p-4 shadow-sm mb-4">
        <h3>Événements</h3>
        <?php if ($events): ?>
            <ul class="list-group">
                <?php foreach ($events as $event): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($event['nom']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun événement trouvé</p>
        <?php endif; ?>
    </div>

    <!-- Salons de discussion -->
    <div class="card p-4 shadow-sm mb-4">
        <h3>Salons de discussion</h3>
        <?php if ($chats): ?>
            <ul class="list-group">
                <?php foreach ($chats as $chat): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($chat['nom']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun salon trouvé</p>
        <?php endif; ?>
    </div>

    <!-- Associations -->
    <div class="card p-4 shadow-sm mb-4">
        <h3>Associations</h3>
        <?php if ($associations): ?>
            <ul class="list-group">
                <?php foreach ($associations as $association): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($association['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune association trouvée</p>
        <?php endif; ?>
    </div>

    <!-- Évaluations -->
    <div class="card p-4 shadow-sm mb-4">
        <h3>Évaluations</h3>
        <?php if ($evaluations): ?>
            <ul class="list-group">
                <?php foreach ($evaluations as $evaluation): ?>
                    <li class="list-group-item">
                        <div>Note: <?php echo htmlspecialchars($evaluation['note']); ?>/5</div>
                        <div>Commentaire: <?php echo htmlspecialchars($evaluation['commentaire']); ?></div>
                        <div>Date: <?php echo htmlspecialchars($evaluation['date_creation']); ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune évaluation trouvée</p>
        <?php endif; ?>
    </div>
</body>
</html></div></ul>
