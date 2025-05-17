<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/server.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";

function createEvaluation(int $note, String $commentaire, int $id_collaborateur,$date_creation){
    $db = getDatabaseConnection();
    $sql = "INSERT INTO evaluation (note, commentaire, id_collaborateur,date_creation) VALUES (:note, :commentaire, :id_collaborateur, :date_creation)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'note' => $note,
        'commentaire' => $commentaire,
        'id_collaborateur' => $id_collaborateur,
        'date_creation' => $date_creation
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function getEvaluationByCollaboratorId(int $id){
    $db = getDatabaseConnection();
    $sql = "SELECT note, commentaire, id_collaborateur, date_creation, desactivate FROM evaluation WHERE id_collaborateur = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'id' => $id
    ]);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEvaluation($id){
    $db = getDatabaseConnection();
    $sql = "SELECT evaluation_id, note, commentaire, id_collaborateur, date_creation, desactivate FROM evaluation WHERE evaluation_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'id' => $id
    ]);
    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}


function getAllEvaluation(?int $note = null,?int $desactivate = null,?int $limit, ?int $offset){
    $db = getDatabaseConnection();
    $params = [];

    $sql = "SELECT evaluation_id, note, commentaire, id_collaborateur, date_creation, desactivate FROM evaluation";

    if ($note !== null) {
        $sql .= " WHERE note = :note";
        $params['note'] = $note;
    }

    if ($desactivate !== null && $note === null) {
        $sql .= " WHERE desactivate = :desactivate";
        $params['desactivate'] = $desactivate;
    }

    // Gestion des paramètres LIMIT et OFFSET
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

function deleteEvaluation(int  $id){
    $db = getDatabaseConnection();
    $sql = "UPDATE evaluation SET desactivate = :desactivate WHERE evaluation_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'id' => $id,
        'desactivate' => 1
    ]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}


function updateEvaluation(?int $id, ?int $note = null, ?String $commentaire = null , ?int $id_collaborateur = null, $date_creation = null, ?int $desactivate = null){
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($note !== null) {
        $setFields[] = "note = :note";
        $params['note'] = $note;
    }

    if ($commentaire !== null) {
        $setFields[] = "commentaire = :commentaire";
        $params['commentaire'] = $commentaire;
    }

    if ($id_collaborateur !== null) {
        $setFields[] = "id_collaborateur = :id_collaborateur";
        $params['id_collaborateur'] = $id_collaborateur;
    }

    if ($date_creation !== null) {
        $setFields[] = "date_creation = :date_creation";
        $params['date_creation'] = $date_creation;
    }

    if ($desactivate !== null) {
        $setFields[] = "desactivate = :desactivate";
        $params['desactivate'] = $desactivate;
    }

    if (empty($setFields)) {
        return false; // Aucun champ à mettre à jour
    }

    $sql = "UPDATE evaluation SET " . implode(", ", $setFields) . " WHERE evaluation_id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}


function getProviderByEvaluation (int $id){
    $db = getDatabaseConnection();
    $sql = "SELECT p.prestataire_id, p.nom, p.type, p.tarif, p.date_debut_disponibilite, p.date_fin_disponibilite 
    FROM prestataire p
    JOIN note_prestataire n ON p.prestataire_id = n.prestataire_id
    JOIN evalutation e ON e.evaluation_id = n.evaluation_id
    WHERE e.evaluation_id = :id";    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'id' => $id
    ]);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}


function newEvaluationInNote_prestataire($id_evaluation,$id_prestataire){
    $db = getDatabaseConnection();
    $sql = "INSERT INTO note_prestataire (id_evaluation, id_prestataire) VALUES (:id_evaluation, :id_prestataire)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'id_evaluation' => $id_evaluation,
        'id_prestataire' => $id_prestataire
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function getEvaluationsByProviderId(int $prestataire_id){
    $db = getDatabaseConnection();
    $sql = "SELECT e.evaluation_id, e.note, e.commentaire, e.date_creation, 
                   c.nom as collaborateur_nom, c.prenom as collaborateur_prenom 
            FROM evaluation e
            JOIN note_prestataire np ON e.evaluation_id = np.id_evaluation
            JOIN collaborateur c ON e.id_collaborateur = c.collaborateur_id
            WHERE np.id_prestataire = :prestataire_id AND e.desactivate = 0
            ORDER BY e.date_creation DESC";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'prestataire_id' => $prestataire_id
    ]);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

function getAverageRatingByProviderId(int $prestataire_id){
    $db = getDatabaseConnection();
    $sql = "SELECT AVG(e.note) as average_rating, COUNT(e.evaluation_id) as total_evaluations
            FROM evaluation e
            JOIN note_prestataire np ON e.evaluation_id = np.id_evaluation
            WHERE np.id_prestataire = :prestataire_id AND e.desactivate = 0";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'prestataire_id' => $prestataire_id
    ]);
    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return ['average_rating' => 0, 'total_evaluations' => 0];
}

function getRatingDistributionByProviderId(int $prestataire_id){
    $db = getDatabaseConnection();
    $sql = "SELECT e.note, COUNT(e.evaluation_id) as count
            FROM evaluation e
            JOIN note_prestataire np ON e.evaluation_id = np.id_evaluation
            WHERE np.id_prestataire = :prestataire_id AND e.desactivate = 0
            GROUP BY e.note
            ORDER BY e.note DESC";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'prestataire_id' => $prestataire_id
    ]);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}