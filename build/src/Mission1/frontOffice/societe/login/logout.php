<?php

session_start();
session_unset();
session_destroy();

// Suprimer le cookie token
if (isset($_COOKIE['societe_token'])) {
    setcookie('societe_token', '', time() - 3600, '/');
}

header("Location: /frontoffice/societe/login/login.php");
exit();
