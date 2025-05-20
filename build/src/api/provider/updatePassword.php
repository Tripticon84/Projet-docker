<?php
header("Content-Type: application/json");
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/provider.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/hashPassword.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";

// Récupérer et décoder le corps de la requête
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier les données requises
if (!isset($data['prestataire_id']) || !isset($data['currentPassword']) || !isset($data['newPassword'])) {
    http_response_code(400);
    echo json_encode(["error" => "Données incomplètes"]);
    exit;
}

try {
    // Récupérer le prestataire
    $provider = getProviderById($data['prestataire_id']);
    if (!$provider) {
        http_response_code(404);
        echo json_encode(["error" => "Prestataire non trouvé"]);
        exit;
    }
    
    // Vérifier le mot de passe actuel
    $currentHashedPassword = hashPassword($data['currentPassword']);
    
    // Requête pour vérifier le mot de passe actuel
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT prestataire_id FROM prestataire WHERE prestataire_id = :id AND password = :password");
    $stmt->execute([
        'id' => $data['prestataire_id'],
        'password' => $currentHashedPassword
    ]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(401);
        echo json_encode(["error" => "Mot de passe actuel incorrect"]);
        exit;
    }
    
    // Mettre à jour le mot de passe
    $result = updateProvider(
        $data['prestataire_id'],
        null, // firstname
        null, // name
        null, // type
        null, // est_candidat
        null, // tarif
        null, // email
        null, // date_debut_disponibilite
        null, // date_fin_disponibilite
        $data['newPassword'], // password
        null  // description
    );
    
    if ($result !== null) {
        echo json_encode(["success" => true, "message" => "Mot de passe mis à jour"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erreur lors de la mise à jour du mot de passe"]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur: " . $e->getMessage()]);
}
