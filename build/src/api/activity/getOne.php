<?php
// Désactiver tout affichage d'erreur dans la sortie
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Assurer un nettoyage complet
ob_start();
ob_clean();

header('Content-Type: application/json');

try {
    // Récupérer l'ID de l'activité
    $activityId = isset($_GET['activite_id']) ? $_GET['activite_id'] : null;
    
    // Vérifier si l'ID est fourni
    if (!isset($activityId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        exit;
    }
    
    // Données de test statiques
    $activities = [
        1 => [
            "id" => 1,
            "activite_id" => 1,
            "nom" => "Atelier gestion du stress",
            "type" => "Atelier collectif",
            "date" => "2025-04-20",
            "id_lieu" => 1,
            "id_devis" => 1,
            "id_prestataire" => 1,
            "desactivate" => 0,
            "refusee" => 0
        ],
        2 => [
            "id" => 2,
            "activite_id" => 2,
            "nom" => "Séances de yoga",
            "type" => "Cours collectif",
            "date" => "2025-05-10",
            "id_lieu" => 2,
            "id_devis" => 2,
            "id_prestataire" => 5,
            "desactivate" => 0,
            "refusee" => 0
        ],
        6 => [
            "id" => 6,
            "activite_id" => 6,
            "nom" => "Activité test désactivée",
            "type" => "test",
            "date" => "2025-06-30",
            "id_lieu" => 1,
            "id_devis" => 1,
            "id_prestataire" => 1,
            "desactivate" => 1,
            "refusee" => 0
        ]
    ];
    
    // Vérifier si l'activité existe dans nos données de test
    if (isset($activities[$activityId])) {
        echo json_encode($activities[$activityId]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Activity not found']);
    }
} catch (Exception $e) {
    error_log("Erreur dans getOne.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
