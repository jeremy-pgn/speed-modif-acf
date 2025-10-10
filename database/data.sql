-- =====================================
-- SPEED MODIF ACF - DONNÉES DE TEST
-- =====================================

-- Utilisateur par défaut (mot de passe : admin123)
INSERT INTO sma_users (email, password_hash, name, role, is_active) VALUES
('admin@speedmodifacf.com', '$2y$12$rQwC7qH8Gp0xR7vNzB2aAuJ4H.m8.Nz1pWqXsT9uYfGhIjKlMnOpQ', 'Administrateur', 'admin', TRUE),
('editor@speedmodifacf.com', '$2y$12$aBcDeF7890GhIjKlMnOpQrStUvWxYz1234567890AbCdEfGhIjKl', 'Éditeur', 'editor', TRUE),
('user@speedmodifacf.com', '$2y$12$xYz987654321AbCdEfGhIjKlMnOpQrStUvWxYz1234567890AbCdE', 'Utilisateur', 'user', TRUE);

-- Champs ACF - Section Identité
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('logo', 'logo', 'Logo', 'image', 'identite_visuelle_group', 'identite', 'logo-entreprise.png', 1),
('mobile_titre', 'mobile_titre', 'Titre Bouton Mobile', 'text', 'bouton_fixe_mobile', 'identite', 'Appelez-nous', 1),
('mobile_number', 'mobile_number', 'Numéro Bouton Mobile', 'text', 'bouton_fixe_mobile', 'identite', '+33 1 23 45 67 89', 1),
('deco_elements', 'deco_elements', 'Éléments Décoratifs', 'textarea', 'deco_group', 'identite', '3 éléments configurés', 1);

-- Champs ACF - Section Header
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('header_titre', 'header_titre', 'Titre Header', 'text', 'header_group', 'header', 'Bienvenue chez notre entreprise', 1),
('header_description', 'header_description', 'Description Header', 'textarea', 'header_group', 'header', 'Nous sommes spécialisés dans...', 1),
('image_arriere_plan', 'image_arriere_plan', 'Image Arrière-plan', 'image', 'header_group', 'header', 'header-bg.jpg', 1),
('texte_bouton', 'texte_bouton', 'Texte Bouton Contact', 'text', 'bouton_contact', 'header', 'Nous contacter', 1),
('lien_bouton', 'lien_bouton', 'Lien Bouton Contact', 'url', 'bouton_contact', 'header', '#contact', 1);

-- Champs ACF - Section Présentation
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('presentation_titre', 'presentation_titre', 'Titre Présentation', 'text', 'presentation_group', 'presentation', 'Notre expertise', 1),
('presentation_paragraphe', 'presentation_paragraphe', 'Texte Présentation', 'textarea', 'presentation_group', 'presentation', 'Forte de plusieurs années...', 1),
('image_cercle', 'image_cercle', 'Image Cercle', 'image', 'presentation_group', 'presentation', 'presentation-circle.jpg', 1),
('cercle_devis', 'cercle_devis', 'Texte Cercle Devis', 'text', 'presentation_group', 'presentation', 'Devis gratuit', 1),
('cercle_devis2', 'cercle_devis2', 'Texte Cercle Devis 2', 'text', 'presentation_group', 'presentation', 'Sous 24h', 1);

-- Champs ACF - Section Partenaires
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('titre_partenaires', 'titre_partenaires', 'Titre Partenaires', 'text', 'partenaire_group', 'partenaires', 'Nos partenaires', 1),
('paragraphe_partenaires_1', 'paragraphe_partenaires_1', 'Paragraphe 1', 'textarea', 'partenaire_group', 'partenaires', 'Nous travaillons avec...', 1),
('logo_partenaires_1', 'logo_partenaires_1', 'Logo Partenaire 1', 'image', 'partenaire_group', 'partenaires', 'partner1.png', 1),
('logo_partenaires_2', 'logo_partenaires_2', 'Logo Partenaire 2', 'image', 'partenaire_group', 'partenaires', 'partner2.png', 1),
('logo_partenaires_3', 'logo_partenaires_3', 'Logo Partenaire 3', 'image', 'partenaire_group', 'partenaires', 'partner3.png', 1),
('logo_partenaires_4', 'logo_partenaires_4', 'Logo Partenaire 4', 'image', 'partenaire_group', 'partenaires', 'partner4.png', 1),
('paragraphe_partenaires_2', 'paragraphe_partenaires_2', 'Paragraphe 2', 'textarea', 'partenaire_group', 'partenaires', 'Ces collaborations...', 1);

-- Champs ACF - Section Services
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('titre_services', 'titre_services', 'Titre Services', 'text', 'service_group', 'services', 'Nos services', 1),
('paragraphe_services', 'paragraphe_services', 'Description Services', 'textarea', 'service_group', 'services', 'Découvrez notre gamme...', 1),
('service_1_titre', 'service_1_titre', 'Service 1 - Titre', 'text', 'service_1', 'services', 'Consultation', 1),
('service_1_description', 'service_1_description', 'Service 1 - Description', 'textarea', 'service_1', 'services', 'Nous vous accompagnons...', 1),
('service_2_titre', 'service_2_titre', 'Service 2 - Titre', 'text', 'service_2', 'services', 'Réalisation', 1),
('service_2_description', 'service_2_description', 'Service 2 - Description', 'textarea', 'service_2', 'services', 'Notre équipe réalise...', 1);

-- Champs ACF - Section Carrousel
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('titre_carrousel', 'titre_carrousel', 'Titre Carrousel', 'text', 'carrousel_group', 'carrousel', 'Nos réalisations', 1),
('carrousel_1_titre', 'carrousel_1_titre', 'Carrousel 1 - Titre', 'text', 'image_carrousel_1_group', 'carrousel', 'Projet résidentiel', 1),
('carrousel_1_description', 'carrousel_1_description', 'Carrousel 1 - Description', 'textarea', 'image_carrousel_1_group', 'carrousel', 'Rénovation complète...', 1),
('carrousel_2_titre', 'carrousel_2_titre', 'Carrousel 2 - Titre', 'text', 'image_carrousel_2_group', 'carrousel', 'Projet commercial', 1),
('carrousel_3_titre', 'carrousel_3_titre', 'Carrousel 3 - Titre', 'text', 'image_carrousel_3_group', 'carrousel', 'Projet industriel', 1);

-- Champs ACF - Section Contact
INSERT INTO sma_acf_fields (field_key, field_name, field_label, field_type, field_group, section, field_value, last_modified_by) VALUES
('contact_title', 'contact_title', 'Titre Contact', 'text', 'contact_group', 'contact', 'Contactez-nous', 1),
('contact_paragraph', 'contact_paragraph', 'Texte Contact', 'textarea', 'contact_group', 'contact', 'N\'hésitez pas à nous contacter...', 1);

-- Historique initial
INSERT INTO sma_field_history (field_id, user_id, action, new_value, ip_address) VALUES
(1, 1, 'create', 'logo-entreprise.png', '127.0.0.1'),
(2, 1, 'create', 'Appelez-nous', '127.0.0.1'),
(3, 1, 'create', '+33 1 23 45 67 89', '127.0.0.1'),
(5, 1, 'update', 'Bienvenue chez notre entreprise', '127.0.0.1'),
(6, 1, 'update', 'Nous sommes spécialisés dans...', '127.0.0.1');
