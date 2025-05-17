<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/association.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();

if (!validateMandatoryParams($data, ['association_id'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$association_id = $data['association_id'];
$name = isset($data['name']) ? $data['name'] : null;
$description = isset($data['description']) ? $data['description'] : null;
$logo = isset($data['logo']) ? $data['logo'] : null;
$banniere = isset($data['banniere']) ? $data['banniere'] : null;
$desactivate = isset($data['desactivate']) ? $data['desactivate'] : null;

if ($name === null && $description === null && $logo === null && $banniere === null && $desactivate === null) {
    returnError(400, 'No data provided for update');
    return;
}

$updateAssociation = updateAssociation($association_id, $name, $description , $logo, $banniere, $desactivate);

if (!$updateAssociation) {
    // Log the error for debugging
    error_log("Failed to update association: " . print_r(error_get_last(), true));
    returnError(500, 'Could not update the association. Database operation failed.');
    return;
} else if ($updateAssociation == 4) {
    //deja les memes valeurs
    returnError(400, 'No changes made to the association.');
}
else{
    echo json_encode(['association_id' => $association_id]);
    http_response_code(200);
}
?>
