<?php
/**
 * Processus de création de devis
 * 
 * Ce script gère la création d'un devis et des frais associés via les API
 */

// Fonction utilitaire pour les logs
if (!function_exists('logAction')) {
    function logAction($message, $level = "INFO") {
        // Implémentation simple de logging
        $logFile = __DIR__ . '/../../../logs/app.log';
        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date][$level] $message" . PHP_EOL;
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }
}

// Fonction utilitaire pour extraire un JSON valide d'une réponse potentiellement corrompue
if (!function_exists('extractLastValidJson')) {
    function extractLastValidJson($response) {
        preg_match_all('/{.*}/s', $response, $matches);
        if (!empty($matches[0])) {
            $lastMatch = end($matches[0]);
            $decoded = json_decode($lastMatch, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return null;
    }
}

// ===== 1. RÉCUPÉRATION ET VALIDATION DES DONNÉES =====

// Récupération des valeurs du GET
$employee_count = isset($_GET['employee_count']) ? intval($_GET['employee_count']) : 0;
$additional_employees = isset($_GET['additional_employees']) ? intval($_GET['additional_employees']) : 0;
$current_cost = isset($_GET['current_cost']) ? floatval($_GET['current_cost']) : 0;
$new_annual_cost = isset($_GET['new_annual_cost']) ? floatval($_GET['new_annual_cost']) : 0;
$difference = isset($_GET['difference']) ? floatval($_GET['difference']) : 0;
$prorated_cost = isset($_GET['prorated_cost']) ? floatval($_GET['prorated_cost']) : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$months_remaining = isset($_GET['months_remaining']) ? intval($_GET['months_remaining']) : 0;

// Vérification des paramètres essentiels
if ($employee_count <= 0 || $end_date === '') {
    header('Location: new_estimate.php?error=missing_parameters');
    exit;
}

// ===== 2. VÉRIFICATION DE LA SESSION =====

session_start();
$societe_id = $_SESSION['societe_id'] ?? null;
$societe_name = $_SESSION['societe_name'] ?? null;

if ($societe_id === null) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

// ===== 3. CRÉATION DU DEVIS VIA API =====

// Préparation des données pour l'API
$data = [
    'montant_ht' => $prorated_cost,
    'id_societe' => $societe_id,
    'statut' => 'envoyé',
    'date_debut' => $start_date,
    'date_fin' => $end_date,
    'is_contract' => 0 // Indique qu'il s'agit d'un devis
];

logAction("Création d'un nouveau devis - données: " . json_encode($data, JSON_UNESCAPED_UNICODE));

// Appel à l'API pour créer le devis
$url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/estimate/create.php';
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Traitement de la réponse
if ($error) {
    logAction("Erreur cURL lors de la création du devis: " . $error, "ERROR");
    echo "Erreur cURL : " . $error;
    exit;
} elseif ($http_code != 201) {
    $response_data = json_decode($response, true);
    $error_message = isset($response_data['message']) ? $response_data['message'] : 'Erreur inconnue';
    logAction("Échec de la création du devis: " . $error_message, "ERROR");
    echo "Erreur lors de la création du devis: " . $error_message;
    exit;
}

// Récupération de l'ID du devis créé
$response_data = json_decode($response, true);
$estimate_id = $response_data['id'];
logAction("Devis #$estimate_id créé avec succès", "SUCCESS");

// ===== 4. CRÉATION DU FRAIS ASSOCIÉ =====

// Initialisation des tableaux d'état et d'erreurs
$status = [];
$errors = [];

// Détermination du plan en fonction du nombre total d'employés
$plan = "";
if ($employee_count <= 5) {
    $plan = "Starter";
} else if ($employee_count <= 20) {
    $plan = "Business";
} else if ($employee_count <= 50) {
    $plan = "Enterprise";
} else {
    $plan = "Premium";
}

logAction("Début de la création d'un frais pour le devis #$estimate_id");
$expenseData = [
    'nom' => "Frais Plan {$plan} - ajout de : {$additional_employees} collaborateurs - Devis #{$estimate_id}",
    'montant' => $prorated_cost, // Utilisation du montant du devis
    'description' => "Frais automatiquement créé suite à l'acceptation du devis #{$estimate_id}",
    'est_abonnement' => 0, 
    'societe_id' => $societe_id,
    'devis_id' => $estimate_id,
    'date_creation' => date('Y-m-d')
];

logAction("Données envoyées pour la création du frais: " . json_encode($expenseData, JSON_UNESCAPED_UNICODE));

// Appel API pour créer le frais
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "http://" . $_SERVER['HTTP_HOST'] . "/api/fees/create.php",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => json_encode($expenseData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

$response = curl_exec($curl);
$curlError = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($curlError) {
    logAction("Erreur CURL lors de la création du frais: " . $curlError, "ERROR");
    $errors[] = "Erreur de connexion: " . $curlError;
} else if ($httpCode === 200 || $httpCode === 201) {
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $result = extractLastValidJson($response);
    }
    
    if ($result && isset($result['frais_id'])) {
        $status['expense'] = true;
        $frais_id = $result['frais_id'];
        logAction("Création du frais réussie pour le devis #$estimate_id. ID du frais: {$result['frais_id']}", "SUCCESS");
        
        // ===== 5. LIAISON EXPLICITE ENTRE LE FRAIS ET LE DEVIS =====
        logAction("Liaison explicite entre le frais #{$frais_id} et le devis #{$estimate_id}", "INFO");
        
        $linkData = [
            'frais_id' => $frais_id,
            'devis_id' => $estimate_id
        ];
        
        logAction("Préparation de la liaison - Données: " . json_encode($linkData), "DEBUG");
        
        // Appel API pour lier le frais au devis
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://" . $_SERVER['HTTP_HOST'] . "/api/fees/link-devis.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($linkData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        
        $linkResponse = curl_exec($curl);
        $linkCurlError = curl_error($curl);
        $linkHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        logAction("API de liaison appelée - Code HTTP: $linkHttpCode", "DEBUG");
        
        if ($linkCurlError) {
            logAction("Erreur CURL lors de la liaison frais-devis: " . $linkCurlError, "ERROR");
        } else {
            $linkResult = json_decode($linkResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $linkResult = extractLastValidJson($linkResponse);
            }
            
            logAction("Réponse brute de l'API de liaison: " . $linkResponse, "DEBUG");
            
            if ($linkHttpCode === 200 || $linkHttpCode === 201) {
                logAction("Liaison du frais #{$frais_id} avec le devis #{$estimate_id} réussie", "SUCCESS");
                
                if ($linkResult && isset($linkResult['lien_id'])) {
                    logAction("ID de la liaison créée: {$linkResult['lien_id']}", "INFO");
                } else {
                    logAction("Liaison réussie mais sans ID de liaison retourné", "WARNING");
                }
            } else {
                logAction("Échec de la liaison frais-devis - Code: $linkHttpCode", "ERROR");
                
                $errorMsg = isset($linkResult['error']) ? $linkResult['error'] : "Message d'erreur non disponible";
                logAction("Détail de l'erreur de liaison: $errorMsg", "ERROR");
                
                if (strpos($errorMsg, "déjà associé") !== false || strpos($errorMsg, "already linked") !== false) {
                    logAction("Le frais et le devis semblent déjà liés dans la base de données", "INFO");
                }
            }
        }
    } else {
        $errorMsg = $result['error'] ?? "Erreur inconnue";
        $errors[] = "Erreur lors de la création du frais: " . $errorMsg;
        logAction("Échec de la création du frais: $errorMsg", "ERROR");
    }
} else {
    $errors[] = "Erreur lors de la création du frais (code: {$httpCode})";
    logAction("Échec de la création du frais. Code HTTP: $httpCode, Réponse: $response", "ERROR");
}

// ===== 6. REDIRECTION VERS LA PAGE DE CONFIRMATION =====

header("Location: estimate_confirmation.php?id=" . $estimate_id . 
       "&employee_count=" . $employee_count .
       "&additional_employees=" . $additional_employees .
       "&prorated_cost=" . $prorated_cost .
       "&start_date=" . urlencode($start_date) .
       "&end_date=" . urlencode($end_date));
exit;
?>

