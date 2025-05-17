<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, true, false, true);

$data = getBody();

// montant_ht est egal au la liste des frais selectionnes en amont(pour les abonnements recuperer la liste des collaborateurs de l'entreprise et multiplier par le nombre de collaborateurs)
if (validateMandatoryParams($data, ['montant_ht', 'id_societe'])) {

    if (!is_numeric($data['montant_ht'])) {
        returnError(400, 'montant must be a number');
        return;
    }

    if ($data['statut'] !== 'refusé' && $data['statut'] !== 'accepté' && $data['statut'] !== 'envoyé' && $data['statut'] !== 'brouillon') {
        returnError(400, 'statut must be refusé, accepté, envoyé or brouillon');
        return;
    }

    $company = getSocietyById($data['id_societe']);
    if (empty($company)) {
        returnError(400, 'company does not exist');
        return;
    }
    if ($company['nom'] === null) {
        returnError(400, 'company name is null');
        return;
    }

    if (empty($data['date_debut'])) {
        $data['date_debut'] = date('Y-m-d');
    }
    if (empty($data['date_fin'])) {
        $data['date_fin'] = date('Y-m-d', strtotime('+1 month'));
    }

    if (!is_numeric($data['is_contract'])) {
        returnError(400, 'is_contract must be a number');
        return;
    }

    if ($data['is_contract'] !== 0 && $data['is_contract'] !== 1) {
        returnError(400, 'is_contract must be 0 or 1');
        return;
    }





    $newEstimateId = createEstimate($data['date_debut'], $data['date_fin'], $data['statut'], $data['montant_ht'], $data['is_contract'], $data['id_societe'],$company['nom']);

    if (!$newEstimateId) {
        returnError(500, 'Could not create the estimate');
        return;
    }

    // Si des frais sont fournis, les associer au devis
    if (isset($data['frais_ids']) && is_array($data['frais_ids'])) {
        $res = attachFraisToEstimate($newEstimateId, $data['frais_ids']);
        if ($res === false) {
            // Supprimer le devis créé si l'association des frais échoue
            deleteEstimate($newEstimateId);
            returnError(500, 'Could not attach frais to the estimate');
            return;
        }
    }

    // Générer automatiquement le PDF du devis
    $pdfPath = generateAndSavePDF($newEstimateId);
    if (!$pdfPath) {
        error_log("Erreur lors de la génération du PDF pour le devis ID: " . $newEstimateId);
    }

    echo json_encode(['id' => $newEstimateId,
                      'pdf_path' => $pdfPath]);
    http_response_code(201);
    exit;

} else {
    returnError(412, 'Mandatory parameters : date_debut, date_fin, statut, montant, is_contract, id_societe');
}
