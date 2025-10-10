-- =====================================
-- SPEED MODIF ACF - DATABASE SCHEMA
-- =====================================

-- Suppression des tables existantes
DROP TABLE IF EXISTS sma_field_history;
DROP TABLE IF EXISTS sma_acf_fields;
DROP TABLE IF EXISTS sma_sessions;
DROP TABLE IF EXISTS sma_users;

-- Table des utilisateurs
CREATE TABLE sma_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table des sessions utilisateur
CREATE TABLE sma_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES sma_users(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at)
);

-- Table des champs ACF
CREATE TABLE sma_acf_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_key VARCHAR(100) UNIQUE NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_label VARCHAR(200) NOT NULL,
    field_type ENUM('text', 'textarea', 'image', 'url', 'number', 'email') DEFAULT 'text',
    field_group VARCHAR(100) NOT NULL,
    section VARCHAR(50) NOT NULL,
    field_value TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_modified_by INT,
    last_modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (last_modified_by) REFERENCES sma_users(id),
    INDEX idx_section (section),
    INDEX idx_group (field_group),
    INDEX idx_active (is_active)
);

-- Table de l'historique des modifications
CREATE TABLE sma_field_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('create', 'update', 'delete') NOT NULL,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (field_id) REFERENCES sma_acf_fields(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES sma_users(id),
    INDEX idx_field (field_id),
    INDEX idx_user (user_id),
    INDEX idx_date (created_at)
);

-- Indexes supplémentaires pour optimisation
CREATE INDEX idx_users_email ON sma_users(email);
CREATE INDEX idx_fields_key ON sma_acf_fields(field_key);
CREATE INDEX idx_sessions_user ON sma_sessions(user_id);
