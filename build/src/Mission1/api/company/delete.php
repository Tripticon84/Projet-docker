<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';


header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);


$data = getBody();
$id = $data['societe_id'];

if (validateMandatoryParams($data, ['societe_id'])) {

    // Vérifier l'id existe
    $society = getSocietyById($id);
    if (empty($society)) {
        returnError(400, 'Company does not exist');
        return;
    }

    $res = deleteSociety($id);

    if (!$res) {
        returnError(500, 'Could not delete the Company');
        return;
    }else if ($res == 4) {
        returnError(400, 'Company already deleted.');
    }

    echo json_encode(['id' => $id]);
    http_response_code(200);
    exit;
} else {
    returnError(412, 'Mandatory parameters: id');
}
