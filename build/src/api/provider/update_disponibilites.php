<?php
// Headers requis pour l'API
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // En prod, faudrait être plus restrictif!
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

// On inclut les fichiers nécessaires
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/provider.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";

// Traitement des données reçues
// On récupère le contenu de la requête POST
$data = json_decode(file_get_contents("php://input"), true);

// Initialisation de la réponse
$response = ['success' => false];

// On vérifie si l'utilisateur est authentifié
$headers = getallheaders();
$token = null;

// On extrait le token de l'en-tête Authorization
if (isset($headers['Authorization'])) {
    $auth_header = $headers['Authorization'];
    if (strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7); // On enlève "Bearer " du début
    }
}

// Si pas de token, erreur d'authentification
if (!$token) {
    $response['error'] = "Vous devez être connecté pour effectuer cette action.";
    echo json_encode($response);
    exit;
}

// On récupère le prestataire avec ce token
$prestataire = getProviderByToken($token);

// Si on ne trouve pas le prestataire, erreur d'authentification
if (!$prestataire) {
    $response['error'] = "Session invalide ou expirée. Veuillez vous reconnecter.";
    echo json_encode($response);
    exit;
}

// Maintenant on peut traiter la requête de mise à jour des disponibilités
if (isset($data['date_debut_disponibilite']) && isset($data['date_fin_disponibilite'])) {
    $date_debut = $data['date_debut_disponibilite'];
    $date_fin = $data['date_fin_disponibilite'];
    
    // Validation des dates (côté serveur aussi pour être sûr!)
    if (strtotime($date_fin) <= strtotime($date_debut)) {
        $response['error'] = "La date de fin doit être après la date de début.";
        echo json_encode($response);
        exit;
    }
    
    // On utilise la fonction updateProvider déjà existante pour mettre à jour les dates
    $result = updateProvider(
        $prestataire['prestataire_id'], 
        null, // prenom - on ne le change pas
        null, // nom - on ne le change pas
        null, // type - on ne le change pas
        null, // est_candidat - on ne le change pas
        null, // tarif - on ne le change pas
        null, // email - on ne le change pas
        $date_debut, // date_debut_disponibilite
        $date_fin // date_fin_disponibilite
    );
    
    // Si la mise à jour a réussi
    if ($result) {
        $response['success'] = true;
        $response['message'] = "Vos disponibilités ont été mises à jour avec succès.";
    } else {
        $response['error'] = "Erreur lors de la mise à jour des disponibilités.";
    }
} else {
    $response['error'] = "Données manquantes. Veuillez fournir les dates de début et de fin.";
}

// On renvoie la réponse en JSON
echo json_encode($response);
?>
