<?php
session_start();


if (isset($_SESSION['prestataire_id'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
    $db = getDatabaseConnection();

    $query = "UPDATE prestataire SET date_activite = NOW() WHERE prestataire_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_SESSION['prestataire_id']);
    $stmt->execute();
}


$_SESSION = [];

session_destroy();


if (isset($_COOKIE['prestataire_token'])) {
    setcookie('prestataire_token', '', time() - 3600, '/');
}

header("Location: login.php");
exit();
?>
