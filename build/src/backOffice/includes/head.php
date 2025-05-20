<?php

session_start();
if (!isset($_SESSION["admin_id"]) && $title != "Connexion") {
    header("location: /backOffice/index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Care - <?= $title ?></title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Récupérer le token JWT depuis le cookie -->
    <script>
        const getToken = () => {
            const cookies = document.cookie.split('; ');
            const tokenCookie = cookies.find(row => row.startsWith('admin_token='));
            return tokenCookie ? tokenCookie.split('=')[1] : null;
        };

        if (getToken() === null && !document.title.includes("Connexion")) {
            alert("Vous devez vous connecter pour accéder à cette page.");
            window.location.href = "/backOffice/login/logout.php";
        }


        document.addEventListener('hidden.bs.modal', function() {
            // Attendre un court délai pour s'assurer que l'événement de fermeture est terminé
            setTimeout(function() {
                // Si aucune modal n'est visible, nettoyer toutes les backdrops
                if (!document.querySelector('.modal.show')) {
                    // Supprimer les backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                        backdrop.remove();
                    });
                    // Supprimer les classes et styles ajoutés au body
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            }, 150);
        });
    </script>
    <style>
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #8338ec;
            --success-color: #38b000;
            --warning-color: #ffbe0b;
            --danger-color: #ff006e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        .sidebar {
            min-height: 100vh;
            background-color: var(--dark-color);
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 0.25rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1.5rem;
            text-align: center;
        }

        .main-content {
            padding: 1.5rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem;
            font-weight: 500;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 50%;
            width: 4rem;
            height: 4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .calendar-event {
            border-left: 4px solid var(--primary-color);
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }

        .progress {
            height: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
        }

        .notification-badge {
            position: absolute;
            top: 0.5rem;
            right: 1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
