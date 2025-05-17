<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";

function createCost($name, $amount, $description, $isSubscription)
{
    try {
        $db = getDatabaseConnection();
        $sql = "INSERT INTO frais (nom, montant, description, est_abonnement,date_creation) VALUES (:name, :amount, :description, :isSubscription, :date_creation)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'amount' => $amount,
            'description' => $description,
            'isSubscription' => $isSubscription,
            'date_creation' => date('Y-m-d H:i:s')
        ]);
        return $db->lastInsertId(); // Return the ID of the newly created cost
    }
    catch (PDOException $e) {
        echo "Erreur lors de la création du frais : " . $e->getMessage();
        return null;
    }
}

function getAllCosts($societyId)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT id, society_id, name, amount, invoice_id, created_at FROM other_costs WHERE society_id = :society_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des frais : " . $e->getMessage();
        return [];
    }
}

function getAllCostsExceptSubscriptions($societyId)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description 
                FROM frais f
                INNER JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
                INNER JOIN devis d ON ifd.id_devis = d.devis_id
                WHERE d.id_societe = :society_id AND f.est_abonnement = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des frais : " . $e->getMessage();
        return [];
    }
}

function getAllSubscription($societyId)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description 
                FROM frais f
                INNER JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
                INNER JOIN devis d ON ifd.id_devis = d.devis_id
                WHERE d.id_societe = :society_id AND f.est_abonnement = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des abonnements : " . $e->getMessage();
        return [];
    }
}

function getCostById($costId)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT frais_id, nom, montant, date_creation, description, est_abonnement FROM frais WHERE frais_id = :frais_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['frais_id' => $costId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération du frais : " . $e->getMessage();
        return null;
    }
}

function updateCost($costId, ?string $name = null, ?float $amount = null, int $isSubscription = null, string $description = null)
{
    try {
        $db = getDatabaseConnection();

        $currentCost = getCostById($costId);
        if (!$currentCost) {
            return null; // Cost not found
        }

        $params = ['frais_id' => $costId];
        $setFields = [];

        if ($name !== null) {
            $setFields[] = "nom = :nom";
            $params['nom'] = $name;
        }

        if ($amount !== null) {
            $setFields[] = "montant = :montant";
            $params['montant'] = $amount;
        }

        if ($isSubscription !== null) {
            $setFields[] = "est_abonnement = :est_abonnement";
            $params['est_abonnement'] = $isSubscription;
        }

        if ($description !== null) {
            $setFields[] = "description = :description";
            $params['description'] = $description;
        }

        if (empty($setFields)) {
            return 0; // Nothing to update
        }

        $sql = "UPDATE frais SET " . implode(", ", $setFields) . " WHERE frais_id = :frais_id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if ($res) {
            return $stmt->rowCount();
        }
        return null;
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour du frais : " . $e->getMessage();
        return null;
    }
}

function deleteCost($costId)
{
    try {
        $db = getDatabaseConnection();
        $sql = "DELETE FROM frais WHERE frais_id = :id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute(['id' => $costId]);
        return $res ? $stmt->rowCount() : null;
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression du frais : " . $e->getMessage();
        return null;
    }
}

function getCostByCompanyId($companyId, $isSubscription = null)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description, f.est_abonnement 
                FROM frais f
                INNER JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
                INNER JOIN devis d ON ifd.id_devis = d.devis_id
                WHERE d.id_societe = :company_id";
        
        // Add filter for subscription/non-subscription costs if parameter is provided
        if ($isSubscription !== null) {
            $sql .= " AND f.est_abonnement = :is_subscription";
        }
        
        $stmt = $db->prepare($sql);
        $params = ['company_id' => $companyId];
        
        if ($isSubscription !== null) {
            $params['is_subscription'] = $isSubscription;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des frais : " . $e->getMessage();
        return [];
    }
}
