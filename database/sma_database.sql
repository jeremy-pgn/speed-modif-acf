-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 15 oct. 2025 à 12:29
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
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sma_acf_fields`
--

INSERT INTO `sma_acf_fields` (`id`, `field_key`, `field_name`, `field_label`, `field_type`, `field_group`, `section`, `field_value`, `is_active`, `last_modified_by`, `last_modified_at`, `created_at`) VALUES
(1, 'identite_visuelle_group_logo', 'logo', 'Logo', 'image', 'identite_visuelle_group', 'identite', 'logo-entreprise.png', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(2, 'identite_visuelle_group_bouton_fixe_mobile_titre_btn_fixe', 'mobile_titre', 'Titre Bouton Mobile', 'text', 'bouton_fixe_mobile', 'identite', 'Nous contacter par téléphone', 1, 1, '2025-10-09 00:02:36', '2025-10-08 18:02:10'),
(3, 'identite_visuelle_group_bouton_fixe_mobile_numero_btn_fixe', 'mobile_number', 'Numéro Bouton Mobile', 'text', 'bouton_fixe_mobile', 'identite', '06 09 xx xx xx', 1, 1, '2025-10-14 22:26:16', '2025-10-08 18:02:10'),
(4, 'deco_elements', 'deco_elements', 'Éléments Décoratifs', 'textarea', 'deco_group', 'identite', '3 éléments configurés', 1, 1, '2025-10-08 18:02:10', '2025-10-08 18:02:10'),
(5, 'header_group_header_titre', 'header_titre', 'Titre Header', 'text', 'header_group', 'header', 'Klean Peinture', 1, 1, '2025-10-14 22:45:10', '2025-10-08 18:02:10'),
(6, 'header_group_header_description', 'header_description', 'Description Header', 'textarea', 'header_group', 'header', 'Nous sommes spécialisés dans...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(7, 'header_group_image_arriere_plan', 'image_arriere_plan', 'Image Arrière-plan', 'image', 'header_group', 'header', 'header-bg.jpg', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(8, 'header_group_bouton_contact_texte_bouton', 'texte_bouton', 'Texte Bouton Contact', 'text', 'bouton_contact', 'header', 'Nous contacter', 1, 1, '2025-10-10 23:21:06', '2025-10-08 18:02:10'),
(9, 'header_group_bouton_contact_lien_bouton', 'lien_bouton', 'Lien Bouton Contact', 'url', 'bouton_contact', 'header', '#contact', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(10, 'presentation_group_presentation_titre', 'presentation_titre', 'Titre Présentation', 'text', 'presentation_group', 'presentation', 'Notre expertise', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(11, 'presentation_group_presentation_paragraphe', 'presentation_paragraphe', 'Texte Présentation', 'textarea', 'presentation_group', 'presentation', 'Forte de plusieurs années...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(12, 'presentation_group_image_cercle', 'image_cercle', 'Image Cercle', 'image', 'presentation_group', 'presentation', 'presentation-circle.jpg', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(13, 'presentation_group_texte_cercle_devis', 'cercle_devis', 'Texte Cercle Devis', 'text', 'presentation_group', 'presentation', 'Devis gratuit', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(14, 'presentation_group_texte_cercle_devis_2', 'cercle_devis2', 'Texte Cercle Devis 2', 'text', 'presentation_group', 'presentation', 'Sous 24h', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(15, 'partenaire_group_titre_partenaires', 'titre_partenaires', 'Titre Partenaires', 'text', 'partenaire_group', 'partenaires', 'Nos partenaires', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(16, 'partenaire_group_paragraphe_partenaires_1', 'paragraphe_partenaires_1', 'Paragraphe 1', 'textarea', 'partenaire_group', 'partenaires', 'Nous travaillons avec...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(17, 'partenaire_group_logo_partenaires_1', 'logo_partenaires_1', 'Logo Partenaire 1', 'image', 'partenaire_group', 'partenaires', 'partner1.png', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(18, 'partenaire_group_logo_partenaires_2', 'logo_partenaires_2', 'Logo Partenaire 2', 'image', 'partenaire_group', 'partenaires', 'partner2.png', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(19, 'partenaire_group_logo_partenaires_3', 'logo_partenaires_3', 'Logo Partenaire 3', 'image', 'partenaire_group', 'partenaires', 'partner3.png', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(20, 'partenaire_group_logo_partenaires_4', 'logo_partenaires_4', 'Logo Partenaire 4', 'image', 'partenaire_group', 'partenaires', 'partner4.png', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(21, 'partenaire_group_paragraphe_partenaires_2', 'paragraphe_partenaires_2', 'Paragraphe 2', 'textarea', 'partenaire_group', 'partenaires', 'Ces collaborations...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(22, 'service_group_titre_services', 'titre_services', 'Titre Services', 'text', 'service_group', 'services', 'Nos services', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(23, 'service_group_paragraphe_services', 'paragraphe_services', 'Description Services', 'textarea', 'service_group', 'services', 'Découvrez notre gamme...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(24, 'service_group_service_1_titre_sans_gras', 'service_1_titre', 'Service 1 - Titre', 'text', 'service_1', 'services', 'Consultation', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(25, 'service_group_service_1_paragraphe_principal', 'service_1_description', 'Service 1 - Description', 'textarea', 'service_1', 'services', 'Nous vous accompagnons...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(26, 'service_group_service_2_titre_sans_gras2', 'service_2_titre', 'Service 2 - Titre', 'text', 'service_2', 'services', 'Réalisation', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(27, 'service_group_service_2_paragraphe_principal2', 'service_2_description', 'Service 2 - Description', 'textarea', 'service_2', 'services', 'Notre équipe réalise...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(28, 'carrousel_group_titre_carrousel_principal', 'titre_carrousel', 'Titre Carrousel', 'text', 'carrousel_group', 'carrousel', 'Nos réalisations', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(29, 'carrousel_group_image_carrousel_1_group_Titre_carrousel_1', 'carrousel_1_titre', 'Carrousel 1 - Titre', 'text', 'image_carrousel_1_group', 'carrousel', 'Projet résidentiel', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(30, 'carrousel_group_image_carrousel_1_group_description_carrousel_1', 'carrousel_1_description', 'Carrousel 1 - Description', 'textarea', 'image_carrousel_1_group', 'carrousel', 'Rénovation complète...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(31, 'carrousel_group_image_carrousel_2_group_Titre_carrousel_2', 'carrousel_2_titre', 'Carrousel 2 - Titre', 'text', 'image_carrousel_2_group', 'carrousel', 'Projet commercial', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(32, 'carrousel_group_image_carrousel_3_group_Titre_carrousel_3', 'carrousel_3_titre', 'Carrousel 3 - Titre', 'text', 'image_carrousel_3_group', 'carrousel', 'Projet industriel', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(33, 'contact_group_contact_title', 'contact_title', 'Titre Contact', 'text', 'contact_group', 'contact', 'Contactez-nous', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10'),
(34, 'contact_group_contact_paragraph', 'contact_paragraph', 'Texte Contact', 'textarea', 'contact_group', 'contact', 'N\'hésitez pas à nous contacter...', 1, 1, '2025-10-08 23:47:53', '2025-10-08 18:02:10');

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
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sma_field_history`
--

