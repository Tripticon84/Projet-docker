<?php
define('HEAD_INCLUDED', true);
session_start();

// Vérification de base de l'authentification
// if (!isset($_SESSION['collaborateur_id'])) {
//     header('Location: /frontOffice/employee/login/logout.php');
//     exit;
// }

// Ajouter le username dans la session s'il n'existe pas déjà
if (isset($_SESSION["collaborateur_id"]) && !isset($_SESSION["username"])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
    $collaborateur = getEmployeeProfile($_SESSION["collaborateur_id"]);
    if ($collaborateur) {
        $_SESSION["username"] = $collaborateur["username"];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Care - <?= $title ?? 'Espace Salarié' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/data/static/js/employee.js"></script>

    <!-- Récupérer le token JWT depuis le cookie -->
    <script>
        const getToken = () => {
            const cookies = document.cookie.split('; ');
            const tokenCookie = cookies.find(row => row.startsWith('collaborateur_token'));
            return tokenCookie ? tokenCookie.split('=')[1] : null;
        };

        // if (getToken() !== null && !document.title.includes("Connexion")) {
        //     alert("Vous devez vous connecter pour accéder à cette page.");
        //     window.location.href = "/frontOffice/employee/login/logout.php";
        // }

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
</head>
<body>
