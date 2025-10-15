-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 15 oct. 2025 à 14:34
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sma_database`
--

-- --------------------------------------------------------

--
-- Structure de la table `sma_acf_fields`
--

DROP TABLE IF EXISTS `sma_acf_fields`;
CREATE TABLE IF NOT EXISTS `sma_acf_fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `field_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `field_label` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `field_type` enum('text','textarea','image','url','number','email') COLLATE utf8mb4_general_ci DEFAULT 'text',
  `field_group` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `section` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `field_value` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `last_modified_by` int DEFAULT NULL,
  `last_modified_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_key` (`field_key`),
  KEY `last_modified_by` (`last_modified_by`),
  KEY `idx_section` (`section`),
  KEY `idx_group` (`field_group`),
  KEY `idx_active` (`is_active`),
  KEY `idx_fields_key` (`field_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sma_field_history`
--

DROP TABLE IF EXISTS `sma_field_history`;
CREATE TABLE IF NOT EXISTS `sma_field_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `field_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` enum('create','update','delete') COLLATE utf8mb4_general_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_general_ci,
  `new_value` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_field` (`field_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sma_users`
--

DROP TABLE IF EXISTS `sma_users`;
CREATE TABLE IF NOT EXISTS `sma_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
