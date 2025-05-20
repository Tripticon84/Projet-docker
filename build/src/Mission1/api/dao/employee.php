<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/hashPassword.php";

function createEmployee(
    string $nom,
    string $prenom,
    string $username,
    string $role = null,
    string $email = null,
    string $password = null,
    string $telephone = null,
    int $id_societe = null
) {
    $db = getDatabaseConnection();

    // Hasher le mot de passe si fourni
    if ($password !== null) {
        $password = hashPassword($password);
    }

    $sql = "INSERT INTO collaborateur (nom, prenom, username, role, email, password, telephone, id_societe, date_creation) VALUES (:nom, :prenom, :username, :role, :email, :password, :telephone, :id_societe, :date_creation)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'username' => $username,
        'role' => $role,
        'email' => $email,
        'password' => $password,
        'telephone' => $telephone,
        'id_societe' => $id_societe,
        'date_creation' => date('Y-m-d H:i:s')
    ]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function getEmployee(int $id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, id_societe, date_creation, date_activite, desactivate FROM collaborateur WHERE collaborateur_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getAllEmployees(string $username = "", int $limit = null, int $offset = null, int $id_societe = null)
{
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, id_societe, date_creation, date_activite FROM collaborateur WHERE desactivate = 0";

    if (!empty($username)) {
        $sql .= " AND username LIKE :username";
        $params['username'] = "%" . $username . "%";
    }

    if (!is_null($id_societe)) {
        $sql .= " AND id_societe = :id_societe";
        $params['id_societe'] = $id_societe;
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

/**
 * Récupère les employés désactivés
 */
function getDisabledEmployees(string $username = "", int $limit = null, int $offset = null, int $id_societe = null)
{
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, id_societe, date_creation, date_activite FROM collaborateur WHERE desactivate = 1";

    if (!empty($username)) {
        $sql .= " AND username LIKE :username";
        $params['username'] = "%" . $username . "%";
    }

    if (!is_null($id_societe)) {
        $sql .= " AND id_societe = :id_societe";
        $params['id_societe'] = $id_societe;
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

/**
 * Réactive un employé désactivé
 */
function reactivateEmployee(int $id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE collaborateur SET desactivate = 0 WHERE collaborateur_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    return $res;
}

// function deleteEmployee(int $id)
// {
//     $db = getDatabaseConnection();
//     $sql = "DELETE FROM collaborateur WHERE collaborateur_id = :id";
//     $stmt = $db->prepare($sql);
//     $res = $stmt->execute(['id' => $id]);
//     if ($res) {
//         return $stmt->rowCount();
//     }
//     return null;
// }

function deleteEmployee(int $id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE collaborateur SET desactivate = 1 WHERE collaborateur_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    return $res;
}


function updateEmployee(int $id, ?string $nom = null, ?string $prenom = null, ?string $role = null, ?string $email = null, ?string $telephone = null, ?int $id_societe = null, ?string $username = null, ?string $password = null)
{
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($nom !== null) {
        $setFields[] = "nom = :nom";
        $params['nom'] = $nom;
    }

    if ($prenom !== null) {
        $setFields[] = "prenom = :prenom";
        $params['prenom'] = $prenom;
    }

    if ($role !== null) {
        $setFields[] = "role = :role";
        $params['role'] = $role;
    }

    if ($email !== null) {
        $setFields[] = "email = :email";
        $params['email'] = $email;
    }

    if ($telephone !== null) {
        $setFields[] = "telephone = :telephone";
        $params['telephone'] = $telephone;
    }

    if ($id_societe !== null) {
        $setFields[] = "id_societe = :id_societe";
        $params['id_societe'] = $id_societe;
    }

    if ($username !== null) {
        $setFields[] = "username = :username";
        $params['username'] = $username;
    }

    if ($password !== null) {
        $setFields[] = "password = :password";
        $params['password'] = hashPassword($password);
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE collaborateur SET " . implode(", ", $setFields) . " WHERE collaborateur_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function getEmployeeByTelephone(string $telephone)
{
    $db = getDatabaseConnection();
    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, id_societe, date_creation, date_activite FROM collaborateur WHERE telephone = :telephone";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['telephone' => $telephone]);
    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeByEmail(string $email)
{
    $db = getDatabaseConnection();
    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, id_societe, date_creation, date_activite FROM collaborateur WHERE email = :email";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['email' => $email]);
    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeesBySociete(int $id_societe)
{
    $db = getDatabaseConnection();
    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, id_societe, date_creation, date_activite FROM collaborateur WHERE id_societe = :id_societe";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id_societe' => $id_societe]);
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

/* Authentification */

function findEmployeeByCredentials($username, $password)
{
    $connection = getDatabaseConnection();
    $hashedPassword = hashPassword($password);
    $sql = "SELECT * FROM collaborateur WHERE username = :username AND password = :password";
    $query = $connection->prepare($sql);
    $res = $query->execute([
        'username' => $username,
        'password' => $hashedPassword
    ]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function setEmployeeSession($id, $token)
{
    $connection = getDatabaseConnection();
    $sql = "UPDATE collaborateur SET token = :token, expiration = DATE_ADD(NOW(), INTERVAL 5 HOUR) WHERE collaborateur_id = :id";
    $query = $connection->prepare($sql);
    $res = $query->execute([
        'id' => $id,
        'token' => $token
    ]);
    if ($res) {
        return $query->rowCount();
    }
    return null;
}

function getEmployeeExpirationByToken($token)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT expiration FROM collaborateur WHERE token = :token";
    $query = $connection->prepare($sql);
    $res = $query->execute([
        'token' => $token
    ]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeByToken($token)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT collaborateur_id, nom, prenom, username, role FROM collaborateur WHERE token = :token";
    $query = $connection->prepare($sql);
    $res = $query->execute(['token' => $token]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeByUsername($username)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT collaborateur_id, nom, prenom FROM collaborateur WHERE username = :username";
    $query = $connection->prepare($sql);
    $res = $query->execute(['username' => $username]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}


// Récupération des statistiques des employés
function getEmployeeStats()
{
    $db = getDatabaseConnection();
    $stats = [
        'total' => 0,
        'totalLastMonth' => 0,
        'active' => 0,
        'activeLastMonth' => 0,
        'new' => 0,
        'newLastMonth' => 0,
        'participationRate' => 0,
        'participationRateLastMonth' => 0
    ];

    // Date du premier jour du mois courant
    $currentMonthStart = date('Y-m-01');
    // Date du premier jour du mois précédent
    $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
    // Date du premier jour du mois suivant (pour limiter le mois courant)
    $nextMonthStart = date('Y-m-01', strtotime('+1 month'));

    try {
        // Nombre total d'employés inscrits
        $query = "SELECT COUNT(*) as total FROM collaborateur";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = $result['total'];

        // Nombre d'employés inscrits le mois dernier
        $query = "SELECT COUNT(*) as total FROM collaborateur WHERE date_creation < :lastMonthStart";
        $stmt = $db->prepare($query);
        // Nombre d'employés inscrits le mois dernier
        $query = "SELECT COUNT(*) as total FROM collaborateur WHERE date_creation < :lastMonthStart";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':lastMonthStart' => $lastMonthStart
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalLastMonth'] = $result['total'];

        // Calcul de la variation totale en pourcentage
        $totalVariation = $stats['totalLastMonth'] > 0 ?
            round((($stats['total'] - $stats['totalLastMonth']) / $stats['totalLastMonth']) * 100) : 0;
        $stats['totalVariation'] = $totalVariation;

        // Nombre d'employés actifs ce mois
        $query = "SELECT COUNT(DISTINCT collaborateur_id) as active
             FROM collaborateur
             WHERE date_activite >= :currentMonthStart AND date_activite < :nextMonthStart";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':currentMonthStart' => $currentMonthStart,
            ':nextMonthStart' => $nextMonthStart
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active'] = $result['active'];

        // Nombre d'employés actifs le mois dernier
        $query = "SELECT COUNT(DISTINCT collaborateur_id) as active
             FROM collaborateur
             WHERE date_activite >= :lastMonthStart AND date_activite < :currentMonthStart";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':lastMonthStart' => $lastMonthStart,
            ':currentMonthStart' => $currentMonthStart
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['activeLastMonth'] = $result['active'];

        // Calcul de la variation des actifs en pourcentage
        $activeVariation = $stats['activeLastMonth'] > 0 ?
            round((($stats['active'] - $stats['activeLastMonth']) / $stats['activeLastMonth']) * 100) : 0;
        $stats['activeVariation'] = $activeVariation;

        // Nombre de nouveaux employés ce mois
        $query = "SELECT COUNT(*) as new FROM collaborateur
             WHERE date_creation >= :currentMonthStart AND date_creation < :nextMonthStart";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':currentMonthStart' => $currentMonthStart,
            ':nextMonthStart' => $nextMonthStart
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['new'] = $result['new'];

        // Nombre de nouveaux employés le mois dernier
        $query = "SELECT COUNT(*) as new FROM collaborateur
             WHERE date_creation >= :lastMonthStart AND date_creation < :currentMonthStart";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':lastMonthStart' => $lastMonthStart,
            ':currentMonthStart' => $currentMonthStart
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['newLastMonth'] = $result['new'];

        // Calcul de la variation des nouveaux en pourcentage
        $newVariation = $stats['newLastMonth'] > 0 ?
            round((($stats['new'] - $stats['newLastMonth']) / $stats['newLastMonth']) * 100) : 0;
        $stats['newVariation'] = $newVariation;

        // Taux de participation (basé sur le nombre d'employés actifs divisé par le total)
        $stats['participationRate'] = $stats['total'] > 0 ?
            round(($stats['active'] / $stats['total']) * 100) : 0;

        // Taux de participation le mois dernier
        $participationRateLastMonth = $stats['totalLastMonth'] > 0 ?
            round(($stats['activeLastMonth'] / $stats['totalLastMonth']) * 100) : 0;
        $stats['participationRateLastMonth'] = $participationRateLastMonth;

        // Calcul de la variation du taux de participation
        $participationVariation = $stats['participationRateLastMonth'] > 0 ?
            round(($stats['participationRate'] - $stats['participationRateLastMonth'])) : 0;
        $stats['participationVariation'] = $participationVariation;

        return $stats;
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des statistiques : " . $e->getMessage();
    }
}

function getEmployeeProfile(int $id) {
    $db = getDatabaseConnection();
    $sql = "SELECT
        collaborateur_id,
        nom,
        prenom,
        role,
        email,
        telephone,
        id_societe,
        date_creation,
        date_activite
    FROM collaborateur
    WHERE collaborateur_id = :id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);

    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeActivities(int $collaborateur_id) {
    $db = getDatabaseConnection();
    $sql = "SELECT a.*
            FROM activite a
            INNER JOIN participe_activite pa ON a.activite_id = pa.id_activite
            WHERE pa.id_collaborateur = :collaborateur_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['collaborateur_id' => $collaborateur_id]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeEvents(int $collaborateur_id) {
    $db = getDatabaseConnection();
    $sql = "SELECT e.*
            FROM evenements e
            INNER JOIN participe_evenement pe ON e.evenement_id = pe.id_evenement
            WHERE pe.id_collaborateur = :collaborateur_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['collaborateur_id' => $collaborateur_id]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeChats(int $collaborateur_id) {
    $db = getDatabaseConnection();
    $sql = "SELECT s.*
            FROM salon s
            INNER JOIN discute_dans d ON s.salon_id = d.id_salon
            WHERE d.id_collaborateur = :collaborateur_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['collaborateur_id' => $collaborateur_id]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeAssociations(int $collaborateur_id) {
    $db = getDatabaseConnection();
    $sql = "SELECT a.*
            FROM association a
            INNER JOIN participe_association pa ON a.association_id = pa.id_association
            WHERE pa.id_collaborateur = :collaborateur_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['collaborateur_id' => $collaborateur_id]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function getEmployeeEvaluations(int $collaborateur_id) {
    $db = getDatabaseConnection();
    $sql = "SELECT evaluation_id, note, commentaire, date_creation
            FROM evaluation
            WHERE id_collaborateur = :collaborateur_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['collaborateur_id' => $collaborateur_id]);

    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

function createInscription(array $data) {
    $db = getDatabaseConnection();
    
    try {
        $db->beginTransaction();
        
        // Normaliser le type
        $type = strtolower($data['type']);
        if ($type === 'activite') {
            $type = 'activity';
        }
        
        if (!in_array($type, ['activity', 'event'])) {
            throw new Exception('Invalid service type: ' . $data['type']);
        }

        // Check if already registered
        $checkSql = ($type === 'activity') 
            ? "SELECT COUNT(*) FROM participe_activite WHERE id_collaborateur = :collaborateur_id AND id_activite = :service_id"
            : "SELECT COUNT(*) FROM participe_evenement WHERE id_collaborateur = :collaborateur_id AND id_evenement = :service_id";
            
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            'collaborateur_id' => $data['id_collaborateur'],
            'service_id' => $data['id_service']
        ]);
        
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception('Already registered for this service');
        }

        // Insert registration
        $sql = ($type === 'activity')
            ? "INSERT INTO participe_activite (id_collaborateur, id_activite) VALUES (:collaborateur_id, :service_id)"
            : "INSERT INTO participe_evenement (id_collaborateur, id_evenement) VALUES (:collaborateur_id, :service_id)";

        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            'collaborateur_id' => $data['id_collaborateur'],
            'service_id' => $data['id_service']
        ]);

        if (!$success) {
            throw new Exception('Failed to insert registration');
        }

        // Update last activity date
        $updateSql = "UPDATE collaborateur SET date_activite = NOW() WHERE collaborateur_id = :collaborateur_id";
        $updateStmt = $db->prepare($updateSql);
        $updateSuccess = $updateStmt->execute(['collaborateur_id' => $data['id_collaborateur']]);

        if (!$updateSuccess) {
            throw new Exception('Failed to update activity date');
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('Registration error: ' . $e->getMessage());
        throw $e;
    }
}

function deleteInscription(array $data) {
    $db = getDatabaseConnection();
    
    try {
        $db->beginTransaction();
        
        if (!in_array($data['type'], ['activite', 'event'])) {
            throw new Exception('Invalid service type');
        }

        // Delete registration
        $sql = ($data['type'] === 'activite')
            ? "DELETE FROM participe_activite WHERE id_collaborateur = :collaborateur_id AND id_activite = :service_id"
            : "DELETE FROM participe_evenement WHERE id_collaborateur = :collaborateur_id AND id_evenement = :service_id";

        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            'collaborateur_id' => $data['id_collaborateur'],
            'service_id' => $data['id_service']
        ]);

        if (!$success) {
            throw new Exception('Failed to delete registration');
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log('Unregistration error: ' . $e->getMessage());
        throw $e;
    }
}

function getEmployeeRegistrations(int $collaborateurId) {
    $db = getDatabaseConnection();
    
    // Récupérer les inscriptions aux événements
    $eventQuery = "SELECT 'event' as type, id_evenement as service_id 
                  FROM participe_evenement 
                  WHERE id_collaborateur = :collaborateur_id";
    
    // Récupérer les inscriptions aux activités (changé 'activity' en 'activite')
    $activityQuery = "SELECT 'activite' as type, id_activite as service_id 
                     FROM participe_activite 
                     WHERE id_collaborateur = :collaborateur_id";
    
    // Combiner les deux requêtes avec UNION
    $query = $db->prepare($eventQuery . " UNION " . $activityQuery);
    $query->execute(['collaborateur_id' => $collaborateurId]);
    
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function createSignalement(string $type, string $description, ?int $id_societe = null) {
    $db = getDatabaseConnection();
    
    $sql = "INSERT INTO signalement (probleme, description, date_signalement, id_societe, statut) 
            VALUES (:probleme, :description, NOW(), :id_societe, 'non_traite')";
            
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'probleme' => $type,
        'description' => $description,
        'id_societe' => $id_societe
    ]);

    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function getAllAssociations() {
    $db = getDatabaseConnection();
    $sql = "SELECT * FROM association WHERE desactivate = 0";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute();
    
    if ($res) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return null;
}

