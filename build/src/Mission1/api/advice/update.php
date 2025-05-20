<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";


$data = json_decode(file_get_contents("php://input"));

if (!empty($data->conseil_id) && (!empty($data->question) || !empty($data->reponse))) {
    $id_admin = !empty($data->id_admin) ? $data->id_admin : null;
    
    $result = updateAdvice(
        $data->conseil_id,
        $data->question,
        $data->reponse,
        $id_admin
    );

    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => "Conseil mis à jour avec succès."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Impossible de mettre à jour le conseil."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Données incomplètes."));
}
?>
