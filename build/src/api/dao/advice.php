<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";

function createAdvice($question, $id_collaborateur)
{
    try {
        $db = getDatabaseConnection();
        $date_creation = date('Y-m-d H:i:s'); // Format de date SQL
        $sql = "INSERT INTO conseil (question, id_collaborateur, date_creation) VALUES (:question, :id_collaborateur, :date_creation)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([
            'question' => $question,
            'id_collaborateur' => $id_collaborateur,
            'date_creation' => $date_creation
        ]);
        if ($res) {
            return $db->lastInsertId();
        }
        return false;
    } catch (PDOException $e) {
        echo "Erreur lors de la création du conseil: " . $e->getMessage();
        return false;
    }
}

function getAllAdvices()
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT c.*, col.nom AS collaborateur_nom, col.prenom AS collaborateur_prenom, 
                a.username AS admin_username 
                FROM conseil c 
                LEFT JOIN collaborateur col ON c.id_collaborateur = col.collaborateur_id 
                LEFT JOIN admin a ON c.id_admin = a.admin_id
                ORDER BY c.conseil_id DESC";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des conseils: " . $e->getMessage();
        return [];
    }
}

function getAdviceById($id)
{
    try {
        $db = getDatabaseConnection();
        $sql = "SELECT c.*, col.nom AS collaborateur_nom, col.prenom AS collaborateur_prenom, 
                a.username AS admin_username 
                FROM conseil c 
                LEFT JOIN collaborateur col ON c.id_collaborateur = col.collaborateur_id 
                LEFT JOIN admin a ON c.id_admin = a.admin_id
                WHERE c.conseil_id = :id";
        
        $stmt = $db->prepare($sql);
        $res = $stmt->execute(['id' => $id]);
        
        if ($res) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération du conseil: " . $e->getMessage();
        return null;
    }
}

function answerAdvice($id, $reponse, $id_admin)
{
    try {
        $db = getDatabaseConnection();
        $date_reponse = date('Y-m-d H:i:s'); // Format de date SQL
        $sql = "UPDATE conseil SET reponse = :reponse, id_admin = :id_admin, date_reponse = :date_reponse WHERE conseil_id = :id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([
            'reponse' => $reponse,
            'id_admin' => $id_admin,
            'date_reponse' => $date_reponse,
            'id' => $id
        ]);
        
        if ($res) {
            return $stmt->rowCount();
        }
        
        return false;
    } catch (PDOException $e) {
        echo "Erreur lors de la réponse au conseil: " . $e->getMessage();
        return false;
    }
}

function updateAdvice($id, $question, $reponse, $id_admin)
{
    try {
        $db = getDatabaseConnection();
        // Add date_reponse when a response is provided
        $dateClause = "";
        $params = [
            'question' => $question,
            'reponse' => $reponse,
            'id_admin' => $id_admin,
            'id' => $id
        ];
        
        if (!empty($reponse)) {
            $dateClause = ", date_reponse = :date_reponse";
            $params['date_reponse'] = date('Y-m-d H:i:s');
        }
        
        $sql = "UPDATE conseil SET question = :question, reponse = :reponse, id_admin = :id_admin$dateClause WHERE conseil_id = :id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);
        
        if ($res) {
            return $stmt->rowCount();
        }
        
        return false;
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour du conseil: " . $e->getMessage();
        return false;
    }
}

function deleteAdvice($id)
{
    try {
        $db = getDatabaseConnection();
        $sql = "DELETE FROM conseil WHERE conseil_id = :id";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute(['id' => $id]);
        
        if ($res) {
            return $stmt->rowCount();
        }
        
        return false;
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression du conseil: " . $e->getMessage();
        return false;
    }
}

/**
 * Recherche des conseils similaires à une question donnée
 * @param string $query La question à rechercher
 * @return array Liste des conseils similaires
 */
function findSimilarAdvices($query)
{
    try {
        $db = getDatabaseConnection();
        
        // Nettoyer la requête pour la recherche
        $cleanQuery = '%' . trim($query) . '%';
        
        // Recherche par mots clés dans les questions et les réponses
        $sql = "SELECT c.*, col.nom AS collaborateur_nom, col.prenom AS collaborateur_prenom, 
                a.username AS admin_username 
                FROM conseil c 
                LEFT JOIN collaborateur col ON c.id_collaborateur = col.collaborateur_id 
                LEFT JOIN admin a ON c.id_admin = a.admin_id
                WHERE c.question LIKE :query OR c.reponse LIKE :query
                ORDER BY 
                    CASE WHEN c.reponse IS NOT NULL THEN 1 ELSE 0 END DESC, 
                    c.date_creation DESC
                LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['query' => $cleanQuery]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la recherche de conseils similaires: " . $e->getMessage());
        return [];
    }
}
?>
