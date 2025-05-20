<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";


$data = json_decode(file_get_contents("php://input"));

if (!empty($data->conseil_id) && !empty($data->reponse) && !empty($data->id_admin)) {
    if (answerAdvice($data->conseil_id, $data->reponse, $data->id_admin)) {
        http_response_code(200);
        echo json_encode(array("message" => "Réponse ajoutée avec succès."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Impossible d'ajouter la réponse."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Données incomplètes."));
}
?>
