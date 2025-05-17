<?php
$title = 'Inscription - Finalisation de l\'inscription';
session_start();

// Initialiser le tableau des erreurs
$errors = [];

//verifier la methode de la requete
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Méthode non autorisée
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit();
}

// verifier si l'utilisateur est connecté
if (!isset($_SESSION['company_data'])) {
    $errors[] = "Vous devez d'abord vous inscrire.";
}

// Vérifier si les étapes précédentes ont été complétées
if (!isset($_POST['plan']) || !isset($_POST['employee_count'])) {
    $errors[] = "Vous devez d'abord choisir un plan et le nombre d'employés.";
}

if (isset($_POST['employee_count']) && $_POST['employee_count'] < 1) {
    $errors[] = "Le nombre d'employés doit être supérieur à 0.";
}

$employeeCount = isset($_POST['employee_count']) ? intval($_POST['employee_count']) : 0;
$plan = isset($_POST['plan']) ? $_POST['plan'] : '';
$money = 0; // Initialisation correcte de la variable

if (!in_array($plan, ['starter', 'basic', 'premium'])) {
    $errors[] = "Le plan sélectionné n'est pas valide.";
}

// Vérifications de compatibilité entre plan et nombre d'employés
if ($employeeCount > 30 && $plan == 'starter') {
    $errors[] = "Le plan Starter est limité à 30 employés maximum.";
} elseif ($employeeCount > 250 && $plan == 'basic') {
    $errors[] = "Le plan Basic est limité à 250 employés maximum.";
} elseif ($employeeCount < 251 && $plan == 'premium') {
    $errors[] = "Le plan Premium nécessite au moins 251 employés.";
}

if (!empty($errors)) {
    $errors_json = urlencode(json_encode($errors));
    header('Location: subscription.php?errors=' . $errors_json);
    exit();
}

// Calcul du montant selon le plan
switch ($plan) {
    case 'starter':
        $money = 180 * $employeeCount;
        break;
    case 'basic':
        $money = 150 * $employeeCount;
        break;
    case 'premium':
        $money = 100 * $employeeCount;
        break;
    default:
        $errors[] = "Le plan sélectionné n'est pas valide.";
        $errors_json = urlencode(json_encode($errors));
        header('Location: subscription.php?errors=' . $errors_json);
        exit();
}

// Récupérer les données
$company = $_SESSION['company_data'];

// Vérification des données de l'entreprise
if (empty($company['nom']) || empty($company['siret']) || empty($company['adresse']) ||
    empty($company['email']) || empty($company['contact_person']) || empty($company['telephone']) ||
    empty($company['password'])) {
    $errors[] = "Tous les champs sont obligatoires.";
    header('Location: register.php?errors=' . urlencode(json_encode($errors)));
    exit();
}

// Construction de la requête à l'API
$apiUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api/company/create.php';
$data = [
    'nom' => $company['nom'],
    'siret' => $company['siret'],
    'adresse' => $company['adresse'],
    'email' => $company['email'],
    'contact_person' => $company['contact_person'],
    'telephone' => $company['telephone'],
    'password' => $company['password'],
    'desactivate' => 1
];

// Envoi de la requête API
$ch = curl_init($apiUrl);
if ($ch === false) {
    $errors[] = "Trouble d'initialisation de la requête cURL.";
    header('Location: register.php?errors=' . urlencode(json_encode($errors)));
    exit();
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

// Vérifier les erreurs cURL
if ($response === false) {
    $errors['curl1'] = curl_error($ch);
    $errors['curl1'] = 'Erreur de communication avec le serveur. Veuillez réessayer ultérieurement.';
    curl_close($ch);
    // header('Location: register.php?errors=' . urlencode(json_encode($errors)));
    exit();
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

// Journalisation des réponses d'erreur pour le débogage
if ($httpCode != 201) {
    error_log("Erreur API création société - Code: $httpCode - Réponse: " . print_r($responseData, true));

    // Traitement des codes d'erreur HTTP spécifiques
    // switch ($httpCode) {
    //     case 400:
    //         $errors['switch'] = $responseData;
    //         break;
    //     case 401:
    //     case 403:
    //         $errors['switch'] = 'Authentification requise ou accès refusé.';
    //         break;
    //     case 404:
    //         $errors['switch'] = 'Le service demandé est indisponible.';
    //         break;
    //     case 409:
    //         $errors['switch'] = 'Cette entreprise existe déjà dans notre système.';
    //         break;
    //     case 500:
    //         $errors['switch'] = 'Erreur interne du serveur. Veuillez réessayer ultérieurement.';
    //         break;
    //     default:
    //         $errors['switch'] = $responseData['error'] ?? 'Une erreur inattendue est survenue lors de la création du compte.';
    // }

    $_SESSION['register_data'] = [
        'nom' => $company['nom'],
        'siret' => $company['siret'],
        'adresse' => $company['adresse'],
        'email' => $company['email'],
        'contact_person' => $company['contact_person'],
        'telephone' => $company['telephone']
    ];

    header('Location: register.php?errors=' . urlencode(json_encode($errors)) . '&formdata=' . urlencode(serialize($_SESSION['register_data'])));
    exit();
}

// Si la création de la société a réussi, on crée le devis
if ($responseData && isset($responseData['societe_id'])) {
    $apiUrl2 = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api/estimate/create.php';
    $data = [
        'date_debut' => date("Y-m-d"),
        'date_fin' => date("Y-m-d", strtotime("+1 month")),
        'montant_ht' => $money,
        'statut' => 'envoyé',
        'is_contract' => 0,
        'id_societe' => $responseData['societe_id'],
    ];

    // Envoi de la requête API
    $ch = curl_init($apiUrl2);
    if ($ch === false) {
        $errors[] = "Erreur d'initialisation de la requête cURL.";
        header('Location: register.php?errors=' . urlencode(json_encode($errors)));
        // curl_close($ch);
        exit();
    }


    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    // Vérifier les erreurs cURL
    if ($response === false) {

        $errors[] = 'Erreur de communication avec le serveur. Veuillez réessayer ultérieurement.';
        header('Location: register.php?errors=' . urlencode(json_encode($errors)));
        curl_close($ch);
        exit();

    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Décoder la réponse JSON pour le devis
    $estimateResponse = json_decode($response, true);

    if ($httpCode != 201) {
        $errors[] = "Erreur lors de la création du devis. Code HTTP: $httpCode";
        header('Location: register.php' . '?errors=' . urlencode(json_encode($errors)));
        exit();
    }

    // Succès - rediriger vers une page de confirmation
    $_SESSION['registration_success'] = true;

    // Nettoyage des données de session
    // unset($_SESSION['company_data']);
    // unset($_SESSION['register_errors']);
    // unset($_SESSION['register_data']);

    header('Location: confirmation.php');
    exit();
} else {
    // Si on arrive ici, c'est qu'il y a un problème avec la réponse de l'API
    error_log("Réponse API société invalide: " . print_r($responseData, true));
    $errors[] = "Erreur lors de la création de la société. Veuillez réessayer.";
    header('Location: register.php' . '?errors=' . urlencode(json_encode($errors)));
    exit();
}
?>
