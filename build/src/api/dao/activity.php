<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";


function createActivity(string $nom, string $type, $date, $id_devis = null, $id_prestataire = null, $id_lieu = null)
{
    $db = getDatabaseConnection();
    $sql = "INSERT INTO activite (nom, type, date, id_devis, id_prestataire, id_lieu) VALUES (:nom, :type, :date, :id_devis, :id_prestataire, :id_lieu)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'nom' => $nom,
        'type' => $type,
        'date' => $date,
        'id_devis' => $id_devis,
        'id_prestataire' => $id_prestataire,
        'id_lieu' => $id_lieu
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function deleteActivity(int $id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE activite SET desactivate = 1 WHERE activite_id=:id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        "id" => $id
    ]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function activateActivity(int $id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE activite SET desactivate = 0 WHERE activite_id=:id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        "id" => $id
    ]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function updateActivity(int $activite_id, string $nom = null, string $type = null, $date = null, $id_prestataire = null, $id_devis = null, $desactivate = null, $id_lieu = null)
{

    $db = getDatabaseConnection();
    $params = ['id' => $activite_id];
    $setFields = [];

    if ($nom !== null) {
        $setFields[] = "nom = :nom";
        $params['nom'] = $nom;
    }

    if ($type !== null) {
        $setFields[] = "type = :type";
        $params['type'] = $type;
    }

    if ($date !== null) {
        $setFields[] = "date = :date";
        $params['date'] = $date;
    }

    if ($id_prestataire !== null) {
        $setFields[] = "id_prestataire = :id_prestataire";
        $params['id_prestataire'] = $id_prestataire;
    }

    if ($id_devis !== null) {
        $setFields[] = "id_devis = :id_devis";
        $params['id_devis'] = $id_devis;
    }

    if ($id_lieu !== null) {
        $setFields[] = "id_lieu = :id_lieu";
        $params['id_lieu'] = $id_lieu;
    }

    if ($desactivate !== null) {
        $setFields[] = "desactivate = :desactivate";
        $params['desactivate'] = $desactivate;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }


    $sql = "UPDATE activite SET " . implode(", ", $setFields) . " WHERE activite_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;

}

function getActivityById($id)
{
    try {
        $connection = getDatabaseConnection();
        $sql = "SELECT activite_id, nom, type, date, id_prestataire, id_devis, id_lieu, desactivate FROM activite WHERE activite_id = :id";
        $query = $connection->prepare($sql);
        $res = $query->execute(['id' => $id]);
        if ($res) {
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        error_log("Erreur SQL dans getActivityById: " . implode(" ", $query->errorInfo()));
        return null;
    } catch (PDOException $e) {
        error_log("Exception PDO dans getActivityById: " . $e->getMessage());
        return null;
    }
}

function getAllActivity($desactivate = null, $limit = null, $offset = null, $search = null)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT activite_id, nom, type, date, id_devis, id_prestataire, id_lieu, desactivate FROM activite";
        $params = [];
        $whereAdded = false;

        // S'assurer que desactivate est correctement traité comme un boolean/int
        if ($desactivate !== null) {
            // Convertir explicitement en entier 0 ou 1
            $desactivateValue = (is_numeric($desactivate)) ? intval($desactivate) : ($desactivate === 'true' ? 1 : 0);
            
            $sql .= " WHERE desactivate = :desactivate";
            $params['desactivate'] = $desactivateValue;
            $whereAdded = true;
            
            error_log("Filtre desactivate appliqué avec valeur: " . $desactivateValue);
        }

        if ($search !== null) {
            $sql .= $whereAdded ? " AND" : " WHERE";
            $sql .= " (nom LIKE :search OR type LIKE :search)";
            $params['search'] = "%$search%";
        }

        // Gestion des paramètres LIMIT et OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT " . (string) $limit;

            if ($offset !== null) {
                $sql .= " OFFSET " . (string) $offset;
            }
        }
        
        // Log de la requête SQL complète avec les paramètres
        $logQuery = $sql;
        foreach ($params as $key => $value) {
            $logQuery = str_replace(":$key", "'$value'", $logQuery);
        }
        error_log("Requête SQL exécutée: " . $logQuery);

        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if ($res) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Nombre d'activités récupérées: " . count($results));
            return $results;
        }
        error_log("Erreur SQL dans getAllActivity: " . implode(" ", $stmt->errorInfo()));
        return null;
    } catch (PDOException $e) {
        error_log("Exception PDO dans getAllActivity: " . $e->getMessage());
        return null;
    }
}

function getActivityByType($type, $limit = null, $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT activite_id, nom, type, date, id_prestataire, id_devis, id_lieu FROM activite WHERE type = :type";
    $params = ['type' => $type];

    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getActivityByDate($date, $limit = null, $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT activite_id, nom, type, date, id_prestataire, id_devis, id_lieu FROM activite WHERE date = :date";
    $params = ['date' => $date];

    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getActivityByPrice($minPrice, $maxPrice, $limit = null, $offset = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT a.activite_id, a.nom, a.type, a.date, a.id_prestataire, a.id_devis, a.id_lieu
            FROM activite a
            INNER JOIN devis d ON a.id_devis = d.devis_id
            WHERE d.montant BETWEEN :minPrice AND :maxPrice";
    $params = [
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice
    ];

    if ($limit !== null) {
        $sql .= " LIMIT " . (string) $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . (string) $offset;
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

