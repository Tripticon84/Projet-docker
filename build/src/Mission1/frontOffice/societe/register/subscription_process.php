<?php

session_start();

if (!isset($_SESSION['company_data'])) {
    header('Location: register.php');
    exit();
}

$company_data = $_SESSION['company_data'];
//echo $_SESSION['company_data']['employee_count'];
//echo $_SESSION['company_data']['plan'];
//echo $_SESSION['company_data']['nom'];
//echo $_SESSION['company_data']['siret'];

//die()


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

if (!isset($_POST['employee_count']) || !isset($_POST['plan'])) {
    header('Location: register.php');
    exit();
}

if (!is_numeric($_POST['employee_count'])) {
    header('Location: register.php');
    exit();
}

if ($_POST['employee_count'] < 1) {
    header('Location: register.php');
    exit();
}

if ($_POST['plan'] !== 'starter' && $_POST['plan'] !== 'basic' && $_POST['plan'] !== 'premium') {
    header('Location: register.php');
    exit();
}


//calculer le prix
$employee_count = $_POST['employee_count'];
$plan = $_POST['plan'];

$price = 0;

if ($plan === 'starter') {
    $price = 180 * $employee_count;
} elseif ($plan === 'basic') {
    $price = 150 * $employee_count;
} elseif ($plan === 'premium') {
    $price = 100 * $employee_count;
}

$_SESSION['company_data'] = [
    'nom' => $company_data['nom'],
    'siret' => $company_data['siret'],
    'adresse' => $company_data['adresse'],
    'email' => $company_data['email'],
    'contact_person' => $company_data['contact_person'],
    'telephone' => $company_data['telephone'],
    'password' => $company_data['password'],
    'confirm_password' => $company_data['confirm_password'],
    'employee_count' => $_POST['employee_count'],
    'plan' => $_POST['plan'],
    'price' => $price
];

$subscription_data = [
    'employee_count' => $_POST['employee_count'],
    'plan' => $_POST['plan'],
    'price' => $price
];

//echo $_SESSION['company_data']['price'];
//echo $_SESSION['company_data']['employee_count'];
//echo $_SESSION['company_data']['plan'];
//echo $_SESSION['company_data']['nom'];
//echo $_SESSION['company_data']['siret'];


header('Location: paiement.php');
exit();
?>

