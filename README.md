# Système de Gestion Universitaire

Plateforme web complète de gestion universitaire développée en PHP natif avec une architecture MVC personnalisée.

## 🏛️ Fonctionnalités

| Module | Description |
|--------|-------------|
| **Authentification** | Connexion sécurisée par rôle (Admin, Enseignant, Étudiant, Finance, SGA) |
| **Étudiants** | Gestion des inscriptions, profils, relevés de notes |
| **Enseignants** | Gestion des cours, saisie et modification des notes |
| **Notes** | Saisie, verrouillage, demandes de modification via le SGA |
| **Cours** | Attribution enseignants/cours, gestion des crédits |
| **Finances** | Frais de scolarité, paiements, états financiers |
| **Messagerie** | Chat interne entre utilisateurs |
| **Tableau de bord** | Statistiques globales par rôle |

## 🗂️ Structure du projet

```
university-system/
├── app/
│   ├── Core/               # Noyau : Router, Controller, View, Database
│   ├── Helpers/            # Fonctions utilitaires (auth, url, flash...)
│   ├── Http/
│   │   ├── Controllers/    # Contrôleurs MVC
│   │   └── Middleware/     # Middlewares (auth, rôles, CSRF)
│   ├── Models/             # Modèles de données
│   ├── Repositories/       # Couche d'accès aux données (PDO)
│   ├── Services/           # Services métier (email, notifications...)
│   └── Views/              # Templates PHP par module
├── bootstrap/              # Chargement app + routes
├── config/                 # Configuration BDD, app, mail
├── database/
│   └── migrations/         # Scripts de création/migration des tables
├── public/                 # Point d entrée web (index.php, assets CSS/JS)
├── routes/                 # Définition des routes (web.php)
├── storage/                # Logs, uploads temporaires
├── vendor/                 # Dépendances Composer
├── .env                    # Variables d environnement (ne pas versionner)
├── .htaccess               # Réécriture URL Apache
└── composer.json           # Dépendances PHP
```

## ⚙️ Installation

### Prérequis
- XAMPP >= 8.0 (Apache + MySQL + PHP 8.0+)
- Composer >= 2.0

### Étapes

1. Cloner dans le dossier XAMPP
   `git clone <url> c:/xampp/htdocs/university-system`

2. Installer les dépendances
   `composer install`

3. Copier et configurer `.env`
   `cp .env.example .env`

4. Créer la base de données `university_system` via phpMyAdmin

5. Les tables sont créées automatiquement au premier accès (auto-migration).

### Variables d environnement (.env)

```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=university_system
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost/university-system
APP_ENV=development
APP_DEBUG=true
```

## 🔐 Rôles et accès

| Rôle        | Valeur | Accès                          |
|-------------|--------|--------------------------------|
| admin       | 1      | Gestion complète               |
| enseignant  | 2      | Cours + Notes                  |
| etudiant    | 3      | Consultation + Relevés         |
| finance     | 4      | Paiements + Rapports           |
| sga         | 5      | Validation des demandes        |

## 🛡️ Sécurité

- Protection CSRF sur tous les formulaires POST
- Mots de passe hachés avec password_hash() (bcrypt)
- Middleware de vérification de rôle sur chaque route
- Requêtes PDO préparées (protection injection SQL)

## 🧰 Technologies

- PHP 8.x natif (architecture MVC maison)
- MySQL / MariaDB
- Bootstrap 5 + CSS personnalisé
- JavaScript ES6 (Fetch API)
- PHPMailer (notifications email)
- Apache mod_rewrite
