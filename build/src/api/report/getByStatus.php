<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/report.php";

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$limit = null;
$offset = null;


// Récupération du paramètre statut
if (!isset($_GET['statut'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Le paramètre statut est requis']);
    exit;
}

$statut = $_GET['statut'];

// Vérification que le statut est valide
$valid_status = ['non_traite', 'en_cours', 'resolu', 'annuler'];
if (!in_array($statut, $valid_status)) {
    http_response_code(400);
    echo json_encode(['error' => 'Statut invalide. Les valeurs acceptées sont: non_traite, en_cours, resolu, annuler']);
    exit;
}

if (isset($_GET['limit'])) {
    $limit = $_GET['limit'];
}
if (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
}


// Récupération des signalements par statut
$reports = getReportsByStatus($statut, $limit, $offset);

if ($reports === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des signalements']);
    exit;
}

// Retourne les données en format JSON
header('Content-Type: application/json');
echo json_encode($reports);
