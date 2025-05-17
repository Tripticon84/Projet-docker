<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

header('Content-Type: application/json');

// Vérification des tokens d'authentification
acceptedTokens(true, false, false, false); // Admin

if (!methodIsAllowed('delete')) {
    returnError(405, "Méthode non autorisée");
}

$data = getBody();

if (!validateMandatoryParams($data, ['salon_id'])) {
    returnError(400, "L'ID du salon est requis");
}

$salon_id = $data['salon_id'];


if (getChat($salon_id) === null) {
    returnError(404, "Salon non trouvé");
}

$result = removeAllUsersFromChat($salon_id);

if ($result === null) {
    returnError(500,"Erreur lors de la suppression des utilisateurs du salon");
}

$result = deleteChat($salon_id);

if ($result > 0) {
    returnSuccess(['success' => 'Salon supprimé avec succès']);
} else {
    returnError(500, "Erreur lors de la suppression du salon");
}
