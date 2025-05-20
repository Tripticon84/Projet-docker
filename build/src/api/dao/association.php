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
        $sql = "SELECT association_id, name, description, banniere, logo, date_creation, desactivate FROM association";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error instead of echoing it
        error_log("Error getting associations: " . $e->getMessage());
        return []; // Return empty array on error
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
            error_log("Execute failed in getAssociationById for ID: $association_id");
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            error_log("No association found with ID: $association_id");
        }
        return $result;
    } catch (PDOException $e) {
        error_log("Error in getAssociationById: " . $e->getMessage());
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

/**
 * Soft deletion of association by setting desactivate flag to 1
 * This preserves the association data but marks it as inactive
 */
function deleteAssociation($association_id)
{
    try {
        // Direct, simple database update - no extra complexity
        $db = getDatabaseConnection();
        $sql = "UPDATE association SET desactivate = 1 WHERE association_id = :id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute(['id' => $association_id]);
        
        if (!$res) {
            error_log("Failed to deactivate association ID: $association_id");
            return null;
        }
        
        // Skip event deactivation for now to isolate the issue
        // We'll handle events separately once basic functionality works
        
        return $stmt->rowCount(); // Will return number of affected rows
    } catch (Exception $e) {
        error_log("Error in deleteAssociation: " . $e->getMessage());
        return null;
    }
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
        // Log error instead of echoing it
        error_log("Error getting employees: " . $e->getMessage());
        return []; // Return empty array on error
    }
}

function getDonationsByAssociationId($association_id)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT d.don_id, d.montant, d.date, d.id_collaborateur,
                c.collaborateur_id, c.nom, c.prenom, c.email 
                FROM don d
                LEFT JOIN collaborateur c ON d.id_collaborateur = c.collaborateur_id 
                WHERE d.id_association = :association_id
                ORDER BY d.date DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':association_id', $association_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting donations: " . $e->getMessage());
        return []; // Return empty array on error
    }
}

function getTotalDonationsByAssociationId($association_id) 
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT COUNT(*) as donation_count, SUM(montant) as total_amount 
                FROM don 
                WHERE id_association = :association_id";
                
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':association_id', $association_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting donation totals: " . $e->getMessage());
        return ['donation_count' => 0, 'total_amount' => 0];
    }
}

function diagnosticDonationsByAssociation($association_id = null) 
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT d.*, a.name as association_name, c.nom, c.prenom 
                FROM don d
                LEFT JOIN association a ON d.id_association = a.association_id
                LEFT JOIN collaborateur c ON d.id_collaborateur = c.collaborateur_id";
                
        if ($association_id !== null) {
            $sql .= " WHERE d.id_association = :association_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':association_id', $association_id, PDO::PARAM_INT);
        } else {
            $stmt = $db->prepare($sql);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in diagnostic: " . $e->getMessage());
        return [];
    }
}


