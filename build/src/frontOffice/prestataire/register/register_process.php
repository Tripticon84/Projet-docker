<?php
session_start();

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Récupérer et nettoyer les données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$type = trim($_POST['type'] ?? '');
$description = trim($_POST['description'] ?? '');
$tarif = floatval($_POST['tarif'] ?? 0);
$date_debut_disponibilite = $_POST['date_debut_disponibilite'] ?? '';
$date_fin_disponibilite = $_POST['date_fin_disponibilite'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';


$errors = [];

if (empty($nom)) {
    $errors[] = "Le nom est requis";
}

if (empty($prenom)) {
    $errors[] = "Le prénom est requis";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email est invalide";
}

if (empty($type)) {
    $errors[] = "Le type de service est requis";
}

if (empty($description)) {
    $errors[] = "La description est requise";
}

if ($tarif <= 0) {
    $errors[] = "Le tarif doit être supérieur à 0";
}

if (empty($date_debut_disponibilite)) {
    $errors[] = "La date de début de disponibilité est requise";
}

if (empty($date_fin_disponibilite)) {
    $errors[] = "La date de fin de disponibilité est requise";
}

if ($date_debut_disponibilite > $date_fin_disponibilite) {
    $errors[] = "La date de fin doit être après la date de début";
}

if (strlen($password) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
}

if ($password !== $confirm_password) {
    $errors[] = "Les mots de passe ne correspondent pas";
}


if (!empty($errors)) {
    $errors_json = urlencode(json_encode($errors));
    $form_data = urlencode(json_encode([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'type' => $type,
        'description' => $description,
        'tarif' => $tarif,
        'date_debut_disponibilite' => $date_debut_disponibilite,
        'date_fin_disponibilite' => $date_fin_disponibilite
    ]));
    header('Location: register.php?errors=' . $errors_json . '&form_data=' . $form_data);
    exit();
}

$apiUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api/provider/create.php';


$data = [
    'email' => $email,
    'nom' => $nom,
    'prenom' => $prenom,
    'type' => $type,
    'description' => $description,
    'tarif' => $tarif,
    'date_debut_disponibilite' => $date_debut_disponibilite,
    'date_fin_disponibilite' => $date_fin_disponibilite,
    'est_candidat' => true, 
    'password' => $password
];


$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);


if ($response === false) {
    $errorMessage = 'Erreur cURL: ' . curl_error($ch);
    $errors[] = "Erreur de communication avec le serveur. Veuillez réessayer ultérieurement.";
    error_log($errorMessage);
    $errors_json = urlencode(json_encode($errors));
    $form_data = urlencode(json_encode([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'type' => $type,
        'description' => $description,
        'tarif' => $tarif,
        'date_debut_disponibilite' => $date_debut_disponibilite,
        'date_fin_disponibilite' => $date_fin_disponibilite
    ]));
    header('Location: register.php?errors=' . $errors_json . '&form_data=' . $form_data);
    curl_close($ch);
    exit();
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);


if ($httpCode != 201) {
    $error_message = isset($responseData['error']) ? $responseData['error'] : 'Erreur lors de la création du prestataire.';
    $errors[] = $error_message;
    
    $errors_json = urlencode(json_encode($errors));
    $form_data = urlencode(json_encode([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'type' => $type,
        'description' => $description,
        'tarif' => $tarif,
        'date_debut_disponibilite' => $date_debut_disponibilite,
        'date_fin_disponibilite' => $date_fin_disponibilite
    ]));
    header('Location: register.php?errors=' . $errors_json . '&form_data=' . $form_data);
    exit();
}


$_SESSION['registration_success'] = true;
$_SESSION['provider_email'] = $email;
header('Location: confirmation.php');
exit();
?>
