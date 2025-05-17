<?php

function generateInseeToken() {
    $client_insee_id = 'LPWii353QBDYksSIfAKSfA1rRgca';
    $client_insee_secret = 'HAjsP89F69ml0OZMIKZBAfxjYAoa';

    // Encodage Base64 de l'ID et du secret
    $authorization = base64_encode("$client_insee_id:$client_insee_secret");

    $url = "https://api.insee.fr/token";
    $data = "grant_type=client_credentials";
    $headers = [
        "Authorization: Basic $authorization",
        "Content-Type: application/x-www-form-urlencoded"
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    // Capture les erreurs cURL
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ["error" => "cURL error: $error_msg"];
    }

    // Capture le code HTTP
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return ["error" => "HTTP error: $http_code", "response" => $response];
    }


    return json_decode($response, true);
}




function getInseeCompanyInfo($type, $code) {

    $response_token = generateInseeToken();

    echo json_encode($response_token);
    if (empty($response_token['access_token']) && empty($response_token['error']['access_token']))
        return $response_token;
        
    elseif (!empty($response_token['access_token']))
        $token = $response_token['access_token'];
    
    else $token = $response_token['error']['access_token'];
    
    $url = "https://api.insee.fr/entreprises/sirene/V3.11/$type/$code";
    $headers = ["Authorization: Bearer $token"];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getInseeCompanyInfoBySiret($siret) {
    return getInseeCompanyInfo("siret", $siret);
}

function getInseeCompanyInfoBySiren($siren) {
    return getInseeCompanyInfo("siren", $siren);    
}