INSERT INTO `sma_field_history` (`id`, `field_id`, `user_id`, `action`, `old_value`, `new_value`, `ip_address`, `created_at`) VALUES
(1, 1, 1, 'create', NULL, 'logo-entreprise.png', '127.0.0.1', '2025-10-08 18:02:10'),
(2, 2, 1, 'create', NULL, 'Appelez-nous', '127.0.0.1', '2025-10-08 18:02:10'),
(3, 3, 1, 'create', NULL, '+33 1 23 45 67 89', '127.0.0.1', '2025-10-08 18:02:10'),
(4, 5, 1, 'update', NULL, 'Bienvenue chez notre entreprise', '127.0.0.1', '2025-10-08 18:02:10'),
(5, 6, 1, 'update', NULL, 'Nous sommes spécialisés dans...', '127.0.0.1', '2025-10-08 18:02:10'),
(6, 1, 1, 'create', NULL, 'logo-entreprise.png', '127.0.0.1', '2025-10-08 18:08:22'),
(7, 2, 1, 'create', NULL, 'Appelez-nous', '127.0.0.1', '2025-10-08 18:08:22'),
(8, 3, 1, 'create', NULL, '+33 1 23 45 67 89', '127.0.0.1', '2025-10-08 18:08:22'),
(9, 5, 1, 'update', NULL, 'Bienvenue chez notre entreprise', '127.0.0.1', '2025-10-08 18:08:22'),
(10, 6, 1, 'update', NULL, 'Nous sommes spécialisés dans...', '127.0.0.1', '2025-10-08 18:08:22'),
(11, 3, 1, 'update', '+33 1 23 45 67 90', '+33 1 23 45 67 90', '172.18.0.1', '2025-10-08 19:19:54'),
(12, 3, 1, 'update', '+33 1 23 45 67 90', '+33 1 23 45 67 911', '172.18.0.1', '2025-10-08 19:30:59'),
(13, 3, 1, 'update', '+33 1 23 45 67 911', '+33 1 23 45 67 911', '172.18.0.1', '2025-10-08 19:36:16'),
(14, 3, 1, 'update', '+33 1 23 45 67 911', '+33 1 23 45 67 91', '172.18.0.1', '2025-10-08 20:11:12'),
(15, 3, 1, 'update', '+33 1 23 45 67 91', '+33 1 23 45 67 911', '172.18.0.1', '2025-10-08 23:52:05'),
(16, 3, 1, 'update', '+33 1 23 45 67 911', '+33 1 23 45 67 911', '172.18.0.1', '2025-10-08 23:54:46'),
(17, 2, 1, 'update', 'Appelez-nous', 'Appelez-nous vite !', '172.18.0.1', '2025-10-08 23:55:17'),
(18, 2, 1, 'update', 'Appelez-nous vite !', 'Nous contacter par téléphone !', '172.18.0.1', '2025-10-09 00:02:29'),
(19, 2, 1, 'update', 'Nous contacter par téléphone !', 'Nous contacter par téléphone', '172.18.0.1', '2025-10-09 00:02:36'),
(20, 3, 1, 'update', '+33 1 23 45 67 911', '+33 1 23 45 67 911', '172.18.0.1', '2025-10-09 00:02:49'),
(21, 3, 1, 'update', '+33 1 23 45 67 911', '+33 1 23 45 67 911', '172.18.0.1', '2025-10-09 00:04:57'),
(22, 5, 1, 'update', 'Bienvenue chez notre entreprise', 'Klean Peinture yeah', '172.18.0.1', '2025-10-09 00:06:40'),
(23, 3, 1, 'update', '+33 1 23 45 67 911', '+33 1 23 45 67 912', '172.18.0.1', '2025-10-09 16:26:25'),
(24, 3, 1, 'update', '+33 1 23 45 67 912', '+33 1 23 45 67 912', '172.18.0.1', '2025-10-09 16:26:46'),
(25, 3, 1, 'update', '+33 1 23 45 67 912', '+33 1 23 45 67 66', '172.18.0.1', '2025-10-09 16:41:52'),
(26, 3, 1, 'update', '+33 1 23 45 67 66', '06 xx xx xx xx', '172.18.0.1', '2025-10-09 16:45:09'),
(27, 3, 1, 'update', '06 xx xx xx xx', '06 05 xx xx xx', '172.18.0.1', '2025-10-09 18:49:10'),
(28, 3, 1, 'update', '06 05 xx xx xx', '06 07 xx xx xx', '172.18.0.1', '2025-10-09 22:28:10'),
(29, 5, 1, 'update', 'Klean Peinture yeah', 'Klean Peinture yeah', '172.18.0.1', '2025-10-09 23:44:01'),
(30, 3, 1, 'update', '06 07 xx xx xx', '06 08 xx xx xx', '172.18.0.1', '2025-10-10 23:01:47'),
(31, 5, 1, 'update', 'Klean Peinture yeah', 'Klean Peinture yeahh', '172.18.0.1', '2025-10-10 23:02:17'),
(32, 3, 1, 'update', '06 08 xx xx xx', '06 09 xx xx xx', '172.18.0.1', '2025-10-10 23:13:04'),
(33, 5, 1, 'update', 'Klean Peinture yeahh', 'Klean Peinture yeahh', '172.18.0.1', '2025-10-10 23:13:38'),
(34, 5, 1, 'update', 'Klean Peinture yeahh', 'Klean Peinture', '172.18.0.1', '2025-10-10 23:13:59'),
(35, 8, 1, 'update', 'Nous contacter', 'Nous contacter ?', '172.18.0.1', '2025-10-10 23:14:49'),
(36, 8, 1, 'update', 'Nous contacter ?', 'Nous contacter', '172.18.0.1', '2025-10-10 23:21:06'),
(37, 3, 1, 'update', '06 09 xx xx xx', '06 10 xx xx xx', '172.18.0.1', '2025-10-10 23:27:40'),
(38, 3, 1, 'update', '06 10 xx xx xx', '06 11 xx xx xx', '172.18.0.1', '2025-10-14 21:56:42'),
(39, 3, 1, 'update', '06 11 xx xx xx', '06 12 xx xx xx', '172.18.0.1', '2025-10-14 22:11:49'),
(40, 3, 1, 'update', '06 12 xx xx xx', '06 09 xx xx xx', '172.18.0.1', '2025-10-14 22:26:16'),
(41, 5, 1, 'update', 'Klean Peinture', 'Klean Peinture !!', '172.18.0.1', '2025-10-14 22:44:50'),
(42, 5, 1, 'update', 'Klean Peinture !!', 'Klean Peinture', '172.18.0.1', '2025-10-14 22:45:10');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sma_users`
--

INSERT INTO `sma_users` (`id`, `email`, `password`, `name`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Test', 'admin', 1, '2025-10-15 14:19:22', '2025-10-14 22:15:27', '2025-10-15 14:19:22');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
