<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/siret.php';
// Ajouter en haut du fichier api/company/create.php, juste après les require_once
error_log("HTTP Method détectée: " . $_SERVER['REQUEST_METHOD']);
header('Content-Type: application/json');

if (!methodIsAllowed('create')) {
    returnError(405, 'Method not allowed');
    return;
}

// acceptedTokens(true, false, false, false);


$data = getBody();
$nom = $data['nom'];
$email = $data['email'];
$adresse = $data['adresse'];
$contact_person = $data['contact_person'];
$password = hashPassword($data['password']);
$telephone = $data['telephone'];
$siret = $data['siret'];
$desactivate = $data['desactivate'] ?? 0;
$employee_count = $data['employee_count'] ?? 0; // Default to 0 if not provided
$plan = $data['plan'] ?? 'starter'; // Default to 'starter' if not provided
// Default to 0 if not provided



if (validateMandatoryParams($data, ['nom', 'email', 'adresse', 'contact_person', 'password', 'telephone', 'siret','desactivate','employee_count','plan'])) {
    try {

        if (getCompanyBySiret($siret)) {
            returnError(400, 'SIRET already exists');
            return;
        }

        if (getInseeCompanyInfoBySiret($siret) === null) {
            returnError(400, 'Invalid SIRET number');
            return;
        }

        // Valider le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            returnError(400, 'Invalid email format');
            return;
        }

        // Vérifier l'email n'existe pas
        $society = getSocietyByEmail($email);
        if (!empty($society)) {
            returnError(400, 'Company already exists');
            return;
        }

        // Vérifier le telephone n'existe pas
        $society = getSocietyByTelephone($telephone);
        if (!empty($society)) {
            returnError(400, 'Telephone already exists');
            return;
        }

        // Vérification de la longueur du mot de passe
        if (strlen($data['password']) < 12) {
            returnError(400, 'Password must be at least 12 characters long');
            return;
        }

        if ($desactivate != 0 && $desactivate != 1) {
            returnError(400, 'Invalid desactivate value. Must be 0 or 1.');
            return;
        }

        if (empty($employee_count) || !is_numeric($employee_count) || $employee_count < 0) {
            returnError(400, 'Invalid employee count.');
            return;
        }

        if (empty($plan) || !is_string($plan)) {
            returnError(400, 'Invalid plan.');
            return;
        }
        if ($plan != 'basic' && $plan != 'premium' && $plan != 'starter') {
            returnError(400, 'Invalid plan. Must be "starter" or "basic" or "premium".');
            return;
        }

        $newSocietyId = createSociety($nom, $email, $adresse, $contact_person, $password, $telephone, $siret, $desactivate,$employee_count, $plan);

        if (!$newSocietyId) {
            // Log the error for debugging
            error_log("Failed to create society: " . print_r(error_get_last(), true));
            returnError(500, 'Could not create the Company. Database operation failed.');
            return;
        }

        echo json_encode(['societe_id' => $newSocietyId]);
        http_response_code(201);
        exit;
    } catch (Exception $e) {
        error_log("Exception while creating company: " . $e->getMessage());
        returnError(500, 'Internal server error: ' . $e->getMessage());
        return;
    }
} else {
    returnError(412, 'Mandatory parameters: nom, email, adresse, contact_person, password, telephone');
}
?>
