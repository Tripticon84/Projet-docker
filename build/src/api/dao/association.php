<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/dao/event.php";

function createAssociation($name, $description, $logo, $banniere)
{
    try {
        $db = getDatabaseConnection();
        $sql = "INSERT INTO association (name, description,logo,banniere,date_creation) VALUES (:name, :description , :logo, :banniere, NOW())";
        $stmt = $db->prepare($sql);

        $res = $stmt->execute(['name' => $name, 'description' => $description, 'logo' => $logo, 'banniere' => $banniere]);
        if ($res) {
            return $db->lastInsertId();
        }
    } catch (PDOException $e) {
        echo "Erreur lors de la créartion de l'association: " . $e->getMessage();
        return false;
    }
}

function getAllAssociations()
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT association_id, name,description,banniere,logo,date_creation,desactivate FROM association";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la recuperation des associations : " . $e->getMessage();
        return [];
    }
}


function getAssociationById($association_id)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT association_id, name, description, logo, banniere, date_creation, desactivate FROM association WHERE association_id = :association_id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute(['association_id' => $association_id]);
        if (!$res) {
            return null;
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération de l'association: " . $e->getMessage();
        return null;
    }
}

function updateAssociation($association_id, ?string $name = null, ?string $description = null, ?string $logo = null, ?string $banniere = null, ?int $desactivate = null)
{
    $db = getDatabaseConnection();

    // Récupérer les valeurs actuelles de l'association
    $currentAssociation = getAssociationById($association_id);
    if (!$currentAssociation) {
        return null; // Association introuvable
    }

    // Vérifier si les valeurs sont identiques
    if (
        ($name === null || $name === $currentAssociation['name']) &&
        ($description === null || $description === $currentAssociation['description']) &&
        ($logo === null || $logo === $currentAssociation['logo']) &&
        ($banniere === null || $banniere === $currentAssociation['banniere']) &&
        ($desactivate === null || $desactivate === $currentAssociation['desactivate'])
    ) {
        return 4; // Les valeurs sont identiques
    }

    $params = ['association_id' => $association_id];
    $setFields = [];

    if ($name !== null) {
        $setFields[] = "name = :name";
        $params['name'] = $name;
    }

    if ($description !== null) {
        $setFields[] = "description = :description";
        $params['description'] = $description;
    }

    if ($logo !== null) {
        $setFields[] = "logo = :logo";
        $params['logo'] = $logo;
    }

    if ($banniere !== null) {
        $setFields[] = "banniere = :banniere";
        $params['banniere'] = $banniere;
    }

    if ($desactivate !== null) {
        $setFields[] = "desactivate = :desactivate";
        $params['desactivate'] = $desactivate;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE association SET " . implode(", ", $setFields) . " WHERE association_id = :association_id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function deleteAssociation($association_id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE association SET desactivate = 1 WHERE association_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $association_id]);
    if (!$res) {
        return null;
    }
    $resultat=desactivateEventFromAssociation($association_id);
    if ($resultat && $res) {
        return $stmt->rowCount();
    }
    return null;
}

function getAssociationByName($name)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT name, description, logo,banniere, date_creation, desactivate FROM association WHERE name = :name";
        $stmt = $db->prepare($sql);
        $stmt->execute(['name' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération de l'association: " . $e->getMessage();
        return null;
    }
}

function getEmployeesByAssociation($association_id, $limit = null, $offset = null)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT c.collaborateur_id, c.nom, c.prenom, c.username, c.email, c.role, c.telephone, c.id_societe, c.date_creation
                FROM collaborateur c
                JOIN participe_association pa ON c.collaborateur_id = pa.id_collaborateur
                WHERE pa.id_association = :association_id AND c.desactivate = 0";

        // Ajouter la pagination si les paramètres sont fournis
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':association_id', $association_id, PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des employés de l'association: " . $e->getMessage();
        return [];
    }
}


