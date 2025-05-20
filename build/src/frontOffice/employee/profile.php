<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
// La ligne session_start() est supprimée car déjà présente dans head.php

// Inclure l'en-tête
require_once 'includes/head.php';


if (!isset($_SESSION['collaborateur_id'])) {
    header('Location: /login.php');
    exit;
}

$collaborateurId = $_SESSION['collaborateur_id'];
$profile = getEmployeeProfile($collaborateurId);

if (!$profile) {
    header('Location: /error.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - BusinessCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 60px;
            border: 4px solid white;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/header.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="profile-img">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="col">
                    <h1 class="mb-0"><?php echo htmlspecialchars($profile['prenom'] . ' ' . $profile['nom']); ?></h1>
                    <p class="mb-0"><i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($profile['role']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Informations personnelles</h4>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
                <form id="profileForm" onsubmit="updateProfile(event)">
                    <input type="hidden" id="collaborateur_id" value="<?php echo $profile['collaborateur_id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" value="<?php echo htmlspecialchars($profile['nom']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" value="<?php echo htmlspecialchars($profile['prenom']); ?>" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($profile['email']); ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" value="<?php echo htmlspecialchars($profile['telephone']); ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['role']); ?>" disabled>
                    </div>

                    <div id="editButtons" style="display: none;">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/footer.php'; ?>
    
    <script src="/data/static/js/employee.js"></script>
</body>
</html>
