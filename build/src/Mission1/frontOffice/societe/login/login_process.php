<?php

session_start();
if ($_SESSION['societe_id']) {
    header("location: ../home.php");
    exit();
}

function error($message) {
    header("location: login.php" . "?message=" . $message);
    exit();
}


include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/hashPassword.php";

// verifier si la methode est post
if (!methodIsAllowed('login')) {
    error("Erreur la méthode n'est pas POST");
}

// Valider les paramètres
if (!validateMandatoryParams($_POST, ['email', 'password'])) {
    error("Les paramètres ne sont pas les bons.");
}

// Connexion a la base de données
$db = getDatabaseConnection();

$email = $_POST['email'];
$password_hash = hashPassword($_POST['password']);

$q = "SELECT * FROM societe WHERE email = :email AND password = :password";
$req = $db->prepare($q);
$req->execute([
    'email' => $email,
    'password' => $password_hash
]);


$result = $req->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    error("Identifiants inconnus");
}


$_SESSION["employee_count"] = $result["employee_count"];
$_SESSION["plan"] = $result["plan"];
$_SESSION["societe_id"] = $result["societe_id"];
$_SESSION["email"] = $result["email"];
$_SESSION["societe_name"] = $result["nom"];


// Login dans l'api pour récupérer le token
$postData = [
    'email' => $email,
    'password' => $_POST['password']
];
$ch = curl_init('localhost/api/company/login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['token'])) {
    $_SESSION['societe_token'] = $data['token'];
    $expirationTimestamp = time() + 3600*24; // 24 heures
    setcookie('societe_token', $data['token'], $expirationTimestamp, '/');
}

// Redirection vers la page d'accueil
header("location:../home.php");
exit();
