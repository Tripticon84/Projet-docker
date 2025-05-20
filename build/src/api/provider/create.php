<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);

$data = getBody();

if (validateMandatoryParams($data, ['nom', 'prenom', 'email', 'password', 'est_candidat'])) {

    // Vérifier si l'email existe déjà
    $provider = getProviderByEmail($data['email']);
    if (!empty($provider)) {
        returnError(400, 'Email already exists');
        return;
    }

    // Vérification de la longueur du mot de passe
    if (strlen($data['password']) < 8) {
        returnError(400, 'Password must be at least 8 characters long');
        return;
    }

    // Récupération des paramètres optionnels
    $type = $data['type'] ?? null;
    $description = $data['description'] ?? null;
    $tarif = $data['tarif'] ?? null;
    $date_debut_disponibilite = $data['date_debut_disponibilite'] ?? null;
    $date_fin_disponibilite = $data['date_fin_disponibilite'] ?? null;

    // Création du prestataire
    $newProviderId = createProvider(
        $data['email'],
        $data['nom'],
        $data['prenom'],
        $type,
        $description,
        $tarif,
        $date_debut_disponibilite,
        $date_fin_disponibilite,
        $data['est_candidat'],
        $data['password']
    );

    if (!$newProviderId) {
        returnError(500, 'Could not create the provider');
        return;
    }

    echo json_encode(['prestataire_id' => $newProviderId]);
    http_response_code(201);
    exit;

} else {
    returnError(412, 'Mandatory parameters: nom, prenom, email, password, est_candidat');
}
