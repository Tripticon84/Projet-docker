<?php

function error($message) {
    header("location: login.php" . "?message=" . $message);
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/employee.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/hashPassword.php";

// Vérifier si la méthode est POST
if (!methodIsAllowed('login')) {
    error("Erreur la méthode n'est pas POST");
}

// Valider les paramètres
if (!validateMandatoryParams($_POST, ['email', 'password'])) {
    error("Les paramètres ne sont pas les bons.");
}

// Connexion à la base de données
$db = getDatabaseConnection();

$email = $_POST['email'];
$password = $_POST['password'];

// Récupérer l'utilisateur par son email
$q = "SELECT * FROM collaborateur WHERE email = :email AND desactivate = 0";
$req = $db->prepare($q);
$req->execute([
    'email' => $email
]);

$result = $req->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    error("Identifiants inconnus");
}

// Vérifier les identifiants via findEmployeeByCredentials
$credentialCheck = findEmployeeByCredentials($result['username'], $password);
if (!$credentialCheck) {
    echo($credentialCheck);
    error("Identifiants incorrects");
}

// Si on arrive ici, c'est que tout est OK
// Connexion
session_start();

// Vérifier s'il n'y a pas déjà de session active
if (isset($_SESSION)) {
    // Si une session est déjà active, on la détruit
    session_destroy();
    session_start();
}

// Créer la session
$_SESSION["collaborateur_id"] = $result["collaborateur_id"];
$_SESSION["nom"] = $result["nom"];
$_SESSION["prenom"] = $result["prenom"];
$_SESSION["username"] = $result["username"];
$_SESSION["telephone"] = $result["telephone"];
$_SESSION["email"] = $result["email"];
$_SESSION["role"] = $result["role"] ?? "collaborateur";
$_SESSION["id_societe"] = $result["id_societe"];

// Mettre à jour la date d'activité sans utiliser bindParam
$update_query = "UPDATE collaborateur SET date_activite = NOW() WHERE collaborateur_id = :id";
$update_stmt = $db->prepare($update_query);
$update_stmt->execute([
    ':id' => $result['collaborateur_id']
]);

// Login dans l'API pour récupérer le token
$postData = [
    'email' => $email,
    'password' => $_POST['password']
];
$ch = curl_init('localhost/api/employee/login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['token'])) {
    $_SESSION['collaborateur_token'] = $data['token'];
    $expirationTimestamp = time() + 3600 * 24; // 24 heures
    setcookie('collaborateur_token', $data['token'], $expirationTimestamp, '/');
}

// Redirection vers la page d'accueil
header("location:../index.php");
exit();
