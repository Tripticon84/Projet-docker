<?php
// Désactiver tout affichage d'erreur dans la sortie
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Assurer un nettoyage complet
ob_start();
ob_clean();

header('Content-Type: application/json');

try {
    // Récupérer le paramètre desactivate
    $desactivate = isset($_GET['desactivate']) ? $_GET['desactivate'] : null;
    
    // Données de test statiques basées sur le paramètre desactivate
    if ($desactivate == '1') {
        // Retourner des activités inactives de test
        $data = [
            [
                "id" => 6, 
                "activite_id" => 6,
                "nom" => "Activité test désactivée",
                "type" => "test",
                "date" => "2025-06-30",
                "id_lieu" => 1,
                "id_devis" => 1,
                "id_prestataire" => 1,
                "desactivate" => 1
            ]
        ];
    } else {
        // Retourner des activités actives de test
        $data = [
            [
                "id" => 1, 
                "activite_id" => 1,
                "nom" => "Atelier gestion du stress",
                "type" => "Atelier collectif",
                "date" => "2025-04-20",
                "id_lieu" => 1,
                "id_devis" => 1,
                "id_prestataire" => 1,
                "desactivate" => 0
            ],
            [
                "id" => 2, 
                "activite_id" => 2,
                "nom" => "Séances de yoga",
                "type" => "Cours collectif",
                "date" => "2025-05-10",
                "id_lieu" => 2,
                "id_devis" => 2,
                "id_prestataire" => 5,
                "desactivate" => 0
            ],
            [
                "id" => 3, 
                "activite_id" => 3,
                "nom" => "Consultation nutrition",
                "type" => "Entretien individuel",
                "date" => "2025-03-25",
                "id_lieu" => 3,
                "id_devis" => 3,
                "id_prestataire" => 4,
                "desactivate" => 0
            ]
        ];
    }
    
    // Appliquer limit et offset aux données de test
    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
        $limit = intval($_GET['limit']);
        $offset = isset($_GET['offset']) && is_numeric($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        $data = array_slice($data, $offset, $limit);
    }
    
    // Sortie des données de test en JSON
    echo json_encode($data);
} catch (Exception $e) {
    // En cas d'erreur, renvoyer un tableau vide
    error_log("Erreur dans getAll.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode([]);
}
