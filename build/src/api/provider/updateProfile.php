<?php
header("Content-Type: application/json");
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/provider.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";

// Récupérer et décoder le corps de la requête
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier les données requises
if (!isset($data['prestataire_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID du prestataire manquant"]);
    exit;
}

try {
    // Mise à jour du profil
    $result = updateProvider(
        $data['prestataire_id'],
        $data['firstname'] ?? null,
        $data['name'] ?? null,
        null, // type
        null, // est_candidat
        $data['tarif'] ?? null,
        $data['email'] ?? null,
        null, // date_debut_disponibilite
        null, // date_fin_disponibilite
        null, // password
        $data['description'] ?? null
    );
    
    if ($result !== null) {
        echo json_encode(["success" => true, "message" => "Profil mis à jour"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur lors de la mise à jour du profil"]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur: " . $e->getMessage()]);
}
