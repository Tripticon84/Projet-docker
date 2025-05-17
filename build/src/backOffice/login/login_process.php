<?php

// session_start();
// if ($_SESSION) {
//     header("location: ../home.php");
//     exit();
// }

function error($message) {
    header("location: ../index.php" . "?message=" . $message);
    exit();
}


include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";


// verifier si la methode est post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error("Erreur la méthode n'est pas POST");
}

// Valider les paramètres
$required = ['username', 'password'];
if (!validateMandatoryParams($_POST, $required)) {
    error("Les paramètres ne sont pas les bons.");
}

// Connexion a la base de données
$db = getDatabaseConnection();

$username = $_POST['username'];
$salt = 'quoicoube';
$password_salt = $_POST['password'] . $salt;
$password_hash = hash("sha256", $password_salt);


$q = "SELECT admin_id, username, password FROM admin WHERE username = :username AND password = :password";
$req = $db->prepare($q);
$req->execute([
    'username' => $username,
    'password' => $password_hash
]);

// Modification ici : utilisation de fetch() au lieu de fetchAll()
$result = $req->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    error("Identifiants inconnus");
}

// Si on arrive ici, c'est que tout est OK
// Connexion

session_start();

$_SESSION["admin_id"] = $result["admin_id"];
$_SESSION["username"] = $result["username"];

// Login dans l'api pour récupérer le token
$postData = [
    'username' => $username,
    'password' => $_POST['password']
];
$ch = curl_init('localhost/api/admin/login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (isset($data['token'])) {
    $_SESSION['admin_token'] = $data['token'];
    $expirationTimestamp = time() + 3600*2;
    setcookie('admin_token', $data['token'], $expirationTimestamp, '/');
}

// Redirection vers la page d'accueil
header("location:../home.php");
exit();
