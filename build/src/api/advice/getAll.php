<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";

$advices = getAllAdvices();


if (count($advices) > 0) {
    http_response_code(200);
    echo json_encode($advices);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Aucun conseil trouvé."));
}
?>
