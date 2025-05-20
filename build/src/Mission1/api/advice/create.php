<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";


$data = json_decode(file_get_contents("php://input"));

// Vérifier si l'utilisateur est un collaborateur

if (!empty($data->question) && !empty($data->id_collaborateur)) {
    // Vérifier que la demande vient d'un collaborateur et non d'un admin
    $id = createAdvice(
        $data->question,
        $data->id_collaborateur
    );

    if ($id) {
        http_response_code(201);
        echo json_encode(array("message" => "Conseil créé avec succès.", "id" => $id));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Impossible de créer le conseil."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Données incomplètes ou accès non autorisé."));
}
?>
