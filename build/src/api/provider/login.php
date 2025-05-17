<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/stringUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/hashPassword.php';
header("Content-Type: application/json");

if (!methodIsAllowed('login')) {
    returnError(405, 'Method not allowed');
}

$data = getBody();

if (!validateMandatoryParams($data, ['email', 'password'])) {
    returnError(400, 'Mandatory parameters : email, password');
}

$email = trim($data['email']);
$password = trim($data['password']);
$passwordHashed = hashPassword($password); // Hash the password before checking

$provider = findProviderByCredentials($email, $passwordHashed);
if (!$provider) {
    returnError(401, 'Invalid credentials');
}
$providerId = $provider['prestataire_id']; // Corriger 'provider_id' en 'prestataire_id'

$token = date('d/M/Y h:m:s') . '_' . $providerId . '_' . generateRandomString(100);
$tokenHashed = hash('md5', $token);

$res = setProviderSession($providerId, $tokenHashed);
if (!$res) {
    returnError(500, 'An error has occurred');
}

returnSuccess(
    [
        'token' => $tokenHashed,
        'date' => date_add(
            date_create("now", new DateTimeZone('Europe/Paris')),
            DateInterval::createFromDateString('3 hour')
        )
    ]
);
