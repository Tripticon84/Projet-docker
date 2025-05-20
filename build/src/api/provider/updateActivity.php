<?php
// Inclusion des fichiers requis - on a besoin des fonctions pour les prestataires et des utilitaires serveur
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

// Définition du type de contenu en JSON pour la réponse
header('Content-Type: application/json');

// On vérifie que la méthode HTTP est bien POST 
// Les requêtes GET ne sont pas autorisées pour modifier des données
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError(405, 'Méthode non autorisée');
    return;
}

// Récupération des données JSON envoyées dans le corps de la requête
// file_get_contents('php://input') permet de lire les données brutes
// json_decode convertit le JSON en tableau associatif PHP
$data = json_decode(file_get_contents('php://input'), true);

// Vérification que les paramètres obligatoires sont bien présents
// Sans ID d'activité et date, on ne peut pas faire la mise à jour
if (!isset($data['activite_id']) || !isset($data['date'])) {
    returnError(400, 'Paramètres manquants: activite_id et date sont obligatoires');
    return;
}

$activityId = $data['activite_id'];
$date = $data['date'];

// Validation du format de date avec une expression régulière
// Format attendu: AAAA-MM-JJ (ex: 2023-12-31)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    returnError(400, 'Format de date invalide. Format requis: AAAA-MM-JJ');
    return;
}

// Appel à la fonction qui va mettre à jour la date dans la BDD
// Cette fonction est définie dans provider.php qu'on a inclus plus haut
$result = updateActivity($activityId, $date);

// Vérification du résultat et envoi de la réponse appropriée
if ($result !== null && $result > 0) {
    // Succès: on renvoie un message positif en JSON
    echo json_encode([
        'success' => true,
        'message' => 'Date de l\'activité mise à jour avec succès'
    ]);
} else {
    // Échec: on utilise notre fonction d'erreur pour envoyer un message d'erreur
    returnError(500, 'Échec lors de la mise à jour de la date de l\'activité');
}
?>
