<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";


function createReport($description, $probleme,$date_signalement, $id_societe){
    $db = getDatabaseConnection();
    $sql = "INSERT INTO signalement (description, probleme, date_signalement, id_societe) VALUES (:description, :probleme,:date_signalement, :id_societe)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'description' => $description,
        'probleme' => $probleme,
        'date_signalement' => $date_signalement,
        'id_societe' => $id_societe
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}


function ChangeState(int $id,$statut)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE signalement SET statut = :statut WHERE signalement_id=:id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        "id" => $id,
        "statut" => $statut
    ]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function getReportById(int $id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT signalement_id, description, probleme, date_signalement, statut, id_societe FROM signalement WHERE signalement_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function checkState(int $id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT statut FROM signalement WHERE signalement_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function modifyReport(int $id, string $description = null, string $probleme = null, string $date_signalement = null, int $id_societe = null, $statut = null)
{
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($description !== null) {
        $setFields[] = "description = :description";
        $params['description'] = $description;
    }

    if ($probleme !== null) {
        $setFields[] = "probleme = :probleme";
        $params['probleme'] = $probleme;
    }

    if ($date_signalement !== null) {
        $setFields[] = "date_signalement = :date_signalement";
        $params['date_signalement'] = $date_signalement;
    }

    if ($id_societe !== null) {
        $setFields[] = "id_societe = :id_societe";
        $params['id_societe'] = $id_societe;
    }

    if ($statut !== null) {
        $setFields[] = "statut = :statut";
        $params['statut'] = $statut;
    }

    if (empty($setFields)) {
        return false; // No fields to update
    }

    $sql = "UPDATE signalement SET " . implode(", ", $setFields) . " WHERE signalement_id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

function getAllReports($probleme,$limit,$offset)
{
    $db = getDatabaseConnection();
    $sql = "SELECT signalement_id, description, probleme, date_signalement, id_societe,statut FROM signalement";
    $params = [];

    if (!empty($probleme)) {
        $sql .= " WHERE probleme LIKE :probleme";
        $params['probleme'] = "%" . $probleme . "%";
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

function getAllReportsByCompany($id_societe,$limit,$offset)
{
    $db = getDatabaseConnection();
    $sql = "SELECT signalement_id, description, probleme, date_signalement, id_societe,statut FROM signalement WHERE id_societe = :id_societe";
    $params = ['id_societe' => $id_societe];

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

function getReportsByStatus($statut, $limit = null, $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT signalement_id, description, probleme, date_signalement, id_societe, statut FROM signalement WHERE statut = :statut";

    // Gestion des paramètres LIMIT et OFFSET
    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['statut' => $statut]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}
