<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

// Récupérer les données de la requête
$data = getBody();

// Vérifier si l'ID est présent
if (!isset($data['prestataire_id']) || empty($data['prestataire_id'])) {
    returnError(400, 'Provider ID is required');
    return;
}

// Récupérer l'employee par son ID
$providerId = $data['prestataire_id'];
$existingProvider = getProviderById($providerId);

// Vérifier si l'employee existe
if (!$existingProvider) {
    returnError(404, 'Provider not found');
    return;
}

// Initialiser les variables avec null pour indiquer qu'elles ne sont pas à mettre à jour
$name = isset($data['nom']) ? $data['nom'] : null;
$firstName = isset($data['prenom']) ? $data['prenom'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$type = isset($data['type']) ? $data['type'] : null;
$price = isset($data['tarif']) ? $data['tarif'] : null;
$date_db = isset($data['date_debut_disponibilite']) ? $data['date_debut_disponibilite'] : null;
$date_fn = isset($data['date_fin_disponibilite']) ? $data['date_fin_disponibilite'] : null;
$est_candidat = isset($data['est_candidat']) ? $data['est_candidat'] : null;
$password = isset($data['password']) ? $data['password'] : null;

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($firstName === null && $name === null && $email === null && $type === null &&
    $price === null && $date_db === null && $date_fn === null && $est_candidat === null && $password === null) {
    returnError(400, 'No data provided for update');
    return;
}

// Vérifications conditionnelles uniquement si les données sont fournies

// Vérifier la longueur du mot de passe uniquement s'il est fourni
if ($password !== null && strlen($password) < 8) {
    returnError(400, 'Password must be at least 8 characters long');
    return;
}


// Vérifier l'email uniquement s'il est fourni
if ($email !== null) {
    $existingProviderEmail = getProviderByEmail($email);
    if ($existingProviderEmail && $existingProviderEmail['prestataire_id'] != $providerId) {
        returnError(400, 'Email already exists');
        return;
    }
    //verifier que l email est bien un email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        returnError(400, 'Email is not valid');
        return;
    }
}


//verifier que les dates sont bien des dates
if ($date_db !== null) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_db)) {
        returnError(400, 'Start date is not valid');
        return;
    }
}
if ($date_fn !== null) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_fn)) {
        returnError(400, 'end date is not valid');
        return;
    }
}


// Appeler updateProvider avec les paramètres corrects
$updateResult = updateProvider( $providerId,  $firstName, $name, $type, $est_candidat, $price, $email, $date_db, $date_fn, $password);  //attention à l'ordre des paramètres

// Vérifier le résultat de la mise à jour
if ($updateResult !== null) {
    // Récupérer les données mises à jour pour la confirmation
    $updatedProvider= getProviderById($providerId);

    if ($updatedProvider) {
        // Ne pas inclure le mot de passe dans la réponse
        unset($updatedProvider['password']);

        // Ajouter un indicateur de succès
        $updatedProvider['success'] = true;

        echo json_encode($updatedProvider);
        http_response_code(200);
    } else {
        // Cas rare où la mise à jour a réussi mais la récupération a échoué
        echo json_encode(['success' => true, 'id' => $providerId]);
        http_response_code(200);
    }
} else {
    returnError(500, 'Failed to update provider');
}
