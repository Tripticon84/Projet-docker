<?php
$title = "Statut du paiement";
include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';

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
    // Utiliser DIRECTORY_SEPARATOR pour la compatibilité cross-platform
    $logDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'logs';

    // Vérifier si le répertoire existe, sinon le créer avec des permissions appropriées
    if (!is_dir($logDir)) {
        // 0755 est plus sécurisé que 0777 et généralement suffisant
        if (!mkdir($logDir, 0755, true)) {
            error_log("Impossible de créer le répertoire de logs: $logDir");
            return; // Sortir si la création échoue
        }
    }

    // S'assurer que le répertoire est accessible en écriture
    if (!is_writable($logDir)) {
        error_log("Le répertoire de logs n'est pas accessible en écriture: $logDir");
        return;
    }

    $logFile = $logDir . DIRECTORY_SEPARATOR . 'payment_verification.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";

    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $logMessage .= " - " . json_encode($data, JSON_PRETTY_PRINT);
        } else {
            $logMessage .= " - " . $data;
        }
    }

    // Essayer d'écrire dans le fichier de log avec gestion d'erreurs
    if (file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND) === false) {
        error_log("Impossible d'écrire dans le fichier de log: $logFile");
    }

    // Aussi afficher en cas d'erreur
    if (strpos(strtolower($message), 'error') !== false ||
        strpos(strtolower($message), 'erreur') !== false) {
        error_log($logMessage);
    }
}

// Démarrer le débogage
logDebug("=== DÉBUT DE LA VÉRIFICATION DU PAIEMENT ===");
logDebug("URL complète: " . $_SERVER['REQUEST_URI']);

// Récupération des paramètres
$invoiceId = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
$paymentIntent = isset($_GET['payment_intent']) ? $_GET['payment_intent'] : '';
$paymentIntentClientSecret = isset($_GET['payment_intent_client_secret']) ? $_GET['payment_intent_client_secret'] : '';
$redirectStatus = isset($_GET['redirect_status']) ? $_GET['redirect_status'] : '';

logDebug("Paramètres reçus", [
    'invoice_id' => $invoiceId,
    'payment_intent' => $paymentIntent,
    'payment_intent_client_secret' => substr($paymentIntentClientSecret, 0, 10) . '...',
    'redirect_status' => $redirectStatus
]);

// Vérifier que l'utilisateur est connecté et a accès à cette facture
if (!isset($_SESSION['societe_id']) || !$invoiceId) {
    logDebug("ERREUR: Session non valide ou facture non spécifiée", [
        'session_exists' => isset($_SESSION['societe_id']),
        'invoice_id' => $invoiceId
    ]);
    header('Location: /frontOffice/societe/dashboard.php');
    exit;
}

logDebug("Utilisateur authentifié", ['societe_id' => $_SESSION['societe_id']]);

// Configuration Stripe - À placer dans un fichier de configuration sécurisé en production
// Remplacer par la clé qui fonctionne
$stripeSecretKey = "sk_test_51PAXnVP0arAN6IC0PDTa298tpKjWwHWrxjomqgmhIMTIAQSU2xxOGabzyUuWkPdwFoWJIina6ryU9bBw8aDiOxzj00RplWbAQm";

// Initialiser Stripe
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
logDebug("Initialisation de Stripe avec la clé secrète");
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Récupération des frais associés à la facture
$associatedFees = [];
$hasAssociatedFees = false;

try {
    logDebug("Récupération des frais associés à la facture #$invoiceId");
    $ch = curl_init();
    $apiUrl = "http://" . $_SERVER['HTTP_HOST'] . "/api/invoice/getOtherFees.php?facture_id=" . $invoiceId;
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    logDebug("Appel API pour récupérer les frais", ['url' => $apiUrl]);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    logDebug("Réponse API frais (HTTP Code: {$httpCode})", $response);

    if ($curlError) {
        logDebug("ERREUR CURL lors de la récupération des frais", $curlError);
    }

    if ($httpCode == 200) {
        $associatedFees = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            logDebug("ERREUR: Impossible de décoder la réponse JSON des frais", json_last_error_msg());
            $associatedFees = extractLastValidJson($response);
        }

        $hasAssociatedFees = !empty($associatedFees);
        logDebug("Frais associés récupérés", [
            'count' => count($associatedFees),
            'has_fees' => $hasAssociatedFees
        ]);
    } else {
        logDebug("Aucun frais récupéré ou erreur API", ['http_code' => $httpCode]);
    }
} catch (Exception $e) {
    logDebug("EXCEPTION lors de la récupération des frais", $e->getMessage());
    // Ne rien faire, les frais ne sont pas essentiels pour afficher la page
}

