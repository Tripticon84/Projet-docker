<?php
// Vérifier si head.php n'a pas déjà été inclus
if (!defined('HEAD_INCLUDED')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/head.php';
}
?>

<header>
    <!-- Barre de navigation principale avec dégradé de couleur -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%);">
        <div class="container">
            <!-- Logo et nom de l'application -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="/data/static/logo.png" alt="Business Care" class="me-2" height="40">
                <span class="fw-bold">Espace Prestataire</span>
            </a>

            <!-- Bouton menu mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu principal -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded-pill mx-1" href="index.php">
                            <i class="fas fa-home me-1"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded-pill mx-1" href="activites.php">
                            <i class="fas fa-calendar-check me-1"></i> Mes Activités
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded-pill mx-1" href="planning.php">
                            <i class="fas fa-calendar-alt me-1"></i> Planning
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded-pill mx-1" href="factures.php">
                            <i class="fas fa-file-invoice me-1"></i> Factures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded-pill mx-1" href="disponibilites.php">
                            <i class="fas fa-clock me-2 text-success"></i> Mes Disponibilités
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 rounded-pill mx-1" href="evaluations.php">
                            <i class="fas fa-star me-2 text-warning"></i> Mes Évaluations
                        </a>
                    </li>
                </ul>

                <!-- Menu utilisateur -->
                <ul class="navbar-nav">
                    <!-- Profil utilisateur -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2 d-none d-lg-block text-end">
                                <span class="d-block fw-bold"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></span>
                                <small class="text-light opacity-75"><?= $_SESSION['type'] ?></small>
                            </div>
                            <div class="bg-light rounded-circle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <?php echo substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1); ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown">
                            <li>
                                <div class="px-3 py-2 text-muted">
                                    <div class="mb-2">Connecté en tant que</div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['email']); ?></h6>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-id-card me-2 text-primary"></i> Mon Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="fas fa-cog me-2 text-secondary"></i> Paramètres
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="login/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Système de notification toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="notificationContainer" style="z-index: 1100;"></div>

<!-- Styles pour animer le dropdown et arrondir les éléments du menu -->
<style>
    /* Fond de page avec dégradé subtil */
    body {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        min-height: 100vh;
    }

    /* Effet hover sur les liens de navigation */
    .navbar-dark .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    /* Animation du dropdown */
    .dropdown-menu-animated {
        animation: dropdownFade 0.2s ease-in-out;
        transform-origin: top center;
    }

    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Style des notifications */
    #notificationsDropdown .badge {
        font-size: 0.65rem;
    }

    /* Styles pour les cartes et conteneurs */
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }

    /* Style pour les boutons primaires */
    .btn-primary {
        background: linear-gradient(135deg, #3a7bd5 0%, #6d5b98 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2a5b9f 0%, #564a79 100%);
    }
</style>
