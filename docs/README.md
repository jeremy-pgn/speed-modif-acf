# Speed Modif ACF

> Application web sécurisée pour simplifier l'édition des champs ACF WordPress

## Description

Speed Modif ACF est une application web développée pour résoudre un problème observé en stage : les clients non-techniques ont du mal à modifier les contenus de leurs sites WordPress via les champs ACF (Advanced Custom Fields).

L'application propose une **interface intuitive** permettant d'éditer facilement les champs ACF sans passer par l'interface WordPress complexe. Elle reste totalement autonome et se synchronise uniquement avec la base de données WordPress pour mettre à jour les champs ciblés.

## Fonctionnalités principales

- **Authentification sécurisée** avec sessions PHP et mots de passe chiffrés
- **Édition temps réel** de 34 champs ACF organisés en 7 sections (Identité, Header, Présentation, Partenaires, Services, Carrousel, Contact)
- **Recherche globale** en temps réel avec surlignage des résultats
- **Historique complet** des modifications (qui a changé quoi et quand)
- **Synchronisation automatique** avec WordPress via API REST
- **Interface responsive** Bootstrap adaptée mobile et desktop
- **Protection CSRF/XSS** et requêtes préparées PDO

## Technologies utilisées

**Frontend**
- HTML5 sémantique
- SCSS compilé en CSS avec Bootstrap 5
- JavaScript ES6 (API REST client)

**Backend**
- PHP 8.1 (API REST, authentification, CRUD)
- MySQL 8.0 via WAMP (3 tables : utilisateurs, champs ACF, historique)

**DevOps**
- Docker (serveur web Apache + PHP)
- WAMP (base de données MySQL locale)
- Git & GitHub
- VS Code

## Architecture

**Environnement de développement :**
- **Docker** : Serveur web conteneurisé (PHP 8.1 + Apache) sur port 8080
- **WAMP** : Base de données MySQL sur port 3306
- **Connexion** : Docker → WAMP via `host.docker.internal`

**Flux de données :**
```
Application (Docker:8080)
         ↓
    API PHP REST
         ↓
Base SMA (WAMP:3306) ←→ Base WordPress (WAMP:3306)
```

## Structure du projet

```
SMA/
├── api/              # Backend PHP
│   ├── auth.php      # Authentification
│   ├── csrf.php      # Token CSRF
│   ├── config.php    # Configuration BDD
│   └── fields.php    # API REST (GET/PUT)
├── css/              # Styles SCSS compilés
│   └── styles.css
├── js/               # Frontend JavaScript
│   ├── api.js        # Client API REST
│   └── app.js        # Interface utilisateur
├── database/
│   └── sma_database.sql
├── index.html        # Page de connexion
├── dashboard.html    # Interface principale
└── docker-compose.yml
```

## Installation

### Prérequis
- Docker Desktop
- WAMP Server (MySQL actif)
- Git

### Étapes

1. **Cloner le repository**
```bash
git clone https://github.com/username/speed-modif-acf.git
cd speed-modif-acf
```

2. **Configurer la base de données WAMP**
```sql
-- Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
CREATE DATABASE IF NOT EXISTS sma_database;
USE sma_database;
SOURCE database/sma_database.sql;
```

3. **Lancer Docker**
```bash
docker-compose build --no-cache
docker-compose up -d
```

4. **Vérifier le démarrage**
```bash
docker-compose ps
docker-compose logs web
```

5. **Accéder à l'application**
- **Application** : http://localhost:8080
- **phpMyAdmin** : http://localhost/phpmyadmin
- **Identifiants** : admin@test.com / (mot de passe hashé en base)

## Sécurité implémentée

- ✅ Protection CSRF avec tokens
- ✅ Authentification par sessions sécurisées
- ✅ Hachage `password_verify()` pour les mots de passe
- ✅ Requêtes préparées PDO (anti-injection SQL)
- ✅ Validation et sanitisation des entrées
- ✅ Transactions avec rollback automatique
- ✅ Traçabilité RGPD (historique avec IP et timestamps)

## Base de données

**3 tables principales :**
- `sma_users` : Gestion des utilisateurs
- `sma_acf_fields` : Stockage des champs ACF (34 champs)
- `sma_field_history` : Historique des modifications

Contraintes d'intégrité référentielle avec cascades et relations établies.

**Configuration :**
- **Host** : `host.docker.internal` (pour connexion Docker → WAMP)
- **Port** : 3306
- **Base SMA** : `sma_database`
- **Base WordPress** : `wordpress_db` (synchronisation)

## Améliorations prévues

**Court terme**
- Remplacement des `prompt()` par modales Bootstrap
- Messages de confirmation après sauvegarde
- Gestion de l'upload d'images

**Moyen terme**
- Support multi-sites WordPress
- Tableau de bord analytique

## Contexte du projet

**Formation** : Titre professionnel DWWM (Développeur Web et Web Mobile) - Niveau 5  
**Période de stage** : 2025
**Type** : Projet personnel post-stage pour validation des 8 compétences DWWM

## Auteur

Développé dans le cadre de la formation DWWM pour démontrer les compétences en développement web sécurisé, architecture API REST et déploiement Docker.

---

*Fast. Simple. Secure.*
