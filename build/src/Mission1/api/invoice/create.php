<?php
// filepath: d:\ESGI\Projet-Annuel\Mission1\api\invoice\simple_create.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

// Désactiver le reporting d'erreurs pour éviter les erreurs 500
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

// Permettre les requêtes PUT pour la création
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Récupération des données
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Vérifier les paramètres requis minimums
$requiredParams = ['date_emission', 'date_echeance', 'statut', 'methode_paiement', 'id_devis'];
$missingParams = [];

foreach ($requiredParams as $param) {
    if (!isset($data[$param]) || empty($data[$param])) {
        $missingParams[] = $param;
    }
}

if (!empty($missingParams)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters: ' . implode(', ', $missingParams)]);
    exit;
}

try {
    // Créer la facture en base de données
    $newInvoiceId = createInvoice(
        $data['date_emission'],
        $data['date_echeance'],
        $data['montant'] ?? 0,
        $data['montant_tva'] ?? 0,
        $data['montant_ht'] ?? 0,
        $data['statut'],
        $data['methode_paiement'],
        $data['id_devis'],
        $data['id_prestataire'] ?? null
    );

    if (!$newInvoiceId) {
        throw new Exception("Erreur lors de la création de la facture en base de données");
    }

    // Retourner le succès avec l'ID
    http_response_code(201);
    echo json_encode(['id' => $newInvoiceId, 'message' => 'Facture créée avec succès']);
    exit;

} catch (Exception $e) {
    // Log l'erreur dans un fichier
    error_log("Erreur création facture: " . $e->getMessage());
    
    // Répondre avec l'erreur
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}