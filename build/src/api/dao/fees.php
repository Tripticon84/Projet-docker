<?php
// fees c est les autres frais et les abonnements

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";

function createFrais($nom, $montant, $description, $est_abonnement = 0)
{
    $db = getDatabaseConnection();
    $sql = "INSERT INTO frais (nom, montant, date_creation, description, est_abonnement)
            VALUES (:nom, :montant, :date_creation, :description, :est_abonnement)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'nom' => $nom,
        'montant' => $montant,
        'date_creation' => date('d-m-Y H:i:s'),
        'description' => $description,
        'est_abonnement' => $est_abonnement
    ]);
    if ($res) {
        return $db->lastInsertId("frais_id");
    }
    return null;
}

function getFraisById($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT frais_id, nom, montant, date_creation, description, est_abonnement
            FROM frais WHERE frais_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateFrais($id, ?string $nom = null, ?float $montant = null, ?string $description = null, ?int $est_abonnement = null)
{
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($nom !== null) {
        $setFields[] = "nom = :nom";
        $params['nom'] = $nom;
    }

    if ($montant !== null) {
        $setFields[] = "montant = :montant";
        $params['montant'] = $montant;
    }

    if ($description !== null) {
        $setFields[] = "description = :description";
        $params['description'] = $description;
    }

    if ($est_abonnement !== null) {
        $setFields[] = "est_abonnement = :est_abonnement";
        $params['est_abonnement'] = $est_abonnement;
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE frais SET " . implode(", ", $setFields) . " WHERE frais_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function deleteFrais($id)
{
    $db = getDatabaseConnection();
    $sql = "DELETE FROM frais WHERE frais_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function getAllFrais($nom = "", $est_abonnement = null, $limit = null, $offset = null)
{
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT frais_id, nom, montant, date_creation, description, est_abonnement FROM frais";

    $conditions = [];

    if (!empty($nom)) {
        $conditions[] = "nom LIKE :nom";
        $params['nom'] = "%" . $nom . "%";
    }

    if ($est_abonnement !== null) {
        $conditions[] = "est_abonnement = :est_abonnement";
        $params['est_abonnement'] = $est_abonnement;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    if ($limit !== null) {
        $sql .= " LIMIT " . intval($limit);
        if ($offset !== null) {
            $sql .= " OFFSET " . intval($offset);
        }
    }

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getAbonnements()
{
    $db = getDatabaseConnection();
    $sql = "SELECT frais_id, nom, montant, date_creation, description, est_abonnement
            FROM frais WHERE est_abonnement = 1";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute();
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getFraisStatistics()
{
    $db = getDatabaseConnection();
    $stats = [
        'total' => 0,
        'totalAbonnements' => 0,
        'totalAutreFrais' => 0,
        'montantTotalFrais' => 0,
        'montantTotalAbonnements' => 0
    ];

    try {
        // Nombre total de frais
        $query = "SELECT COUNT(*) as total FROM frais";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = $result['total'];

        // Nombre total d'abonnements
        $query = "SELECT COUNT(*) as total FROM frais WHERE est_abonnement = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalAbonnements'] = $result['total'];

        // Nombre total d'autres frais
        $query = "SELECT COUNT(*) as total FROM frais WHERE est_abonnement = 0";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalAutreFrais'] = $result['total'];

        // Montant total des frais
        $query = "SELECT SUM(montant) as total FROM frais";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['montantTotalFrais'] = $result['total'] ?? 0;

        // Montant total des abonnements
        $query = "SELECT SUM(montant) as total FROM frais WHERE est_abonnement = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['montantTotalAbonnements'] = $result['total'] ?? 0;

        return $stats;
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des statistiques des frais : " . $e->getMessage();
        return null;
    }
}

// Fonctions pour gérer la relation INCLUT_FRAIS_DEVIS

/**
 * Associe un frais à un devis
 */
function linkFraisToDevis($fraisId, $devisId)
{
    $db = getDatabaseConnection();

    // Vérifier si la relation existe déjà
    $checkSql = "SELECT COUNT(*) as count FROM INCLUT_FRAIS_DEVIS WHERE id_devis = :devisId AND id_frais = :fraisId";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([
        'devisId' => $devisId,
        'fraisId' => $fraisId
    ]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        return 0; // La relation existe déjà
    }

    $sql = "INSERT INTO INCLUT_FRAIS_DEVIS (id_devis, id_frais) VALUES (:devisId, :fraisId)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'devisId' => $devisId,
        'fraisId' => $fraisId
    ]);

    if ($res) {
        return 1; // Relation créée avec succès
    }
    return null; // Erreur
}

/**
 * Dissocie un frais d'un devis
 */
function unlinkFraisFromDevis($fraisId, $devisId)
{
    $db = getDatabaseConnection();
    $sql = "DELETE FROM INCLUT_FRAIS_DEVIS WHERE id_devis = :devisId AND id_frais = :fraisId";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'devisId' => $devisId,
        'fraisId' => $fraisId
    ]);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

/**
 * Récupère tous les frais associés à un devis
 */
function getFraisByDevisId($devisId)
{
    $db = getDatabaseConnection();
    $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description, f.est_abonnement
            FROM frais f
            JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
            WHERE ifd.id_devis = :devisId";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['devisId' => $devisId]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

/**
 * Récupère tous les devis associés à un frais
 */
function getDevisByFraisId($fraisId)
{
    $db = getDatabaseConnection();
    $sql = "SELECT d.devis_id, d.date_debut, d.date_fin, d.statut, d.montant, d.id_societe
            FROM devis d
            JOIN INCLUT_FRAIS_DEVIS ifd ON d.devis_id = ifd.id_devis
            WHERE ifd.id_frais = :fraisId";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['fraisId' => $fraisId]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

/**
 * Supprime toutes les relations d'un frais
 */
function removeAllFraisRelations($fraisId)
{
    $db = getDatabaseConnection();
    $sql = "DELETE FROM INCLUT_FRAIS_DEVIS WHERE id_frais = :fraisId";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['fraisId' => $fraisId]);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

/**
 * Calcule le montant total des frais pour un devis
 */
function calculateTotalFraisForDevis($devisId)
{
    $db = getDatabaseConnection();
    $sql = "SELECT SUM(f.montant) as total
            FROM frais f
            JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
            WHERE ifd.id_devis = :devisId";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['devisId' => $devisId]);

    if ($res) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    return 0;
}
