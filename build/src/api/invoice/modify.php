<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/estimate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('update')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, false, false);


$data = getBody();

if (!validateMandatoryParams($data, ['facture_id'])) {
    returnError(400, 'Missing required parameters');
    return;
}

$invoice_id = $data['facture_id'];
if (!getInvoiceById($invoice_id)) {
    returnError(404, 'Invoice not found');
    return;
}

$date_emission = isset($data['date_emission']) ? $data['date_emission'] : null;
$date_echeance = isset($data['date_echeance']) ? $data['date_echeance'] : null;
$montant = isset($data['montant']) ? $data['montant'] : null;
$montant_tva = isset($data['montant_tva']) ? $data['montant_tva'] : null;
$montant_ht = isset($data['montant_ht']) ? $data['montant_ht'] : null;
$statut = isset($data['statut']) ? $data['statut'] : null;
$methode_paiement = isset($data['methode_paiement']) ? $data['methode_paiement'] : null;
$id_devis = isset($data['id_devis']) ? $data['id_devis'] : null;
$id_prestataire = isset($data['id_prestataire']) ? $data['id_prestataire'] : null;


if ($id_devis !== null && !(getEstimateById($id_devis))) {
    returnError(404, 'Provider not found');
    return;
}
if ($id_prestataire !== null && !(getProviderById($id_prestataire))) {
    returnError(404, 'Provider not found');
    return;
}

// Vérifier si au moins un champ est fourni pour la mise à jour
if ($date_emission === null && $date_echeance === null && $montant === null && $montant_tva === null && $montant_ht === null && $statut === null && $methode_paiement === null && $id_devis === null && $id_prestataire === null) {
    returnError(400, 'No data provided for update');
    return;
}



if (isValidInvoiceStatus($statut)) {
    returnError(400, 'Invalid status provided: '.$statut);
    return;
}

$updatedInvoice = updateInvoice(
    $invoice_id,
    $date_emission,
    $date_echeance,
    $montant,
    $montant_tva,
    $montant_ht,
    $statut,
    $methode_paiement,
    $id_devis,
    $id_prestataire

);




if (!$updatedInvoice) {
    // Log the error for debugging
    error_log("Failed to update Invoice: " . print_r(error_get_last(), true));
    returnError(500, 'Could not update the Invoice. Database operation failed.');
    return;
}
else {
    // Générer un nouveau PDF après la mise à jour
    $pdfPath = null;
    $invoiceDetails = getInvoiceById($invoice_id);

    if ($invoiceDetails['id_prestataire']) {
        // Si c'est une facture pour un prestataire
        $pdfPath = generateAndSaveProviderInvoicePDF($invoice_id);
    } else {
        // Si c'est une facture pour une société
        $pdfPath = generateAndSaveCompanyInvoicePDF($invoice_id);
    }

    if (!$pdfPath) {
        error_log("Erreur lors de la génération du PDF pour la facture ID: " . $invoice_id);
    }

    echo json_encode(['facture_id' => $invoice_id, 'pdf_path' => $pdfPath]);
    http_response_code(200);
}
?>
