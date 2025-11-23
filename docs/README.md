# Speed Modif ACF

> Application web sécurisée pour la modification simplifiée des champs ACF WordPress

##  Description

Speed Modif ACF est une application web développée pour résoudre une problématique observée lors d'un stage chez **Argent Virgule Net** : la difficulté pour les clients non-techniques de modifier les contenus de leurs sites WordPress via les champs ACF (Advanced Custom Fields).

Cette solution propose une **interface utilisateur intuitive** permettant d'éditer facilement les champs ACF sans passer par l'interface WordPress complexe.

## ✨ Fonctionnalités

### 🔐 **Authentification Sécurisée**
- Système de connexion avec `password_verify()` PHP
- Gestion des sessions sécurisées  
- Validation des champs obligatoires
- Protection contre les attaques par force brute

### ✏️ **Gestion des Champs ACF**
- Interface organisée par sections (7 sections : Identité, Header, Présentation, etc.)
- Édition temps réel avec prévisualisation
- API REST complète (GET/PUT) avec authentification requise
- Synchronisation SMA → WordPress automatisée

### 📱 **Interface Responsive Bootstrap**
- Design adaptatif complet (375px → 1920px+)
- Sidebar mobile avec offcanvas
- Recherche globale temps réel avec surlignage
- 54+ champs ACF organisés et accessibles

### 📊 **Historique et Traçabilité**
- Enregistrement de toutes les modifications (table sma_field_history)
- horodatage, utilisateur, action et titre du champ ACF
- Interface dédiée pour consulter l'historique
- Sauvegarde IP et données old/new côté serveur.

### **Architecture Hybride**

#### **Développement (actuel):**
- Speed Modif ACF (Docker:8080) ←→ Base WordPress (WAMP:3306)

#### **Architecture Finale :**
- **Docker** : Serveur web conteneurisé (PHP 8.1 + Apache) - Port 8080 - service MySQL Docker défini mais non utilisé aujourd’hui (prévu)
- **WAMP** : Base de données MySQL - Port 3306
- **GitHub** : Versioning et déploiement du code source

#### **Production (déploiement):**
- Speed Modif ACF (Serveur Docker)
- Base SMA (MySQL Docker/dédié) 
- Site WordPress client (reste sur son hébergement existant)

#### **Flux de données:**
- App SMA lit/modifie les champs ACF via connexion réseau
- Historique stocké dans base SMA dédiée
- Site WordPress client non impacté

## 🚀 Installation et Déploiement

#### **Développement:**
- Docker Desktop
- Git  
- WAMP (accès base WordPress de test)

#### **Production:**
- Serveur Docker
- Accès réseau à la base WordPress du client
- MySQL pour base SMA (Docker ou serveur dédié)

### **Installation**

1. Cloner le repository
- git clone https://github.com/username/speed-modif-acf.git
- cd speed-modif-acf

2. Construire et lancer le serveur Docker
- docker-compose build --no-cache
- docker-compose up -d


3. Configuration base de données WAMP
- Démarrer WAMP MySQL
- Console MySQL :
- CREATE DATABASE IF NOT EXISTS sma_database;
- USE sma_database;
- SOURCE database/sma_database.sql;

4. Vérifier le fonctionnement
- docker-compose ps
- docker-compose logs web

5. Accéder à l'application
- **Application** : http://localhost:8080
- **Base WAMP** : http://localhost/phpmyadmin
- **Vérification Docker** : `docker-compose ps`


### **Structure de Fichiers Requise**

Avant le déploiement, vérifier cette organisation :
- api/ : auth.php, config.php, fields.php (authentification et API REST)
- css/ : styles.css compilé depuis SCSS avec variables CSS
- js/ : api.js (client REST), app.js (interface utilisateur 600+ lignes)
- database/ : sma_database.sql avec 3 tables et contraintes
- HTML : index.html (connexion), dashboard.html (interface principale)

### **Configuration Base de Données**

Import via console MySQL WAMP :
- CREATE DATABASE IF NOT EXISTS sma_database;
- USE sma_database;
- SOURCE database/sma_database.sql;

Vérification :
- USE sma_database;
- SHOW TABLES;

