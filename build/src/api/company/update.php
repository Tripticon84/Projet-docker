<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/siret.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();

$id = $data['id'];
$nom = isset($data['nom']) ? $data['nom'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$adresse = isset($data['adresse']) ? $data['adresse'] : null;
$contact_person = isset($data['contact_person']) ? $data['contact_person'] : null;
$password = !empty($data['password']) ? hashPassword($data['password']) : null;
$telephone = isset($data['telephone']) ? $data['telephone'] : null;
$siret = isset($data['siret']) ? $data['siret'] : null;

//verifier qu un champ est fourni pour la mise a jour
if ($nom === null && $email === null && $adresse === null && $contact_person === null && $password === null && $telephone === null && $siret === null) {
    returnError(400, 'No data provided for update');
    return;
}

// Vérifier l'id existe
$company = getSocietyById($id);
if (empty($company)) {
    returnError(400, 'company does not exist');
    return;
}

// Vérifier l'email n'existe pas
$company = getSocietyByEmail($email);
if (!empty($company) && $company['societe_id'] != $id) {
    returnError(400, 'company already exists');
    return;
}

// Vérifier le telephone n'existe pas
$company = getSocietyByTelephone($telephone);
if (!empty($company) && $company['societe_id'] != $id) {
    returnError(400, 'Telephone already exists');
    return;
}

// Vérification de la longueur du mot de passe
if ($password != null && strlen($data['password']) < 12) {
    returnError(400, 'Password must be at least 12 characters long');
    return;
}

// Vérifier le siret n'existe pas
$company = getCompanyBySiret($siret);
if (!empty($company) && $company['societe_id'] != $id) {
    returnError(400, 'SIRET already exists');
    return;
}

// Vérifier le siret est valide
if ($siret != null && getInseeCompanyInfoBySiret($siret) === null) {
    returnError(400, 'Invalid SIRET number');
    return;
}

$res = updateSociety($id, $nom, $email, $adresse, $contact_person, $password, $telephone, $siret);

if (!$res) {
    returnError(500, 'Could not update the company');
    return;
}else{
    echo json_encode(['id' => $id]);
    http_response_code(200);
}
