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

// Fonction de journalisation pour le débogage
function logDebug($message, $data = null) {
    $logDir = $_SERVER['DOCUMENT_ROOT'] . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = $logDir . '/payment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $logMessage .= " - " . json_encode($data, JSON_PRETTY_PRINT);
        } else {
            $logMessage .= " - " . $data;
        }
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    
    // Aussi afficher en cas d'erreur
    if (strpos(strtolower($message), 'error') !== false || 
        strpos(strtolower($message), 'erreur') !== false) {
        error_log($logMessage);
    }
}

// Démarrer le débogage
logDebug("=== DÉBUT DE LA TRANSACTION DE PAIEMENT ===");
logDebug("URL complète: " . $_SERVER['REQUEST_URI']);

// Vérifier si les données de la société sont présentes
if (!isset($_SESSION['company_data'])) {
    logDebug("ERREUR: Session company_data manquante, redirection vers register.php");
    header('Location: register.php');
    exit();
}

logDebug("Données de la société présentes en session", $_SESSION['company_data']);

$company_data = $_SESSION['company_data'];
$paymentIntent = isset($_GET['payment_intent']) ? $_GET['payment_intent'] : '';
$redirectStatus = isset($_GET['redirect_status']) ? $_GET['redirect_status'] : '';

logDebug("Paramètres reçus", [
    'payment_intent' => $paymentIntent,
    'redirect_status' => $redirectStatus
]);

$stripeSecretKey = "sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm";
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

