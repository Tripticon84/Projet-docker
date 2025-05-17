<?php


function methodIsAllowed(string $action): bool
{
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($action) {
        case 'update':
            return $method == 'PATCH';
        case 'create':
            return $method == 'PUT';
        case 'read':
            return $method == 'GET';
        case 'delete':
            return $method == 'DELETE';
        case 'login':
            return $method == 'POST';
        default:
            return false;
    }
}

function getBody(): array
{
    $body = file_get_contents('php://input');
    return json_decode($body, true);
}

function returnError(int $code, string $message)
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

function returnSuccess($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
}


function validateMandatoryParams(array $data, array $mandatoryParams): bool
{
    foreach ($mandatoryParams as $param) {
        if (!isset($data[$param])) {
            return false;
        }
    }
    return true;
}


/**
 * Valide et traite les jetons d'authentification pour différents types d'utilisateurs.
 *
 * Cette fonction vérifie si le jeton Bearer fourni dans l'en-tête Authorization
 * correspond à une session utilisateur valide (admin, employé, entreprise ou fournisseur)
 * et vérifie si le jeton n'a pas expiré. Elle s'assure également que le type d'utilisateur
 * a la permission d'accéder à l'endpoint demandé.
 *
 * @param bool $admin    Si l'accès admin est autorisé (par défaut true)
 * @param bool $company  Si l'accès entreprise est autorisé (par défaut false)
 * @param bool $employee Si l'accès employé est autorisé (par défaut false)
 * @param bool $provider Si l'accès fournisseur est autorisé (par défaut false)
 *
 * @return void Retourne immédiatement en cas d'authentification réussie ou d'erreur
 *
 * @throws Retourne une erreur 401 si le jeton est manquant, invalide ou expiré
 * @throws Retourne une erreur 403 si le type d'utilisateur n'est pas autorisé à accéder
 * @throws Retourne une erreur 500 si impossible de récupérer la date d'expiration du jeton
 */
function acceptedTokens($admin = true, $company = false, $employee = false, $provider = false) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/admin.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/company.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';

    $debug = true;
    if ($debug) {
        return;
    }

    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($headers['authorization'])) {
        $token = str_replace('Bearer ', '', $headers['authorization']);
    } elseif (isset($_GET['token'])) {
        $token = $_GET['token'];
    } else {
        returnError(401, 'Unauthorized: Missing token');
        return;
    }

    $adminUser = getAdminByToken($token);
    if ($adminUser) {
        if (!$admin) {
            returnError(403, 'Unauthorized: Admin access not allowed');
            return;
        }
        $expirationDate = getExpirationByToken($token);
        if ($expirationDate) {
            $expiration = new DateTime($expirationDate['expiration']);
            if ($expiration < new DateTime()) {
                returnError(401, 'Unauthorized: Token expired');
                return;
            }
        } else {
            returnError(500, 'Internal Server Error: Unable to retrieve expiration date');
            return;
        }
        return; // Admin is authorized
    }

    $employeeUser = getEmployeeByToken($token);
    if ($employeeUser) {
        if (!$employee) {
            returnError(403, 'Unauthorized: Employee access not allowed');
            return;
        }
        $expirationDate = getEmployeeExpirationByToken($token);
        if ($expirationDate) {
            $expiration = new DateTime($expirationDate['expiration']);
            if ($expiration < new DateTime()) {
                returnError(401, 'Unauthorized: Token expired');
                return;
            }
        } else {
            returnError(500, 'Internal Server Error: Unable to retrieve expiration date');
            return;
        }
        return; // Employee is authorized
    }

    $companyUser = getCompanyByToken($token);
    if ($companyUser) {
        if (!$company) {
            returnError(403, 'Unauthorized: Company access not allowed');
            return;
        }
        $expirationDate = getCompanyExpirationByToken($token);
        if ($expirationDate) {
            $expiration = new DateTime($expirationDate['expiration']);
            if ($expiration < new DateTime()) {
                returnError(401, 'Unauthorized: Token expired');
                return;
            }
        } else {
            returnError(500, 'Internal Server Error: Unable to retrieve expiration date');
            return;
        }
        return; // Company is authorized
    }

    $providerUser = getProviderByToken($token);
    if ($providerUser) {
        if (!$provider) {
            returnError(403, 'Unauthorized: Provider access not allowed');
            return;
        }
        $expirationDate = getProviderExpirationByToken($token);
        if ($expirationDate) {
            $expiration = new DateTime($expirationDate['expiration']);
            if ($expiration < new DateTime()) {
                returnError(401, 'Unauthorized: Token expired');
                return;
            }
        } else {
            returnError(500, 'Internal Server Error: Unable to retrieve expiration date');
            return;
        }
        return;
    }

    // Si on arrive ici, le jeton n'est valide pour aucun utilisateurs
    returnError(401, 'Unauthorized: Invalid token');
}
