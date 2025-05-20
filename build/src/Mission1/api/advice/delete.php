<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";


$data = json_decode(file_get_contents("php://input"));

if (!empty($data->conseil_id)) {
    if (deleteAdvice($data->conseil_id)) {
        http_response_code(200);
        echo json_encode(array("message" => "Conseil supprimé avec succès."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Impossible de supprimer le conseil."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Impossible de supprimer le conseil. ID non fourni."));
}
?>