try {
    logDebug("Initialisation de Stripe avec la clé secrète");
    \Stripe\Stripe::setApiKey($stripeSecretKey);
    
    if ($paymentIntent && $redirectStatus === 'succeeded') {
        logDebug("Récupération du PaymentIntent depuis Stripe: " . $paymentIntent);
        $payment = \Stripe\PaymentIntent::retrieve($paymentIntent);
        logDebug("PaymentIntent récupéré", $payment->toArray());
        
        if ($payment->status === 'succeeded') {
            logDebug("Paiement confirmé comme réussi");
            
            // Requête curl appelant company/create.php
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/company/create.php';
            logDebug("Préparation de l'appel API pour créer la société: " . $url);
            
            $data = [
                'nom' => $company_data['nom'],
                'email' => $company_data['email'],
                'adresse' => $company_data['adresse'],
                'contact_person' => $company_data['contact_person'],
                'password' => $company_data['password'],
                'telephone' => $company_data['telephone'],
                'siret' => $company_data['siret'],
                'desactivate' => 0,
                'plan' => $company_data['plan'],
                'employee_count' => $company_data['employee_count']
            ];
            
            logDebug("Données pour la création de société", array_merge(
                $data,
                ['password' => '***MASQUÉ***'] // Masquer le mot de passe dans les logs
            ));

            // Initialize cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Méthode correcte
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Désactiver également la vérification de l'hôte
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            // Execute cURL request avec débogage amélioré
            logDebug("Exécution de la requête API pour créer la société");
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            logDebug("Réponse API (HTTP Code: {$httpCode})", $response);
            if ($curlError) {
                logDebug("ERREUR CURL lors de la création de la société", $curlError);
            }
            
            curl_close($ch);

            // Decode the response avec extraction d'un JSON valide si nécessaire
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Essayer d'extraire un JSON valide de la réponse
                $responseData = extractLastValidJson($response);
                
                if ($responseData === null) {
                    logDebug("ERREUR: Impossible de décoder la réponse JSON", [
                        'error' => json_last_error_msg(),
                        'raw_response' => $response
                    ]);
                    throw new Exception('Erreur lors du décodage de la réponse API: ' . json_last_error_msg());
                } else {
                    logDebug("JSON malformé détecté, mais extraction réussie d'un objet valide", $responseData);
                }
            }

            if (isset($responseData['error'])) {
                logDebug("ERREUR API lors de la création de la société", $responseData);
                throw new Exception('Erreur lors de la création de la société: ' . $responseData['error']);
            }

            // Vérifier que l'ID est bien présent
            if (!isset($responseData['societe_id'])) {
                logDebug("ERREUR: ID de société manquant dans la réponse", $responseData);
                throw new Exception("L'API n'a pas renvoyé d'ID de société");
            }

            // Récupération de l'ID de la société
            $societe_id = $responseData['societe_id'];
            logDebug("Société créée avec succès, ID: " . $societe_id);

            // Création d'un devis (contrat)
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/estimate/create.php';
            logDebug("Préparation de l'appel API pour créer le devis: " . $url);
            
            // Calcul des montants
            $montantHT = ($payment->amount / 100) * 0.8; // 80% du total (pour enlever TVA)
            logDebug("Calcul des montants", [
                'total_cents' => $payment->amount,
                'total_euros' => $payment->amount / 100,
                'montant_ht' => $montantHT
            ]);
            
            $estimateData = [
                'date_debut' => date('Y-m-d'),
                'date_fin' => date('Y-m-d', strtotime('+1 year')), // Contrat d'un an
                'montant_ht' => $montantHT,
                'statut' => 'accepté', // Paiement déjà effectué
                'is_contract' => 1, // C'est un contrat
                'id_societe' => $societe_id,
                'description' => 'Abonnement Business Care - ' . $company_data['plan'] . ' - ' . $company_data['employee_count'] . ' employés'
            ];
            
            logDebug("Données pour la création du devis", $estimateData);

            // Initialize cURL for estimate creation
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Méthode correcte
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($estimateData)); // Correction: utiliser $estimateData au lieu de $data
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Désactiver également la vérification de l'hôte
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            // Execute cURL request avec débogage
            logDebug("Exécution de la requête API pour créer le devis");
            $estimateResponse = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            logDebug("Réponse API de création de devis (HTTP Code: {$httpCode})", $estimateResponse);
            if ($curlError) {
                logDebug("ERREUR CURL lors de la création du devis", $curlError);
            }
            
            curl_close($ch);

            // Decode the response avec extraction d'un JSON valide si nécessaire
            $estimateResponseData = json_decode($estimateResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Essayer d'extraire un JSON valide de la réponse
                $estimateResponseData = extractLastValidJson($estimateResponse);
                
                if ($estimateResponseData === null) {
                    logDebug("ERREUR: Impossible de décoder la réponse JSON du devis", [
                        'error' => json_last_error_msg(),
                        'raw_response' => $estimateResponse
                    ]);
                    throw new Exception('Erreur lors du décodage de la réponse API de devis: ' . json_last_error_msg());
                } else {
                    logDebug("JSON devis malformé détecté, mais extraction réussie d'un objet valide", $estimateResponseData);
                }
            }

            if (isset($estimateResponseData['error'])) {
                logDebug("ERREUR API lors de la création du devis", $estimateResponseData);
                throw new Exception('Erreur lors de la création du devis: ' . $estimateResponseData['error']);
            }

            // Vérifier l'ID du devis
            if (!isset($estimateResponseData['id'])) {
                logDebug("ERREUR: ID de devis manquant dans la réponse", $estimateResponseData);
                throw new Exception("L'API n'a pas renvoyé d'ID de devis");
            }

            $estimate_id = $estimateResponseData['id'];
            logDebug("Devis créé avec succès, ID: " . $estimate_id);


            // enregistrement de l'abonnement dans la base de donnees
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/fees/create.php';
            logDebug("Préparation de l'appel API pour créer l'abonnement dans la table frais: " . $url);

            $feesData = [
                'nom' => 'Abonnement ' . $company_data['plan'] . ' - ' . $company_data['employee_count'] . ' employes',
                'montant' => $payment->amount / 100,
                'description' => 'Abonnement Business Care - ' . $company_data['plan'] . ' - ' . $company_data['employee_count'] . ' employes',
                'est_abonnement' => 1,
            ];
            logDebug("Données pour la création de l'abonnement", $feesData);
            // Initialize cURL for fees creation
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Méthode correcte
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($feesData)); // Correction: utiliser $feesData au lieu de $data
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Désactiver également la vérification de l'hôte
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            // Execute cURL request avec débogage
            logDebug("Exécution de la requête API pour créer l'abonnement");
            $feesResponse = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            logDebug("Réponse API de création d'abonnement (HTTP Code: {$httpCode})", $feesResponse);
            if ($curlError) {
                logDebug("ERREUR CURL lors de la création de l'abonnement", $curlError);
            }
            curl_close($ch);
            // Decode the response avec extraction d'un JSON valide si nécessaire
            $feesResponseData = json_decode($feesResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Essayer d'extraire un JSON valide de la réponse
                $feesResponseData = extractLastValidJson($feesResponse);
                
                if ($feesResponseData === null) {
                    logDebug("ERREUR: Impossible de décoder la réponse JSON de l'abonnement", [
                        'error' => json_last_error_msg(),
                        'raw_response' => $feesResponse
                    ]);
                    throw new Exception('Erreur lors du décodage de la réponse API d\'abonnement: ' . json_last_error_msg());
                } else {
                    logDebug("JSON abonnement malformé détecté, mais extraction réussie d'un objet valide", $feesResponseData);
                }
            }

            if (isset($feesResponseData['error'])) {
                logDebug("ERREUR API lors de la création de l'abonnement", $feesResponseData);
                throw new Exception('Erreur lors de la création de l\'abonnement: ' . $feesResponseData['error']);
            }

            // Vérifier l'ID de l'abonnement
            if (!isset($feesResponseData['frais_id'])) {
                logDebug("ERREUR: ID d'abonnement manquant dans la réponse", $feesResponseData);
                throw new Exception("L'API n'a pas renvoyé d'ID d'abonnement");
            }
            $frais_id = $feesResponseData['frais_id'];
            logDebug("Abonnement créé avec succès, ID: " . $frais_id);

            // link abonnement et devis
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/fees/link-devis.php';
            logDebug("Préparation de l'appel API pour lier l'abonnement et le devis: " . $url);
            $linkData = [
                'frais_id' => $frais_id,
                'devis_id' => $estimate_id
            ];

            logDebug("Données pour lier l'abonnement et le devis", $linkData);
            // Initialize cURL for linking fees and estimate
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Méthode correcte
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($linkData)); // Correction: utiliser $linkData au lieu de $data
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Désactiver également la vérification de l'hôte
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            // Execute cURL request avec débogage
            logDebug("Exécution de la requête API pour lier l'abonnement et le devis");
            $linkResponse = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            logDebug("Réponse API de liaison d'abonnement et de devis (HTTP Code: {$httpCode})", $linkResponse);
            if ($curlError) {
                logDebug("ERREUR CURL lors de la liaison de l'abonnement et du devis", $curlError);
            }
            curl_close($ch);
            // Decode the response avec extraction d'un JSON valide si nécessaire
            $linkResponseData = json_decode($linkResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Essayer d'extraire un JSON valide de la réponse
                $linkResponseData = extractLastValidJson($linkResponse);
                
                if ($linkResponseData === null) {
                    logDebug("ERREUR: Impossible de décoder la réponse JSON de liaison", [
                        'error' => json_last_error_msg(),
                        'raw_response' => $linkResponse
                    ]);
                    throw new Exception('Erreur lors du décodage de la réponse API de liaison: ' . json_last_error_msg());
                } else {
                    logDebug("JSON liaison malformé détecté, mais extraction réussie d'un objet valide", $linkResponseData);
                }
            }
            if (isset($linkResponseData['error'])) {
                logDebug("ERREUR API lors de la liaison de l'abonnement et du devis", $linkResponseData);
                throw new Exception('Erreur lors de la liaison de l\'abonnement et du devis: ' . $linkResponseData['error']);
            }
            logDebug("Liaison de l'abonnement et du devis réussie");
            

            // Enregistrer la facture dans la base de données
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/invoice/create.php';
            logDebug("Préparation de l'appel API pour créer la facture: " . $url);
            
            $invoiceData = [
                'date_emission' => date('Y-m-d'),
                'date_echeance' => date('Y-m-d', strtotime('+30 days')),
                'montant' => $payment->amount / 100,
                'montant_tva' => ($payment->amount / 100) * 0.2, // 20% TVA
                'montant_ht' => $montantHT,
                'statut' => 'Payee', // Déjà payé via Stripe
                'methode_paiement' => 'carte bancaire',
                'id_devis' => $estimate_id, // Utiliser la variable correcte
                'id_prestataire' => null // Pas de prestataire associé
            ];
            
            logDebug("Données pour la création de la facture", $invoiceData);

            // Initialize cURL for invoice creation - Correction du double $ dans $$ch
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Méthode correcte
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoiceData)); // Correction: utiliser $invoiceData au lieu de $data
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Désactiver également la vérification de l'hôte
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            // Execute cURL request avec débogage
            logDebug("Exécution de la requête API pour créer la facture");
            $invoiceResponse = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            logDebug("Réponse API de création de facture (HTTP Code: {$httpCode})", $invoiceResponse);
            if ($curlError) {
                logDebug("ERREUR CURL lors de la création de la facture", $curlError);
            }
            
            curl_close($ch);

            // Decode the response avec extraction d'un JSON valide si nécessaire
            $invoiceResponseData = json_decode($invoiceResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Essayer d'extraire un JSON valide de la réponse
                $invoiceResponseData = extractLastValidJson($invoiceResponse);
                
                if ($invoiceResponseData === null) {
                    logDebug("ERREUR: Impossible de décoder la réponse JSON de la facture", [
                        'error' => json_last_error_msg(),
                        'raw_response' => $invoiceResponse
                    ]);
                    throw new Exception('Erreur lors du décodage de la réponse API de facture: ' . json_last_error_msg());
                } else {
                    logDebug("JSON facture malformé détecté, mais extraction réussie d'un objet valide", $invoiceResponseData);
                }
            }

            if (isset($invoiceResponseData['error'])) {
                logDebug("ERREUR API lors de la création de la facture", $invoiceResponseData);
                throw new Exception('Erreur lors de la création de la facture: ' . $invoiceResponseData['error']);
            }

            $invoice_id = $invoiceResponseData['id'] ?? null;
            logDebug("Facture créée avec succès" . ($invoice_id ? ", ID: " . $invoice_id : ""));

            // Envoi d'email - commenté pour l'instant
            /*
            $to = $company_data['email'];
            $subject = 'Confirmation de votre abonnement Business Care';
            $message = 'Bonjour ' . $company_data['contact_person'] . ',

            Merci pour votre inscription à Business Care. Votre abonnement a été activé avec succès.

            Détails de l\'abonnement :
            - Formule : ' . ucfirst($company_data['plan']) . '
            - Nombre de salariés : ' . $company_data['employee_count'] . '
            - Montant total : ' . number_format($payment->amount / 100, 2, ',', ' ') . ' €
            - N° de facture : ' . ($invoice_id ?? 'Non disponible') . '

            Vous pouvez dès à présent vous connecter à votre espace administrateur pour gérer votre compte.

            L\'équipe Business Care';

            $headers = 'From: noreply@businesscare.com' . "\r\n";
            mail($to, $subject, $message, $headers);
            */
            
            // Tout s'est bien passé, on peut nettoyer la session
            // Garder juste l'email pour l'affichage
            $userEmail = $company_data['email'];
            logDebug("Transaction complétée avec succès pour l'email: " . $userEmail);
            logDebug("Nettoyage de la session company_data");
            
            // Supprimer les données sensibles de la session
            unset($_SESSION['company_data']);
        } else {
            logDebug("ERREUR: Statut du paiement non réussi", ['status' => $payment->status]);
        }
    } else {
        logDebug("ERREUR: PaymentIntent ou redirectStatus non valides", [
            'paymentIntent' => $paymentIntent,
            'redirectStatus' => $redirectStatus
        ]);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    logDebug("EXCEPTION ATTRAPÉE: " . $error);
    logDebug("Trace: " . $e->getTraceAsString());
}

