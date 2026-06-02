# Mnémo

Application web de mémorisation par répétition espacée, développée en stage au Lycée Rascol (Albi).

## Présentation

Mnémo permet de créer des modules thématiques contenant des items (photo + nom FR + nom EN + fonction), puis de s'entraîner via deux modes :
- **Mode Anki** : questions infinies avec feedback immédiat et suivi de progression personnalisé (algorithme SM-2)
- **Mode Test** : série de N questions avec score final et récapitulatif des erreurs

## Stack technique

- **Back-end** : PHP 8.3, Laravel 12
- **Base de données** : SQLite (développement) / MySQL (production)
- **Front-end** : Bootstrap 5.3, Bootstrap Icons, JavaScript natif
- **Authentification** : Laravel Breeze
- **Stockage photos** : Fichiers locaux via Laravel Storage

## Prérequis

- PHP >= 8.2 avec extensions : `pdo`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`
- Composer
- Git

## Installation rapide

### Option 1 — Via l'installateur web

1. Déposer les fichiers sur le serveur
2. Ouvrir `http://votre-domaine/install.php` dans un navigateur
3. Suivre les étapes de l'assistant

### Option 2 — Installation manuelle

```bash
# 1. Cloner le dépôt
git clone https://github.com/YoannFM-rascol/Mnemo.git
cd Mnemo

# 2. Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# 3. Copier et configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
# SQLite (défaut) : DB_CONNECTION=sqlite
# MySQL : renseigner DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Créer la base et le lien storage
php artisan migrate --seed
php artisan storage:link

# 6. Lancer le serveur de développement
php artisan serve
```

L'application est disponible sur `http://localhost:8000`.

**Compte de démo** : `demo@mnemo.fr` / `demo1234`

## Structure du projet

```
app/
├── Http/Controllers/    # Contrôleurs (Module, Item, Test, Anki, Library, Progress...)
├── Models/              # Modèles Eloquent (User, Module, Item, Progress, Score)
└── Services/
    └── QuizGenerator.php   # Moteur de génération des QCM (Q1-Q8)
database/
├── migrations/          # Schéma de base de données
└── seeders/             # Données de démonstration
resources/views/
├── layouts/             # Layout principal (dark theme)
├── modules/             # CRUD modules
├── items/               # CRUD items (+ import CSV)
├── quiz/
│   ├── anki/            # Mode Anki
│   └── test/            # Mode Test
├── library/             # Bibliothèque publique
└── progress/            # Progression et historique
public/
└── install.php          # Installateur autonome
```

## Types de questions (Q1-Q8)

| Type | Énoncé      | Réponse attendue |
|------|-------------|-----------------|
| Q1   | Photo       | Nom français    |
| Q2   | Photo       | Fonction        |
| Q3   | Fonction    | Photo           |
| Q4   | Nom français| Nom anglais     |
| Q5   | Nom anglais | Photo           |
| Q6   | Fonction    | Nom français    |
| Q7   | Nom anglais | Nom français    |
| Q8   | Photo       | Nom anglais     |

## Auteur

Développé par **YoannFM** — [github.com/YoannFM-rascol](https://github.com/YoannFM-rascol/)  
Lycée Rascol, Albi — Projet de stage développement web
