<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, true, true);


// Vérification de l'ID de la societe
if (!isset($_GET['societe_id']) || empty($_GET['societe_id'])) {
    returnError(400, 'Company ID not provided');
    return;
}

$companyId = intval($_GET['societe_id']);
$company = getSociety($companyId);

if (!$company) {
    returnError(404, 'Company not found');
    return;
}

$result = [
    "societe_id" => $company['societe_id'],
    "nom" => $company['nom'],
    "contact_person" => $company['contact_person'],
    "adresse" => $company['adresse'],
    "email" => $company['email'],
    "telephone" => $company['telephone'],
    "date_creation" => $company['date_creation'],
    "siret" => $company['siret'],
    "desactivate" => $company['desactivate']
];

echo json_encode($result);
http_response_code(200);