// Vérifier le paiement via l'API Stripe
$isPaymentValid = false;
$paymentDetails = null;

try {
    logDebug("Vérification du paiement Stripe", ['payment_intent' => $paymentIntent]);

    if ($paymentIntent) {
        logDebug("Mise à jour du statut de la facture #$invoiceId");
        $updateRows = modifyInvoiceState($invoiceId, 'Payee');
        logDebug("Résultat mise à jour statut", ['rows_affected' => $updateRows]);

        if ($redirectStatus === 'succeeded') {
            // Si le paiement a été redirigé avec succès, on peut mettre à jour le statut de la facture
            logDebug("Paiement réussi, mise à jour du statut en 'Payee'");
            modifyInvoiceState($invoiceId, 'Payee');
        } elseif ($redirectStatus === 'failed') {
            logDebug("Paiement échoué, mise à jour du statut en 'Attente'");
            modifyInvoiceState($invoiceId, 'Attente');
        }

        // CORRECTION: ne pas utiliser $paymentIntentClientSecret pour récupérer le Payment Intent
        // Cette ligne était incorrecte:
        // $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentClientSecret);

        // Utiliser directement l'ID du payment intent:
        logDebug("Récupération des détails du PaymentIntent depuis Stripe");
        $paymentDetails = \Stripe\PaymentIntent::retrieve($paymentIntent);
        $redirectStatus = $paymentDetails->status;
        logDebug("Statut du paiement récupéré", ['status' => $redirectStatus]);

        $isPaymentValid = ($paymentDetails->status === 'succeeded');

        logDebug("Vérification du paiement terminée", [
            'is_valid' => $isPaymentValid,
            'payment_status' => $paymentDetails->status,
            'payment_id' => $paymentDetails->id,
            'amount' => $paymentDetails->amount / 100
        ]);

        // Si le paiement est réussi, mettre à jour le statut de la facture
        if ($isPaymentValid !== null) {
            // Créer un résultat similaire à celui qu'on obtiendrait de l'API
            $updateResult = [
                'success' => ($updateRows > 0),
                'message' => ($updateRows > 0) ? 'Statut de la facture mis à jour avec succès' : 'Aucune modification effectuée'
            ];
            logDebug("Résultat final de la mise à jour", $updateResult);
        }
    } else {
        logDebug("ERREUR: Aucun PaymentIntent fourni");
    }
} catch (Exception $e) {
    // Gérer l'erreur
    $error = $e->getMessage();
    logDebug("EXCEPTION lors de la vérification du paiement", [
        'error' => $error,
        'trace' => $e->getTraceAsString()
    ]);
}

// Ajouter ce code juste après la vérification de paiement réussi
if ($redirectStatus === 'succeeded' || $isPaymentValid) {
    // Si le paiement est réussi, traiter les frais pour mise à jour du plan et des limites
    if ($hasAssociatedFees) {
        logDebug("Traitement des frais pour mise à jour du plan et des limites");

        // Variables pour stocker les informations à mettre à jour
        $newPlan = null;
        $additionalEmployees = 0;
        $planUpdated = false;
        $employeeUpdated = false;

        foreach ($associatedFees as $fee) {
            $infosFrais = extraireInfosFrais($fee['nom']);

            // Si un plan est détecté dans les frais, on le prend en compte
            if (!empty($infosFrais['plan'])) {
                $newPlan = $infosFrais['plan'];
                logDebug("Plan détecté dans les frais", ['plan' => $newPlan]);
            }

            // Si des collaborateurs supplémentaires sont détectés
            if ($infosFrais['nb_collaborateurs'] > 0) {
                $additionalEmployees += $infosFrais['nb_collaborateurs'];
                logDebug("Collaborateurs supplémentaires détectés", ['count' => $infosFrais['nb_collaborateurs']]);
            }
        }

        // Mise à jour du plan si nécessaire
        if ($newPlan !== null) {
            $updatePlanResult = updateCompanyPlan($_SESSION['societe_id'], $newPlan);
            $planUpdated = ($updatePlanResult > 0);

            logDebug("Mise à jour du plan de l'entreprise", [
                'societe_id' => $_SESSION['societe_id'],
                'new_plan' => $newPlan,
                'success' => $planUpdated
            ]);
        }

        // Mise à jour du nombre d'employés si nécessaire
        if ($additionalEmployees > 0) {
            $updateEmployeeResult = addEmployeeToEmployeeCount($_SESSION['societe_id'], $additionalEmployees);
            $employeeUpdated = ($updateEmployeeResult > 0);

            logDebug("Mise à jour du nombre d'employés", [
                'societe_id' => $_SESSION['societe_id'],
                'additional_employees' => $additionalEmployees,
                'success' => $employeeUpdated
            ]);
        }

        // Stockage des résultats pour affichage à l'utilisateur
        $planEmployeeUpdateResult = [
            'plan_updated' => $planUpdated,
            'new_plan' => $newPlan,
            'employee_updated' => $employeeUpdated,
            'additional_employees' => $additionalEmployees
        ];
    }
}

