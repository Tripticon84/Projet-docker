<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/utils/hashPassword.php";


function createSociety($nom, $email, $adresse, $contact_person, $password, $telephone, $siret, $desactivate )
{
    $db = getDatabaseConnection();

    // Hasher le mot de passe
    $password = hashPassword($password);

    $sql = "INSERT INTO societe (nom, email, adresse, contact_person, password, telephone, date_creation, siret, desactivate) VALUES (:nom, :email, :adresse, :contact_person, :password, :telephone, :date_creation, :siret, :desactivate)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute([
        'nom' => $nom,
        'email' => $email,
        'adresse' => $adresse,
        'contact_person' => $contact_person,
        'password' => $password,
        'telephone' => $telephone,
        'date_creation' => date('Y-m-d H:i:s'),
        'siret' => $siret,
        'desactivate' => $desactivate
    ]);
    if ($res) {
        return $db->lastInsertId("societe_id");
    }
    return null;
}


function getSocietyByEmail($email)
{
    $db = getDatabaseConnection();
    $sql = "SELECT societe_id,email FROM societe WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSocietyByTelephone($telephone)
{
    $db = getDatabaseConnection();
    $sql = "SELECT societe_id,telephone FROM societe WHERE telephone = :telephone";
    $stmt = $db->prepare($sql);
    $stmt->execute(['telephone' => $telephone]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSocietyById($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT societe_id, nom, email, adresse, contact_person, telephone, date_creation,siret FROM societe WHERE societe_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function deleteSociety($id)
{
    $db = getDatabaseConnection();
    // Check if desactivate is already 4
    $checkSql = "SELECT desactivate FROM societe WHERE societe_id = :id";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute(['id' => $id]);
    $currentStatus = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($currentStatus && $currentStatus['desactivate'] == 1) {
        return 4;
    }

    // Update desactivate to 1
    $sql = "UPDATE societe SET desactivate = 1 WHERE societe_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    if (!$res) {
        return null;
    }

    $resultat = desactivateEmployeeFromSociety($id);
    if ($resultat && $res) {
        return $stmt->rowCount();
    }
    return null;
}

function desactivateEmployeeFromSociety($societe_id)
{
    $db = getDatabaseConnection();
    $sql = "UPDATE collaborateur SET desactivate = 1 WHERE id_societe = :societe_id AND desactivate = 0";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['societe_id' => $societe_id]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function updateSociety($id, ?string $nom = null, ?string $email = null, ?string $adresse = null, ?string $contact_person = null, ?string $password = null, ?int $telephone = null)
{
    $db = getDatabaseConnection();
    $params = ['id' => $id];
    $setFields = [];

    if ($nom !== null) {
        $setFields[] = "nom = :nom";
        $params['nom'] = $nom;
    }

    if ($email !== null) {
        $setFields[] = "email = :email";
        $params['email'] = $email;
    }

    if ($adresse !== null) {
        $setFields[] = "adresse = :adresse";
        $params['adresse'] = $adresse;
    }

    if ($contact_person !== null) {
        $setFields[] = "contact_person = :contact_person";
        $params['contact_person'] = $contact_person;
    }

    if ($telephone !== null) {
        $setFields[] = "telephone = :telephone";
        $params['telephone'] = $telephone;
    }

    if ($password !== null) {
        $setFields[] = "password = :password";
        $params['password'] = hashPassword($password);
    }

    if (empty($setFields)) {
        return 0; // Rien à mettre à jour
    }

    $sql = "UPDATE societe SET " . implode(", ", $setFields) . " WHERE societe_id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}


function getAllSociety($name = "", $limit = null, $offset = null)
{
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT societe_id, nom, email, adresse, contact_person, telephone, date_creation, siret, desactivate FROM societe WHERE desactivate = 0";
    $conditions = [];

    if (!empty($name)) {
        $conditions[] = "nom LIKE :name";
        $params['name'] = "%" . $name . "%";
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
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

function getSociety($id)
{
    $db = getDatabaseConnection();
    $sql = "SELECT societe_id, nom, email, adresse, contact_person, telephone, date_creation, siret, desactivate FROM societe WHERE societe_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSocietyEmployees($societe_id, $desactivate = null, $name = null, $role = null, $date = null)
{
    $db = getDatabaseConnection();
    $params = ['societe_id' => $societe_id];

    $sql = "SELECT collaborateur_id, nom, prenom, username, role, email, telephone, date_creation, date_activite, desactivate
            FROM collaborateur
            WHERE id_societe = :societe_id";

    // Filtrage par statut de désactivation si spécifié
    if ($desactivate !== null) {
        $sql .= " AND desactivate = :desactivate";
        $params['desactivate'] = $desactivate;
    }

    // Filtrage par nom ou prénom
    if ($name !== null) {
        $sql .= " AND (nom LIKE :name OR prenom LIKE :name)";
        $params['name'] = "%" . $name . "%";
    }

    // Filtrage par rôle
    if ($role !== null) {
        $sql .= " AND role = :role";
        $params['role'] = $role;
    }

    // Filtrage par date de création
    if ($date !== null) {
        $sql .= " AND DATE(date_creation) = :date";
        $params['date'] = $date;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getCompanyEstimate($societe_id, $is_contract, $statut = null, $date_debut = null, $date_fin = null)
{
    $db = getDatabaseConnection();
    $sql = "SELECT devis_id, date_debut, date_fin, statut, montant, montant_ht, montant_tva, fichier FROM devis WHERE id_societe = :societe_id AND is_contract = :is_contract";
    $params = ['societe_id' => $societe_id, 'is_contract' => $is_contract];

    if ($statut !== null) {
        $sql .= " AND statut = :statut";
        $params['statut'] = $statut;
    }

    if ($date_debut !== null) {
        $sql .= " AND date_debut >= :date_debut";
        $params['date_debut'] = $date_debut;
    }

    if ($date_fin !== null) {
        $sql .= " AND date_fin <= :date_fin";
        $params['date_fin'] = $date_fin;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCompanyOtherCost($societe_id)
{
    $db = getDatabaseConnection();

    $sql = "SELECT f.frais_id, f.nom, f.montant, f.date_creation, f.description, f.est_abonnement, d.devis_id, d.date_debut, d.date_fin, d.statut
            FROM frais f
            JOIN INCLUT_FRAIS_DEVIS ifd ON f.frais_id = ifd.id_frais
            JOIN devis d ON ifd.id_devis = d.devis_id
            WHERE d.id_societe = :societe_id";

    $stmt = $db->prepare($sql);
    $stmt->execute(['societe_id' => $societe_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des statistiques
function getCompaniesStats()
{
    $db = getDatabaseConnection();
    $stats = [
        'total' => 0,
        'totalLastMonth' => 0,
        'new' => 0,
        'newLastMonth' => 0,
    ];

    // Date du premier jour du mois courant
    $currentMonthStart = date('Y-m-01');
    // Date du premier jour du mois précédent
    $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
    // Date du premier jour du mois suivant (pour limiter le mois courant)
    $nextMonthStart = date('Y-m-01', strtotime('+1 month'));

    try {
        // Nombre total de sociétés inscrites
        $query = "SELECT COUNT(*) as total FROM societe";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = $result['total'];

        // Nombre de sociétés inscrites avant le mois dernier
        $query = "SELECT COUNT(*) as total FROM societe WHERE date_creation < :lastMonthStart";
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

        // Nombre de nouvelles sociétés ce mois
        $query = "SELECT COUNT(*) as new FROM societe
             WHERE date_creation >= :currentMonthStart AND date_creation < :nextMonthStart";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':currentMonthStart' => $currentMonthStart,
            ':nextMonthStart' => $nextMonthStart
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['new'] = $result['new'];

        // Nombre de nouvelles sociétés le mois dernier
        $query = "SELECT COUNT(*) as new FROM societe
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

        return $stats;
    } catch (PDOException $e) {
        echo "Erreur lors de la récupération des statistiques des sociétés : " . $e->getMessage();
        return null;
    }
}



// Token

function findCompanyByCredentials($email, $password)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT societe_id FROM societe WHERE email = :email AND password = :password";
    $query = $connection->prepare($sql);
    $res = $query->execute([
        'email' => $email,
        'password' => $password
    ]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function setCompanySession($id, $token)
{
    $connection = getDatabaseConnection();
    $sql = "UPDATE societe SET token = :token, expiration = DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE societe_id = :id";
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

function getCompanyExpirationByToken($token)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT expiration FROM societe WHERE token = :token";
    $query = $connection->prepare($sql);
    $res = $query->execute([
        'token' => $token
    ]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getCompanyByToken($token)
{
    $connection = getDatabaseConnection();
    $sql = "SELECT societe_id, nom, email, contact_person FROM societe WHERE token = :token";
    $query = $connection->prepare($sql);
    $res = $query->execute(['token' => $token]);
    if ($res) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getCompanyInvoices($societe_id, $limit = null, $offset = null, $statut = null, $date_emission = null, $date_echeance = null)
{
    $db = getDatabaseConnection();
    $params = ['societe_id' => $societe_id];

    $sql = "SELECT f.facture_id, f.date_emission, f.date_echeance, f.montant, f.montant_tva,
            f.montant_ht, f.statut, f.methode_paiement, f.id_devis, f.id_prestataire
            FROM facture f
            JOIN devis d ON f.id_devis = d.devis_id
            WHERE d.id_societe = :societe_id";

    if ($statut !== null) {
        $sql .= " AND f.statut = :statut";
        $params['statut'] = $statut;
    }

    if ($date_emission !== null) {
        $sql .= " AND f.date_emission >= :date_emission";
        $params['date_emission'] = $date_emission;
    }

    if ($date_echeance !== null) {
        $sql .= " AND f.date_echeance <= :date_echeance";
        $params['date_echeance'] = $date_echeance;
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

function getCompanyInvoiceByID($societe_id, $facture_id)
{
    $db = getDatabaseConnection();
    $params = [
        'societe_id' => $societe_id,
        'facture_id' => $facture_id
    ];

    $sql = "SELECT f.facture_id, f.date_emission, f.date_echeance, f.montant, f.montant_tva,
            f.montant_ht, f.statut, f.methode_paiement, f.id_devis, f.id_prestataire
            FROM facture f
            JOIN devis d ON f.id_devis = d.devis_id
            WHERE d.id_societe = :societe_id
            AND f.facture_id = :facture_id";

    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getCompanyBySiret($siret)
{
    $db = getDatabaseConnection();
    $sql = "SELECT societe_id, siret FROM societe WHERE siret = :siret";
    $stmt = $db->prepare($sql);
    $stmt->execute(['siret' => $siret]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
