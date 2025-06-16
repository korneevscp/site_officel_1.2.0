-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Lun 16 Juin 2025 à 12:20
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

-- Configuration du mode SQL et du fuseau horaire
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Sauvegarde des paramètres d'encodage actuels
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- Sélection de la base de données `trdcrft`
--
-- Base de données :  `trdcrft`
--

-- --------------------------------------------------------

-- Création de la table `admins` pour stocker les administrateurs
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- Identifiant unique de l'admin
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL, -- Nom d'utilisateur
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, -- Mot de passe hashé
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date de création
  PRIMARY KEY (`id`), -- Clé primaire
  UNIQUE KEY `username` (`username`) -- Nom d'utilisateur unique
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=5 ;

-- Insertion des données dans la table `admins`
INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(3, 'ADMIN', '$2y$10$MnHdvSzEy2OCmXHj99M4c.bOK4PY0PZZKNk.eDb2f9S7VrNwyqJc.', '2025-05-15 13:17:02'),
(4, 'test', '$2y$10$kl8SPW9aqHyMoVzvOSbLVuf3WyR7OdI2RGXlvKYVmSZ6blLwwz5Vq', '2025-05-15 13:17:17');

-- --------------------------------------------------------

-- Création de la table `articles` pour stocker les articles
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- Identifiant unique de l'article
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, -- Titre de l'article
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL, -- Contenu de l'article
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP, -- Date de création
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Date de modification
  `author_id` int(11) DEFAULT NULL, -- Identifiant de l'auteur (clé étrangère)
  PRIMARY KEY (`id`) -- Clé primaire
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=18 ;

-- Insertion des données dans la table `articles`
INSERT INTO `articles` (`id`, `title`, `content`, `created_at`, `updated_at`, `author_id`) VALUES
(8, 'CIA.GOV :', '<p><img src="https://www.cia.gov/static/cea7a65a7f7d05b07ca3103ee4a532ea/a654d/AgencySealMatrixCroppped.png"><br>.</p>', '2025-05-15 23:33:15', '2025-05-15 23:33:15', 4),
(9, 'tt', '<p>test ahahah&nbsp;<br><br><img src="https://i.pinimg.com/736x/7d/6c/db/7d6cdb9e8735addcc8b27745ae3309d8.jpg" alt="CIA TEST" width="446" height="446"><br>SES MOI&nbsp;</p>', '2025-06-16 09:53:14', '2025-06-16 09:53:14', 17),
(17, 'caca', '<p>caca <img src="https://i.pinimg.com/736x/db/19/fe/db19fe0d92c3dbd97ffe236ccbfff655.jpg" alt="" width="590" height="885"></p>', '2025-06-16 12:10:13', '2025-06-16 12:13:08', 7);

-- --------------------------------------------------------

-- Création de la table `loadouts` pour stocker les équipements des utilisateurs
CREATE TABLE IF NOT EXISTS `loadouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- Identifiant unique du loadout
  `user_id` int(11) NOT NULL, -- Identifiant de l'utilisateur (clé étrangère)
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL, -- Nom du loadout
  `description` text COLLATE utf8mb4_unicode_ci, -- Description du loadout
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL, -- Contenu détaillé du loadout
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP, -- Date de création
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Date de modification
  PRIMARY KEY (`id`), -- Clé primaire
  KEY `user_id` (`user_id`) -- Index sur user_id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- Création de la table `loadout_files` pour stocker les fichiers liés aux loadouts
CREATE TABLE IF NOT EXISTS `loadout_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- Identifiant unique du fichier
  `loadout_id` int(11) NOT NULL, -- Identifiant du loadout (clé étrangère)
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, -- Nom du fichier
  `filetype` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL, -- Type du fichier
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP, -- Date d'upload
  PRIMARY KEY (`id`), -- Clé primaire
  KEY `loadout_id` (`loadout_id`) -- Index sur loadout_id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- Création de la table `users` pour stocker les utilisateurs
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- Identifiant unique de l'utilisateur
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL, -- Nom d'utilisateur
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL, -- Email de l'utilisateur
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, -- Mot de passe hashé
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user', -- Rôle de l'utilisateur
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date de création
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, -- Mot de passe (doublon, à éviter)
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL, -- Avatar de l'utilisateur
  `description` text COLLATE utf8mb4_unicode_ci, -- Description de l'utilisateur
  PRIMARY KEY (`id`), -- Clé primaire
  UNIQUE KEY `username` (`username`), -- Nom d'utilisateur unique
  UNIQUE KEY `email` (`email`) -- Email unique
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=8 ;

-- Insertion des données dans la table `users`
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `password`, `avatar`, `description`) VALUES
(4, 'CIA', 'CIA@CIA.gov', '', 'user', '2025-05-15 21:22:24', '$2y$10$P.YsAxf6EJS.1H2iGdtVw.nXCc9KQ1JF85IaESd83dFL2WPzdF8jO', 'avatar_4_1747344244.png', 'cia.gov 😎'),
(5, 'CIA.GOV', 'test@test.fr', '$2y$10$Rty9Vdb1S35lgbkpB2R8xuRy5UNz/PWW1iv2dCaSmEu3Dvp5bz3hG', 'user', '2025-06-11 08:51:43', '', NULL, NULL),
(6, 'test', 'test@test.gov', '$2y$10$.TQO679nmYGOxfXETdAy2eCujSHUrlEThso.en7LkRspxzKBUgNY2', 'user', '2025-06-11 08:52:42', '', NULL, NULL),
(7, 'DD', 'my@my.my', '$2y$10$mKzFysxczghYLUFI6039Z.4CtZRgV6QSlenWQHDkuYZqIhIzsL5aK', 'user', '2025-06-16 07:57:03', '', 'avatar_7_1750060949.jpeg', 'hey j''aime les gg');

-- --------------------------------------------------------

-- Ajout des contraintes de clé étrangère pour assurer l'intégrité référentielle

-- La suppression d'un utilisateur supprime ses loadouts
ALTER TABLE `loadouts`
  ADD CONSTRAINT `loadouts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- La suppression d'un loadout supprime ses fichiers associés
ALTER TABLE `loadout_files`
  ADD CONSTRAINT `loadout_files_ibfk_1` FOREIGN KEY (`loadout_id`) REFERENCES `loadouts` (`id`) ON DELETE CASCADE;

-- Restauration des paramètres d'encodage précédents
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
