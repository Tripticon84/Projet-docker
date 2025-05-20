<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';


header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, false);

$data = getBody();

$devis_id = $data['devis_id'];
$date_debut = isset($data['date_debut']) ? $data['date_debut'] : null;
$date_fin = isset($data['date_fin']) ? $data['date_fin'] : null;
$statut = isset($data['statut']) ? $data['statut'] : null;
$montant_ht = isset($data['montant_ht']) ? $data['montant_ht'] : null;

// Si montant_ht est fourni, on ignore complètement montant et montant_tva
// car ils seront automatiquement recalculés dans updateEstimate
$montant = null;
$montant_tva = null;

// On récupère montant et montant_tva seulement si montant_ht n'est PAS fourni
if ($montant_ht === null) {
    $montant = isset($data['montant']) ? $data['montant'] : null;
    $montant_tva = isset($data['montant_tva']) ? $data['montant_tva'] : null;
}

$is_contract = !empty($data['is_contract']) ? $data['is_contract'] : null;
$id_societe = isset($data['id_societe']) ? $data['id_societe'] : null;
$fichier = isset($data['fichier']) ? $data['fichier'] : null;

// Simplifier la vérification: n'avoir aucun champ fourni pour la mise à jour
if ($date_debut === null && $date_fin === null && $statut === null &&
    $montant_ht === null && $montant === null && $montant_tva === null &&
    $is_contract === null && $id_societe === null && $fichier === null) {
    returnError(400, 'No data provided for update');
    return;
}

// Vérifier l'id existe
$estimate = getEstimateById($devis_id);

if (empty($estimate)) {
    returnError(400, 'estimate does not exist');
    return;
}

if ($is_contract != null && $is_contract != 1 && $is_contract != 0) {
    returnError(400, 'is_contract must be 0 or 1');
    return;
}

if ($id_societe != null && !is_numeric($id_societe)) {
    returnError(400, 'id_societe must be an integer');
    return;
}

// Vérification des montants
if ($montant_ht !== null) {
    if (!is_numeric($montant_ht)) {
        returnError(400, 'montant_ht must be a number');
        return;
    }
    // Quand montant_ht est fourni, on ne vérifie pas montant et montant_tva
    // car ils seront ignorés et recalculés dans updateEstimate
} else {
    // Vérifications de montant et montant_tva seulement si montant_ht n'est pas fourni
    if ($montant !== null && !is_numeric($montant)) {
        returnError(400, 'montant must be a number');
        return;
    }

    if ($montant_tva !== null && !is_numeric($montant_tva)) {
        returnError(400, 'montant_tva must be a number');
        return;
    }
}

if ($date_debut != null && !DateTime::createFromFormat('Y-m-d', $date_debut)) {
    returnError(400, 'date_debut must be a date');
    return;
}

if ($date_fin != null && !DateTime::createFromFormat('Y-m-d', $date_fin)) {
    returnError(400, 'date_fin must be a date');
    return;
}

if ($date_debut != null && $date_fin != null && $date_debut > $date_fin) {
    returnError(400, 'date_debut must be before date_fin');
    return;
}

if ($id_societe != null && !getSocietyById($id_societe)) {
    returnError(400, 'id_societe does not exist');
    return;
}

if ($statut != null && $statut !== 'refusé' && $statut !== 'accepté' && $statut !== 'envoyé' && $statut !== 'brouillon') {
    returnError(400, 'statut must be refusé, accepté, envoyé or brouillon');
    return;
}

// Toujours passer null pour montant et montant_tva car ils seront
// recalculés automatiquement si montant_ht est fourni
$res = updateEstimate($date_debut, $date_fin, $statut, $montant_ht, $is_contract, $id_societe, $fichier, $devis_id);

if (!$res) {
    returnError(500, 'Failed to update estimate');
    return;
}

// Si des frais sont fournis, mettre à jour les associations
if (isset($data['frais_ids']) && is_array($data['frais_ids'])) {
    $res = attachFraisToEstimate($devis_id, $data['frais_ids']);
    if (!$res) {
        returnError(500, 'Failed to update frais associations');
        return;
    }
}

// Générer automatiquement un nouveau PDF du devis mis à jour
$pdfPath = generateAndSavePDF($devis_id);
if (!$pdfPath) {
    error_log("Erreur lors de la génération du PDF pour le devis ID: " . $devis_id);
}

echo json_encode([
    'success' => "Le devis id : " . $devis_id . " a été mis à jour avec succès",
    'pdf_path' => $pdfPath
]);
http_response_code(200);
