<?php

function error($message) {
    header("location: login.php" . "?message=" . $message);
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/provider.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/hashPassword.php";


if (!methodIsAllowed('login')) {
    error("Erreur la méthode n'est pas POST");
}

if (!validateMandatoryParams($_POST, ['email', 'password'])) {
    error("Les paramètres ne sont pas les bons.");
}

$db = getDatabaseConnection();

$email = $_POST['email'];
$password = $_POST['password'];


$q = "SELECT * FROM prestataire WHERE email = :email AND desactivate = 0";
$req = $db->prepare($q);
$req->execute([
    'email' => $email
]);

$result = $req->fetch(PDO::FETCH_ASSOC);

// Debug : Vérifier si l'email existe
if (!$result) {
    error("Identifiants inconnus. Email non trouvé ou désactivé.");
}

$passwordHashed = hashPassword($password);
$credentialCheck = findProviderByCredentials($email, $passwordHashed);

if (!$credentialCheck) {
    error("Identifiants incorrects. Mot de passe invalide.");
}


if ($result["est_candidat"] == 1) {
   
    session_start();
    $_SESSION['registration_success'] = true;
    $_SESSION['provider_email'] = $result['email'];
    $_SESSION['waiting_approval'] = true;
    
   
    header("Location: ../register/confirmation.php");
    exit();
}


session_start();


if (isset($_SESSION)) {
    
    session_destroy();
    session_start();
}


$_SESSION["prestataire_id"] = $result["prestataire_id"];
$_SESSION["nom"] = $result["nom"];
$_SESSION["prenom"] = $result["prenom"];
$_SESSION["email"] = $result["email"];
$_SESSION["telephone"] = $result["telephone"] ?? null;
$_SESSION["type"] = $result["type"] ?? "prestataire";
$_SESSION["description"] = $result["description"] ?? null;
$_SESSION["tarif"] = $result["tarif"] ?? null;


$postData = [
    'email' => $email, 
    'password' => $password
];
$ch = curl_init('http://localhost/api/provider/login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    error("Erreur API : " . curl_error($ch)); 
}

curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['token'])) {
    error("Erreur lors de la récupération du token");
}

if (isset($data['token'])) {
    $_SESSION['prestataire_token'] = $data['token'];
    $expirationTimestamp = time() + 3600 * 24; 
    setcookie('prestataire_token', $data['token'], $expirationTimestamp, '/');
}

header("location:../index.php");
exit();
