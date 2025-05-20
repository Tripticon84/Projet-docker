<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";

function createPlace($adress, $city, $postalCode)
{

        $db = getDatabaseConnection();
        $sql = "INSERT INTO lieu (adresse, ville, code_postal) VALUES (:adress, :city, :postal_code)";
        $stmt = $db->prepare($sql);
        $res=$stmt->execute(['adress' => $adress, 'city' => $city, 'postal_code' => $postalCode]);
        if($res){
            return $db->lastInsertId();
        }
        return null;
}

function getPlaceById(int $id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT lieu_id,adresse,ville,code_postal FROM lieu WHERE lieu_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function getAllPlace(string $adress = "", int $limit = null, int $offset = null)        //tout les params sont optionnels: le premier pour filtrer par username, le deuxième pour définir la limite de résultats et le dernier pour définir où on commence (utile pour la pagination)
{
    $db = getDatabaseConnection();
    $sql = "SELECT lieu_id, adresse, ville, code_postal FROM lieu";
    $params = [];

    if (!empty($adress)) {
        $sql .= " WHERE adresse LIKE :adress";
        $params['adress'] = "%" . $adress . "%";
    }

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);  // Seuls les paramètres username seront utilisés

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}


function updatePlace( $id, ?string $adress = null, ?string $city = null, ?int $postalCode = null)
{
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($adress !== null) {
        $setFields[] = "adresse = :adress";
        $params['adress'] = $adress;
    }

    if ($city !== null) {
        $setFields[] = "ville = :city";
        $params['city'] = $city;
    }

    if ($postalCode !== null) {
        $setFields[] = "code_postal = :postalCode";
        $params['postalCode'] = $postalCode;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }


    $sql = "UPDATE lieu SET " . implode(", ", $setFields) . " WHERE lieu_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function deletePlace(int $id)
{
    $db = getDatabaseConnection();
    $sql = "DELETE FROM lieu WHERE lieu_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}
