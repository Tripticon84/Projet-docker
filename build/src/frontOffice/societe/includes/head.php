<?php

session_start();
// if (!isset($_SESSION["societe_token"]) && $title != "Connexion" && strpos($title, "Inscription") === false) {
//     header("location: /frontOffice/societe/login/logout.php");
//     exit();
// }

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

    <script src="/data/static/js/societe.js"></script>

    <!-- Récupérer le token JWT depuis le cookie -->
    <script>
        const getToken = () => {
            const cookies = document.cookie.split('; ');
            const tokenCookie = cookies.find(row => row.startsWith('societe_token='));
            return tokenCookie ? tokenCookie.split('=')[1] : null;
        };

        // if (getToken() === null && !document.title.includes("Connexion") && !document.title.includes("Inscription")) {
        //     alert("Vous devez vous connecter pour accéder à cette page.");
        //     window.location.href = "/frontOffice/societe/login/logout.php";
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
