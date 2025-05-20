-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 11 fév. 2025 à 15:20
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


--
-- Base de données : `businesscare`
--
CREATE DATABASE IF NOT EXISTS `businesscare` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `businesscare`;

-- --------------------------------------------------------

--
-- Structure de la table `lieu`
--
CREATE TABLE `lieu`(
  `lieu_id` int(11) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `code_postal` int(11) DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `activite`
--
CREATE TABLE `activite` (
  `activite_id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `id_devis` int(11) DEFAULT NULL,
  `desactivate` boolean DEFAULT 0,
  `id_prestataire` int(11)  DEFAULT NULL,
  `id_lieu` int(11)  DEFAULT NULL,
  `refusee` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) NULL,
  `expiration` datetime NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `association`
--

CREATE TABLE `association` (
  `association_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banniere` varchar(255) DEFAULT NULL,
  `desactivate` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `frais`
--

CREATE TABLE `frais` (
  `frais_id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `description` text DEFAULT NULL,
  `est_abonnement` boolean DEFAULT 0

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `chatbot`
--

CREATE TABLE `chatbot` (
  `question_id` int(11) NOT NULL,
  `question` text DEFAULT NULL,
  `reponse` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `collaborateur`
--

CREATE TABLE `collaborateur` (
  `collaborateur_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT "employe",
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `id_societe` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_activite` datetime DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expiration` datetime DEFAULT NULL,
  `desactivate` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `conseil`
--

CREATE TABLE `conseil` (
  `conseil_id` int(11) NOT NULL,
  `question` text DEFAULT NULL,
  `reponse` text DEFAULT NULL,
  `id_collaborateur` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `date_creation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

CREATE TABLE `devis` (
  `devis_id` int(11) NOT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `statut` ENUM('brouillon', 'envoyé', 'accepté', 'refusé') DEFAULT 'brouillon',
  `montant` decimal(10,2) DEFAULT NULL,
  `montant_ht` decimal(10,2) DEFAULT NULL,
  `montant_tva` decimal(10,2) DEFAULT NULL,
  `fichier` varchar(255) DEFAULT NULL,
  `is_contract` tinyint(1) DEFAULT NULL,
  `id_societe` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `don`
--

CREATE TABLE `don` (
  `don_id` int(11) NOT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `id_collaborateur` int(11) NOT NULL,
  `id_association` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `inclut_frais_devis`
--

CREATE TABLE `inclut_frais_devis` (
  `id_devis` int(11) NOT NULL,
  `id_frais` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `discute_dans`
--

CREATE TABLE `discute_dans` (
  `id_salon` int(11) NOT NULL,
  `id_collaborateur` int(11) NOT NULL,
  `is_admin` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

CREATE TABLE `evaluation` (
  `evaluation_id` int(11) NOT NULL,
  `note` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `id_collaborateur` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `desactivate` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `evenement_id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `statut` ENUM('en_cours', 'a_venir', 'termine') DEFAULT 'en_cours',
  `id_association` int(11)  DEFAULT NULL,
  `desactivate` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `facture`
--

CREATE TABLE `facture` (
  `facture_id` int(11) NOT NULL,
  `date_emission` date DEFAULT NULL,
  `date_echeance` date DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `montant_tva` decimal(10,2) DEFAULT NULL,
  `montant_ht` decimal(10,2) DEFAULT NULL,
  `statut` 	ENUM('Attente', 'Payee', 'Annulee') DEFAULT 'Attente',
  `methode_paiement` VARCHAR(50)	DEFAULT NULL,
  `fichier` varchar(255) DEFAULT NULL,
  `id_devis` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `payment_intent_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `note_prestataire`
--

CREATE TABLE `note_prestataire` (
  `note_prestataire_id` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_evaluation` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participe_activite`
--

CREATE TABLE `participe_activite` (
  `id_activite` int(11) NOT NULL,
  `id_collaborateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participe_association`
--

CREATE TABLE `participe_association` (
  `id_association` int(11) NOT NULL,
  `id_collaborateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participe_evenement`
--

CREATE TABLE `participe_evenement` (
  `id_evenement` int(11) NOT NULL,
  `id_collaborateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prestataire`
--

CREATE TABLE `prestataire` (
  `prestataire_id` int(11) NOT NULL,
  `email` varchar(255)  NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `tarif` decimal(10,2) DEFAULT NULL,
  `date_debut_disponibilite` date DEFAULT NULL,
  `date_fin_disponibilite` date DEFAULT NULL,
  `est_candidat` boolean  NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expiration` datetime DEFAULT NULL,
  `desactivate` boolean DEFAULT 0,
  `date_activite` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `salon`
--

CREATE TABLE `salon` (
  `salon_id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `signalement`
--

CREATE TABLE `signalement` (
  `signalement_id` int(11) NOT NULL,
  `probleme` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_signalement` datetime NOT NULL,
  `statut` ENUM('non_traite', 'en_cours', 'resolu','annuler') DEFAULT 'non_traite',
  `id_societe` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `societe`
--

CREATE TABLE `societe` (
  `societe_id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `adresse` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expiration` datetime NULL,
  `siret` varchar(255) DEFAULT NULL,
  `plan` ENUM('starter', 'basic', 'premium') DEFAULT 'starter',
  `employee_count` int(11) DEFAULT 0,
  `desactivate` boolean DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--
--
-- Index pour la table `lieu`
--
ALTER TABLE `lieu`
  ADD PRIMARY KEY (`lieu_id`);
--
-- Index pour la table `activite`
--
ALTER TABLE `activite`
  ADD PRIMARY KEY (`activite_id`),
  ADD KEY `id_devis` (`id_devis`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_lieu` (`id_lieu`);

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Index pour la table `association`
--
ALTER TABLE `association`
  ADD PRIMARY KEY (`association_id`);

--
-- Index pour la table `autre_frais`
--
ALTER TABLE `frais`
  ADD PRIMARY KEY (`frais_id`);

--
-- Index pour la table `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`question_id`);

--
-- Index pour la table `collaborateur`
--
ALTER TABLE `collaborateur`
  ADD PRIMARY KEY (`collaborateur_id`),
  ADD KEY `id_societe` (`id_societe`);


--
-- Index pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD PRIMARY KEY (`conseil_id`),
  ADD KEY `id_collaborateur` (`id_collaborateur`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Index pour la table `devis`
--
ALTER TABLE `devis`
  ADD PRIMARY KEY (`devis_id`),
  ADD KEY `id_societe` (`id_societe`);

--
-- Index pour la table `don`
--
ALTER TABLE `don`
  ADD PRIMARY KEY (`don_id`),
  ADD KEY `id_collaborateur` (`id_collaborateur`),
  ADD KEY `id_association` (`id_association`);

--
-- Index pour la table `inclut_frais_devis`
--
ALTER TABLE `inclut_frais_devis`
  ADD PRIMARY KEY (`id_devis`,`id_frais`),
  ADD KEY `id_frais` (`id_frais`);

--
-- Index pour la table `discute_dans`
--
ALTER TABLE `discute_dans`
  ADD PRIMARY KEY (`id_salon`,`id_collaborateur`),
  ADD KEY `id_collaborateur` (`id_collaborateur`);

--
-- Index pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD KEY `id_collaborateur` (`id_collaborateur`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`evenement_id`),
  ADD KEY `id_association`(`id_association`);

--
-- Index pour la table `facture`
--
ALTER TABLE `facture`
  ADD PRIMARY KEY (`facture_id`),
  ADD KEY `id_devis` (`id_devis`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `note_prestataire`
--
ALTER TABLE `note_prestataire`
  ADD PRIMARY KEY (`note_prestataire_id`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_evaluation` (`id_evaluation`);

--
-- Index pour la table `participe_activite`
--
ALTER TABLE `participe_activite`
  ADD PRIMARY KEY (`id_activite`,`id_collaborateur`),
  ADD KEY `id_collaborateur` (`id_collaborateur`);

--
-- Index pour la table `participe_association`
--
ALTER TABLE `participe_association`
  ADD PRIMARY KEY (`id_association`,`id_collaborateur`),
  ADD KEY `id_collaborateur` (`id_collaborateur`);

--
-- Index pour la table `participe_evenement`
--
ALTER TABLE `participe_evenement`
  ADD PRIMARY KEY (`id_evenement`,`id_collaborateur`),
  ADD KEY `id_collaborateur` (`id_collaborateur`);

--
-- Index pour la table `prestataire`
--
ALTER TABLE `prestataire`
  ADD PRIMARY KEY (`prestataire_id`);

--
-- Index pour la table `salon`
--
ALTER TABLE `salon`
  ADD PRIMARY KEY (`salon_id`);

--
-- Index pour la table `signalement`
--
ALTER TABLE `signalement`
  ADD PRIMARY KEY (`signalement_id`),
  ADD KEY `id_societe` (`id_societe`);

--
-- Index pour la table `societe`
--
ALTER TABLE `societe`
  ADD PRIMARY KEY (`societe_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--
ALTER TABLE `lieu`
  MODIFY `lieu_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `activite`
--
ALTER TABLE `activite`
  MODIFY `activite_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `association`
--
ALTER TABLE `association`
  MODIFY `association_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `frais`
--
ALTER TABLE `frais`
  MODIFY `frais_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `chatbot`
--
ALTER TABLE `chatbot`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `collaborateur`
--
ALTER TABLE `collaborateur`
  MODIFY `collaborateur_id` int(11) NOT NULL AUTO_INCREMENT;


--
-- AUTO_INCREMENT pour la table `conseil`
--
ALTER TABLE `conseil`
  MODIFY `conseil_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `devis`
--
ALTER TABLE `devis`
  MODIFY `devis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `don`
--
ALTER TABLE `don`
  MODIFY `don_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `evenement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `facture`
--
ALTER TABLE `facture`
  MODIFY `facture_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `note_prestataire`
--
ALTER TABLE `note_prestataire`
  MODIFY `note_prestataire_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `prestataire`
--
ALTER TABLE `prestataire`
  MODIFY `prestataire_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `salon`
--
ALTER TABLE `salon`
  MODIFY `salon_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `signalement`
--
ALTER TABLE `signalement`
  MODIFY `signalement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `societe`
--
ALTER TABLE `societe`
  MODIFY `societe_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activite`
--
ALTER TABLE `activite`
  ADD CONSTRAINT `activite_ibfk_1` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`devis_id`),
  ADD CONSTRAINT `activite_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`prestataire_id`),
  ADD CONSTRAINT `activite_ibfk_3` FOREIGN KEY (`id_lieu`) REFERENCES `lieu` (`lieu_id`);

--
-- Contraintes pour la table `chatbot`
--
ALTER TABLE `chatbot`
  ADD CONSTRAINT `chatbot_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `chatbot` (`question_id`) ON DELETE CASCADE;  -- on reference la question parente pour les sous-questions, on delete cascade pour supprimer les sous-questions si la question parente est supprimée


--
-- Contraintes pour la table `inclut_frais_devis`
--
ALTER TABLE `inclut_frais_devis`
  ADD CONSTRAINT `inclut_frais_devis_ibfk_1` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`devis_id`),
  ADD CONSTRAINT `inclut_frais_devis_ibfk_2` FOREIGN KEY (`id_frais`) REFERENCES `frais` (`frais_id`);

--
-- Contraintes pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`id_association`) REFERENCES `association` (`association_id`);

--
-- Contraintes pour la table `collaborateur`
--
ALTER TABLE `collaborateur`
  ADD CONSTRAINT `collaborateur_ibfk_1` FOREIGN KEY (`id_societe`) REFERENCES `societe` (`societe_id`);


--
-- Contraintes pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD CONSTRAINT `conseil_ibfk_1` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`),
  ADD CONSTRAINT `conseil_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`admin_id`);


--
-- Contraintes pour la table `devis`
--
ALTER TABLE `devis`
  ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (`id_societe`) REFERENCES `societe` (`societe_id`);


--
-- Contraintes pour la table `don`
--
ALTER TABLE `don`
  ADD CONSTRAINT `don_ibfk_1` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`),
  ADD CONSTRAINT `don_ibfk_2` FOREIGN KEY (`id_association`) REFERENCES `association` (`association_id`);

--
-- Contraintes pour la table `discute_dans`
--
ALTER TABLE `discute_dans`
  ADD CONSTRAINT `discute_dans_ibfk_1` FOREIGN KEY (`id_salon`) REFERENCES `salon` (`salon_id`),
  ADD CONSTRAINT `discute_dans_ibfk_2` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`);

--
-- Contraintes pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`);

--
-- Contraintes pour la table `facture`
--
ALTER TABLE `facture`
  ADD CONSTRAINT `facture_ibfk_1` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`devis_id`),
  ADD CONSTRAINT `facture_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`prestataire_id`);

--
-- Contraintes pour la table `note_prestataire`
--
ALTER TABLE `note_prestataire`
  ADD CONSTRAINT `note_prestataire_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`prestataire_id`),
  ADD CONSTRAINT `note_prestataire_ibfk_2` FOREIGN KEY (`id_evaluation`) REFERENCES `evaluation` (`evaluation_id`);

--
-- Contraintes pour la table `participe_activite`
--
ALTER TABLE `participe_activite`
  ADD CONSTRAINT `participe_activite_ibfk_1` FOREIGN KEY (`id_activite`) REFERENCES `activite` (`activite_id`),
  ADD CONSTRAINT `participe_activite_ibfk_2` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`);

--
-- Contraintes pour la table `participe_association`
--
ALTER TABLE `participe_association`
  ADD CONSTRAINT `participe_association_ibfk_1` FOREIGN KEY (`id_association`) REFERENCES `association` (`association_id`),
  ADD CONSTRAINT `participe_association_ibfk_2` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`);

--
-- Contraintes pour la table `participe_evenement`
--
ALTER TABLE `participe_evenement`
  ADD CONSTRAINT `participe_evenement_ibfk_1` FOREIGN KEY (`id_evenement`) REFERENCES `evenements` (`evenement_id`),
  ADD CONSTRAINT `participe_evenement_ibfk_2` FOREIGN KEY (`id_collaborateur`) REFERENCES `collaborateur` (`collaborateur_id`);

--
-- Contraintes pour la table `signalement`
--
ALTER TABLE `signalement`
  ADD CONSTRAINT `signalement_ibfk_1` FOREIGN KEY (`id_societe`) REFERENCES `societe` (`societe_id`);
COMMIT;

/* Ajout de données */
-- Administrateurs système
INSERT INTO admin (username, password, token, expiration) VALUES
('admin', '3c534fd5e3dce4a0a207354c5a41a4670490f1661aea86d0db72915b939346a5', NULL, NULL);