logDebug("=== FIN DE LA VÉRIFICATION DU PAIEMENT ===");
?>

<div class="container-fluid">
    <div class="row">
        <!-- Inclusion de la sidebar -->
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Statut du paiement</h1>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Résultat du paiement</h5>
                </div>
                <div class="card-body">
                    <?php if ($redirectStatus === 'succeeded' || $isPaymentValid): ?>
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Paiement réussi!</h4>
                            <p>Votre paiement pour la facture #<?php echo $invoiceId; ?> a été traité avec succès.</p>
                            <hr>
                            <p class="mb-0">ID de transaction: <?php echo htmlspecialchars($paymentIntent); ?></p>
                            <?php if (isset($updateResult)): ?>
                                <p class="mt-2">Statut de la facture: <?php echo $updateResult['success'] ? 'Mise à jour réussie' : 'Erreur lors de la mise à jour'; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-times-circle me-2"></i> Échec du paiement</h4>
                            <p>Votre paiement pour la facture #<?php echo $invoiceId; ?> n'a pas pu être traité.</p>
                            <hr>
                            <p class="mb-0">
                                <?php if (isset($error)): ?>
                                    Erreur: <?php echo htmlspecialchars($error); ?>
                                <?php else: ?>
                                    Veuillez vérifier vos informations de paiement et réessayer.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h5>Que souhaitez-vous faire maintenant?</h5>
                        <div class="d-flex gap-2 mt-3">
                            <a href="/frontOffice/societe/invoices/invoices.php" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i> Retour à la liste des factures
                            </a>
                            <?php if ($redirectStatus !== 'succeeded' && !$isPaymentValid): ?>
                                <a href="javascript:void(0);" onclick="retryPayment(<?php echo $invoiceId; ?>)" class="btn btn-warning">
                                    <i class="fas fa-sync me-1"></i> Réessayer le paiement
                                </a>
                            <?php endif; ?>
                            <a href="/frontOffice/societe/dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-home me-1"></i> Tableau de bord
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($paymentDetails && is_object($paymentDetails)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Détails du paiement</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">ID du paiement</th>
                                    <td><?php echo htmlspecialchars($paymentDetails->id); ?></td>
                                </tr>
                                <tr>
                                    <th>Montant</th>
                                    <td><?php echo number_format($paymentDetails->amount / 100, 2, ',', ' '); ?> €</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        <span class="badge bg-<?php echo $paymentDetails->status === 'succeeded' ? 'success' : 'danger'; ?>">
                                            <?php echo htmlspecialchars($paymentDetails->status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td><?php echo date('d/m/Y H:i:s', $paymentDetails->created); ?></td>
                                </tr>
                                <tr>
                                    <th>Devise</th>
                                    <td><?php echo strtoupper(htmlspecialchars($paymentDetails->currency)); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($hasAssociatedFees): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5>Frais associés à cette facture</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Plan</th>
                        <th>Collaborateurs</th>
                        <th>Date de création</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($associatedFees as $fee):
                        // Application de la fonction d'extraction sur chaque frais
                        $infosFrais = extraireInfosFrais($fee['nom']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fee['id_frais']); ?></td>
                        <td><?php echo htmlspecialchars($fee['nom']); ?></td>
                        <td><?php echo htmlspecialchars($fee['description']); ?></td>
                        <td><?php echo number_format($fee['montant'], 2, ',', ' '); ?> €</td>
                        <td>
                            <?php if (!empty($infosFrais['plan'])): ?>
                                <span class="badge bg-<?php
                                    echo match($infosFrais['plan']) {
                                        'starter' => 'primary',
                                        'business' => 'success',
                                        'enterprise' => 'info',
                                        'premium' => 'warning',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst(htmlspecialchars($infosFrais['plan'])); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($infosFrais['nb_collaborateurs'] > 0): ?>
                                <span class="badge bg-light text-dark">
                                    <?php echo $infosFrais['nb_collaborateurs']; ?> collaborateur<?php echo $infosFrais['nb_collaborateurs'] > 1 ? 's' : ''; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($fee['date_creation'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($associatedFees) > 0): ?>
        <div class="mt-3 p-3 bg-light rounded">
            <h6>Répartition par plan:</h6>
            <?php
                // Analyse des plans utilisés
                $planCounts = [];
                $totalCollaborateurs = 0;

                foreach ($associatedFees as $fee) {
                    $info = extraireInfosFrais($fee['nom']);
                    if (!empty($info['plan'])) {
                        if (!isset($planCounts[$info['plan']])) {
                            $planCounts[$info['plan']] = 0;
                        }
                        $planCounts[$info['plan']]++;
                    }
                    $totalCollaborateurs += $info['nb_collaborateurs'];
                }

                // Affichage des compteurs par plan
                foreach ($planCounts as $plan => $count):
            ?>
                <div class="mb-1">
                    <span class="badge bg-<?php
                        echo match($plan) {
                            'starter' => 'primary',
                            'business' => 'success',
                            'enterprise' => 'info',
                            'premium' => 'warning',
                            default => 'secondary'
                        };
                    ?>">
                        <?php echo ucfirst($plan); ?>: <?php echo $count; ?> frais
                    </span>
                </div>
            <?php endforeach; ?>

            <div class="mt-2">
                <strong>Total des collaborateurs ajoutés:</strong> <?php echo $totalCollaborateurs; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
        </main>
    </div>
</div>

<script>
    function retryPayment(invoiceId) {
        // Redirection vers la page des factures avec l'ID de la facture
        window.location.href = '/frontOffice/societe/invoices/invoices.php?retry_payment=' + invoiceId;
    }
</script>

<?php
/**
 * Extrait le plan et le nombre de collaborateurs d'un nom de frais
 *
 * @param string $nomFrais Nom du frais (ex: "Frais Plan Starter - ajout de : 1 collaborateurs - Devis #15")
 * @return array Tableau associatif avec 'plan' et 'nb_collaborateurs'
 */
function extraireInfosFrais($nomFrais) {
    $infos = [
        'plan' => '',
        'nb_collaborateurs' => 0
    ];

    // Extraction du plan
    if (preg_match('/Frais Plan ([A-Za-z]+)/i', $nomFrais, $matches)) {
        $infos['plan'] = strtolower($matches[1]); // Conversion en minuscules
    }

    // Extraction du nombre de collaborateurs
    if (preg_match('/ajout de : (\d+) collaborateurs/i', $nomFrais, $matches)) {
        $infos['nb_collaborateurs'] = intval($matches[1]);
    }

    return $infos;
}
?>

<!-- Ajouter cette section dans la carte des détails du paiement, après l'affichage du paiement réussi -->
<?php if (isset($planEmployeeUpdateResult) && ($planEmployeeUpdateResult['plan_updated'] || $planEmployeeUpdateResult['employee_updated'])): ?>
<div class="alert alert-info mt-3" role="alert">
    <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Mises à jour de votre compte</h4>

    <?php if ($planEmployeeUpdateResult['plan_updated']): ?>
    <p>
        <i class="fas fa-arrow-circle-up me-1"></i>
        Votre plan a été mis à jour vers <strong><?php echo ucfirst(htmlspecialchars($planEmployeeUpdateResult['new_plan'])); ?></strong>.
    </p>
    <?php endif; ?>

    <?php if ($planEmployeeUpdateResult['employee_updated']): ?>
    <p>
        <i class="fas fa-user-plus me-1"></i>
        Votre limite de collaborateurs a été augmentée de <strong><?php echo $planEmployeeUpdateResult['additional_employees']; ?></strong>
        collaborateur<?php echo $planEmployeeUpdateResult['additional_employees'] > 1 ? 's' : ''; ?>.
    </p>
    <?php endif; ?>

    <hr>
    <p class="mb-0">
        Ces modifications sont appliquées immédiatement à votre compte.
        <a href="/frontOffice/societe/company-settings.php" class="alert-link">Voir les détails de votre abonnement</a>
    </p>
</div>
<?php endif; ?>