Tables créées :
- sma_users : Gestion des utilisateurs
- sma_acf_fields : Stockage des champs ACF  
- sma_field_history : Historique des modifications

Structure complète avec contraintes d'intégrité, indexation et relations entre tables.

## 🔒 Sécurité et Développement

### **Authentification Sécurisée Implémentée**
- ✅ Vérification par `password_verify()` (auth.php ligne 40)
- ✅ Sessions PHP sécurisées avec `session_start()`
- ✅ Validation POST obligatoire avec contrôle de méthode
- ✅ Protection contre l'injection SQL via PDO prepared statements
- ✅ Validation des champs obligatoires (email, password)

### **API REST Sécurisée**  
- ✅ Contrôle d'authentification sur toutes les modifications (fields.php)
- ✅ Transactions base de données avec `beginTransaction()/commit()`
- ✅ Historique complet des modifications (table sma_field_history)
- ✅ Gestion des erreurs avec rollback automatique
- ✅ Validation JSON et sanitisation des entrées

### **Interface Responsive et Accessible**
- ✅ Variables CSS pour cohérence (styles.css :root)
- ✅ Breakpoints mobiles : 480px, 768px, 1024px (lignes 280-290)
- ✅ Focus visible pour navigation clavier (:focus-visible)
- ✅ Navigation offcanvas mobile avec Bootstrap
- ✅ Sidebar adaptative et hamburger menu

### **Architecture Modulaire**
- ✅ Séparation API/Interface (app.js 600+ lignes documentées)
- ✅ Gestion d'état avec variables globales (acfData, currentSection)
- ✅ Système de recherche temps réel avec surlignage
- ✅ Synchronisation WordPress bidirectionnelle

### **Outils de Développement**
- Docker-compose pour environnement reproductible
- Structure organisée par responsabilités (api/, css/, js/)
- Gestion d'erreurs complète avec messages utilisateur
- Workflow Git standard (add, commit, push)

## 📈 Contexte du Projet

### **Inspiration Professionnelle**
- **Entreprise** : Argent Virgule Net (Agence de communication digitale)
- **Période d'observation** : 07/04/2025 - 05/05/2025 (4 semaines de stage)
- **Problématique observée** : Clients non-techniques en difficulté pour modifier les contenus WordPress

### **Problème Identifié**
- Interface WordPress trop complexe pour les utilisateurs finaux
- Difficulté d'accès aux champs ACF (Advanced Custom Fields)
- Besoin d'une solution simplifiée et sécurisée
- Demande récurrente d'assistance pour des modifications simples

### **Solution Développée**
- Interface dédiée avec authentification sécurisée
- API REST pour communication avec WordPress existant
- Historique complet des modifications
- Architecture hybride Docker + WAMP pour flexibilité

### **Statut Projet**
- **Type** : Projet personnel post-stage (entreprise non impliquée)
- **Objectif** : Démonstration de compétences DWWM complètes
- **Approche** : Solution technique répondant à un besoin réel observé

## 👥 Équipe et Informations

### **Développeur**
- **Nom** : [Votre Nom]
- **Formation** : Titre professionnel DWWM (Développeur Web et Web Mobile) - Niveau 5
- **Contexte** : Dossier de projet pour validation des compétences

### **Technologies Maîtrisées**
- **Frontend** : HTML5, CSS3/SCSS, JavaScript ES6, Bootstrap 5
- **Backend** : PHP 8.1, API REST, MySQL 8.0  
- **Outils** : Docker, Git, VS Code, PDO, Sessions PHP
- **Architecture** : SPA (Single Page Application), MVC pattern

### **Compétences DWWM Démontrées**
- CP1 à CP8 : Installation environnement → Déploiement production
- Sécurité : Authentification, validation, protection SQL injection
- Accessibilité : Navigation clavier, responsive design
- Architecture : Séparation des responsabilités, API REST

### **Licence et Utilisation**
- Projet réalisé dans le cadre de la formation DWWM
- Code source documenté pour démonstration pédagogique
- Architecture adaptable pour projets similaires

---

*Application développée pour simplifier la gestion des contenus web*

