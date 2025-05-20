<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/advice.php";


$id = isset($_GET['id']) ? $_GET['id'] : die();

$advice_data = getAdviceById($id);

if ($advice_data) {
    http_response_code(200);
    echo json_encode($advice_data);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Conseil non trouvé."));
}
?>