logDebug("=== FIN DE LA TRANSACTION DE PAIEMENT ===");

$title = "Confirmation de paiement";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5, #6fc2c0);
        background-attachment: fixed;
    }

    .confirmation-card {
        border-radius: 15px;
        box-shadow: rgba(58, 123, 213, 0.4) 5px 5px,
                    rgba(58, 123, 213, 0.3) 10px 10px,
                    rgba(58, 123, 213, 0.2) 15px 15px,
                    rgba(58, 123, 213, 0.1) 20px 20px,
                    rgba(58, 123, 213, 0.05) 25px 25px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.95);
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem !important;
        background: linear-gradient(to right, #3a7bd5, #6fc2c0) !important;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(to right, #3a7bd5, #6fc2c0);
        border: none;
        transition: all 0.3s ease;
    }

    .check-icon {
        font-size: 5rem;
        color: #3a7bd5;
    }
    
    .debug-info {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 2rem;
        border: 1px solid #dee2e6;
        text-align: left;
        font-family: monospace;
    }
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8">
            <div class="card confirmation-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Statut du paiement</h3>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if ($redirectStatus === 'succeeded'): ?>
                        <div class="my-4">
                            <i class="fas fa-check-circle check-icon"></i>
                            <h4 class="mt-3 mb-3">Paiement effectué avec succès!</h4>
                            <p class="lead mb-4">Merci pour votre inscription à Business Care.</p>
                            <div class="alert alert-success">
                                <p class="mb-0">Un email de confirmation avec tous les détails de votre abonnement vous a été envoyé à l'adresse <?php echo htmlspecialchars($userEmail ?? ($company_data['email'] ?? 'votre email')); ?></p>
                            </div>
                            <hr class="my-4">
                            <h5 class="mb-3">Prochaines étapes</h5>
                            <ol class="list-group list-group-numbered mb-4 text-start">
                                <li class="list-group-item">Connectez-vous à votre compte administrateur</li>
                                <li class="list-group-item">Ajoutez les informations de vos collaborateurs</li>
                                <li class="list-group-item">Personnalisez votre espace entreprise</li>
                            </ol>
                            <a href="/frontOffice/societe/login/login.php" class="btn btn-lg btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="my-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                            <h4 class="mt-3 mb-3">Échec du paiement</h4>
                            <p class="lead mb-4">Une erreur est survenue lors du traitement de votre paiement.</p>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <p class="mb-0">Détail: <?php echo htmlspecialchars($error); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <hr class="my-4">
                            <p>Veuillez réessayer ou contacter notre service client si le problème persiste.</p>
                            
                            <a href="paiement.php" class="btn btn-lg btn-primary">
                                <i class="fas fa-redo me-2"></i>Réessayer
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                        <div class="debug-info mt-4">
                            <h6>Informations de débogage</h6>
                            <p>Ces informations aideront à diagnostiquer les problèmes avec l'API.</p>
                            <p>Pour voir les logs complets, consultez le fichier: /logs/payment_debug.log</p>
                            
                            <h6 class="mt-3">État de la session:</h6>
                            <pre><?php echo htmlspecialchars(print_r($_SESSION, true)); ?></pre>
                            
                            <?php if (isset($payment)): ?>
                                <h6 class="mt-3">Détails du paiement Stripe:</h6>
                                <pre><?php echo htmlspecialchars(print_r($payment->toArray(), true)); ?></pre>
                            <?php endif; ?>
                            
                            <?php if (isset($responseData)): ?>
                                <h6 class="mt-3">Réponse API (création société):</h6>
                                <pre><?php echo htmlspecialchars(print_r($responseData, true)); ?></pre>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>