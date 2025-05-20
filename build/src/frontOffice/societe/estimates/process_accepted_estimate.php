<?php
session_start();

// Fonction pour extraire le dernier objet JSON valide d'une chaîne potentiellement malformée
function extractLastValidJson($jsonString) {
    // Vérifions d'abord s'il y a un problème de JSON concaténés
    if (strpos($jsonString, '}{') !== false) {
        // Trouver le dernier objet JSON dans la chaîne
        if (preg_match('/{[^{]*}$/s', $jsonString, $matches)) {
            $potentialJson = $matches[0];
            $decoded = json_decode($potentialJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
    }
    
    // Essayer de décoder tout le texte normalement
    $decoded = json_decode($jsonString, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $decoded;
    }
    
    return null;
}

// Fonction de journalisation améliorée
function logAction($message, $level = 'INFO') {
    // Créer les dossiers nécessaires s'ils n'existent pas
    $logDir = $_SERVER['DOCUMENT_ROOT'] . '/logs/estimates';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Récupérer le devis_id et la société_id pour un nom de fichier plus spécifique
    $devis_id = isset($_GET['devis_id']) ? intval($_GET['devis_id']) : 0;
    $societe_id = isset($_SESSION['societe_id']) ? $_SESSION['societe_id'] : 'unknown';
    
    // Nom du fichier de log plus explicite
    $logFile = $logDir . "/devis_{$devis_id}_acceptance_" . date('Y-m-d') . ".log";
    
    // Formatage du message
    $timestamp = date('Y-m-d H:i:s');
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'unknown';
    
    // Création de la ligne de log
    $logLine = "[$timestamp][$level][User:$userId][Societe:$societe_id] $message" . PHP_EOL;
    
    // Écriture dans le fichier
    file_put_contents($logFile, $logLine, FILE_APPEND);
    
    // Également écrire dans un journal global des acceptations de devis pour référence
    $globalLogFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/all_estimate_acceptances.log';
    file_put_contents($globalLogFile, $logLine, FILE_APPEND);
}

// Début du traitement
logAction("=== DÉBUT DU TRAITEMENT DE L'ACCEPTATION DU DEVIS ===");

// Vérification de l'authentification
if (!isset($_SESSION['societe_id'])) {
    logAction("Tentative d'accès sans authentification", "ERROR");
    header('Location: /login.php');
    exit;
}

// Récupération des paramètres
$devis_id = isset($_GET['devis_id']) ? intval($_GET['devis_id']) : 0;
$societe_id = isset($_GET['societe_id']) ? intval($_GET['societe_id']) : 0;

logAction("Paramètres reçus: devis_id=$devis_id, societe_id=$societe_id");

// Vérification des paramètres
if ($devis_id <= 0 || $societe_id <= 0) {
    logAction("Paramètres invalides: devis_id=$devis_id, societe_id=$societe_id", "ERROR");
    $_SESSION['error'] = "Paramètres invalides.";
    header('Location: /frontOffice/societe/estimates/estimates.php');
    exit;
}

if (!isset($_SESSION['societe_id']) || $_SESSION['societe_id'] != $societe_id) {
    logAction("Accès non autorisé: session_societe_id={$_SESSION['societe_id']}, requested_societe_id=$societe_id", "ERROR");
    $_SESSION['error'] = "Accès non autorisé.";
    header('Location: /frontOffice/societe/estimates/estimates.php');
    exit;
}

// Initialisation des variables de statut
$status = [
    'expense' => false,
    'invoice' => false,
    'contract' => false
];
$errors = [];

logAction("Initialisation du traitement pour le devis #$devis_id");

try {
    // 1. Récupération des détails du devis via getOne.php
    logAction("Récupération des détails du devis #$devis_id");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . "/api/estimate/getOne.php?devis_id={$devis_id}");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL pour l'environnement de dev
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlError) {
        logAction("Erreur CURL lors de la récupération du devis: " . $curlError, "ERROR");
        throw new Exception("Erreur de connexion: " . $curlError);
    }

    if ($httpCode !== 200) {
        logAction("Échec de la récupération des détails du devis. Code HTTP: $httpCode, Réponse: $response", "ERROR");
        throw new Exception("Impossible de récupérer les détails du devis. Code: " . $httpCode);
    }

    // Traiter la réponse JSON avec gestion des erreurs améliorée
    $estimate = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $estimate = extractLastValidJson($response);
        if (!$estimate) {
            logAction("Format de données JSON invalide pour le devis: " . json_last_error_msg(), "ERROR");
            throw new Exception("Format de données invalide pour le devis.");
        }
    }

    logAction("Détails du devis récupérés avec succès: " . json_encode($estimate, JSON_UNESCAPED_UNICODE));

    // Vérification que le devis appartient bien à la société
    if ($estimate['id_societe'] != $societe_id) {
        logAction("Tentative d'accès à un devis d'une autre société: devis_societe_id={$estimate['id_societe']}, session_societe_id=$societe_id", "ERROR");
        throw new Exception("Ce devis n'appartient pas à votre société.");
    }

    // 2. Conversion du devis en contrat
    logAction("Début de la conversion du devis #$devis_id en contrat");
    $contractConversionData = [
        'devis_id' => $devis_id
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . "/api/estimate/convertToContract.php");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contractConversionData));
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlError) {
        logAction("Erreur CURL lors de la conversion en contrat: " . $curlError, "ERROR");
        $errors[] = "Erreur de connexion: " . $curlError;
    } else if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result = extractLastValidJson($response);
        }
        
        if ($result && isset($result['success'])) {
            $status['contract'] = true;
            logAction("Conversion en contrat réussie pour le devis #$devis_id", "SUCCESS");
        } else {
            $errorMsg = $result['error'] ?? "Erreur inconnue";
            $errors[] = "Erreur lors de la conversion en contrat: " . $errorMsg;
            logAction("Échec de la conversion en contrat: $errorMsg", "ERROR");
        }
    } else {
        $errors[] = "Erreur lors de la conversion en contrat (code: {$httpCode})";
        logAction("Échec de la conversion en contrat. Code HTTP: $httpCode, Réponse: $response", "ERROR");
    }

   

    // 4. Création d'une facture liée au devis
    logAction("Début de la création d'une facture pour le devis #$devis_id");
    $today = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+30 days'));

    $invoiceData = [
        'date_emission' => $today,
        'date_echeance' => $dueDate,
        'montant' => $estimate['montant'] ?? 0,
        'montant_tva' => $estimate['montant_tva'] ?? 0,
        'montant_ht' => $estimate['montant_ht'] ?? 0,
        'statut' => 'Attente',
        'methode_paiement' => 'virement',
        'id_devis' => $devis_id
    ];

    logAction("Données envoyées pour la création de la facture: " . json_encode($invoiceData, JSON_UNESCAPED_UNICODE));

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . "/api/invoice/create.php");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($invoiceData));
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlError) {
        logAction("Erreur CURL lors de la création de la facture: " . $curlError, "ERROR");
        $errors[] = "Erreur de connexion: " . $curlError;
    } else if ($httpCode === 200 || $httpCode === 201) {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result = extractLastValidJson($response);
        }
        
        if ($result && isset($result['id'])) {
            $status['invoice'] = true;
            logAction("Création de la facture réussie pour le devis #$devis_id. ID de la facture: {$result['id']}", "SUCCESS");
        } else {
            $errorMsg = $result['error'] ?? "Erreur inconnue";
            $errors[] = "Erreur lors de la création de la facture: " . $errorMsg;
            logAction("Échec de la création de la facture: $errorMsg", "ERROR");
        }
    } else {
        $errors[] = "Erreur lors de la création de la facture (code: {$httpCode}): " . $response;
        logAction("Échec de la création de la facture. Code HTTP: $httpCode, Réponse: $response", "ERROR");
    }

} catch (Exception $e) {
    $errors[] = $e->getMessage();
    logAction("Exception: " . $e->getMessage(), "ERROR");
    logAction("Trace: " . $e->getTraceAsString(), "ERROR");
}

// Préparation du message de résultat
if ($status['expense'] && $status['invoice'] && $status['contract']) {
    $_SESSION['success'] = "Le devis a été accepté avec succès ! Un frais, une facture et un contrat ont été créés.";
    logAction("Toutes les opérations ont réussi pour le devis #$devis_id", "SUCCESS");
} elseif ($status['expense'] || $status['invoice'] || $status['contract']) {
    $_SESSION['warning'] = "Le devis a été accepté, mais certaines opérations ont échoué: " . implode(", ", $errors);
    logAction("Certaines opérations ont échoué pour le devis #$devis_id: " . implode(", ", $errors), "WARNING");
} else {
    $_SESSION['error'] = "Le devis a été accepté, mais toutes les opérations supplémentaires ont échoué: " . implode(", ", $errors);
    logAction("Toutes les opérations ont échoué pour le devis #$devis_id: " . implode(", ", $errors), "ERROR");
}

// Redirection vers la page des devis
logAction("Fin du traitement pour le devis #$devis_id. Redirection vers la page des devis.");
header('Location: /frontOffice/societe/estimates/estimates.php');
exit;
?>

