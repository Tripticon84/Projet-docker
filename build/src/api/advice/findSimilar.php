<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->query) && strlen($data->query) >= 5) {
    // Recherche de conseils similaires
    $similarAdvices = findSimilarAdvices($data->query);
    
    if (count($similarAdvices) > 0) {
        http_response_code(200);
        echo json_encode(array(
            "message" => "Conseils similaires trouvés.",
            "results" => $similarAdvices
        ));
    } else {
        http_response_code(200);
        echo json_encode(array(
            "message" => "Aucun conseil similaire trouvé.",
            "results" => []
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Requête trop courte ou incomplète."));
}
?>
