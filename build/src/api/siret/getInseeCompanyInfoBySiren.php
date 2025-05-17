<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/siret.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
header("Content-Type: application/json");

acceptedTokens(true, false, false, false);

if (empty($_GET['siren']))
    returnError(400, 'Mandatory parameter : siren');

$siren = str_replace(' ', '', $_GET["siren"]);

$response = getInseeCompanyInfoBySiren($siren);

if ($response === null) returnError(503, 'No response from Insee api');

http_response_code(200);
echo json_encode($response);
