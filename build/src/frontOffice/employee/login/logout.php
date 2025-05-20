<?php
session_start();

// Mettre à jour la date d'activité avant la déconnexion
if (isset($_SESSION['collaborateur_id'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
    $db = getDatabaseConnection();

    $query = "UPDATE collaborateur SET date_activite = NOW() WHERE collaborateur_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_SESSION['collaborateur_id']);
    $stmt->execute();
}

// Détruire toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Supprimer le cookie token
if (isset($_COOKIE['collaborateur_token'])) {
    setcookie('collaborateur_token', '', time() - 3600, '/');
}

// Rediriger vers la page de connexion
header("Location: login.php");
exit();
?>
