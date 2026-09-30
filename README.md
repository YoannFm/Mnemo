# Mnemo

Application web de mémorisation par répétition espacée, développée au Lycée Rascol (Albi).

Mnemo est un projet **open source** distribué sous licence MIT (voir [LICENSE](LICENSE)). Aucune clé de licence, activation ni connexion à un serveur de licence n'est nécessaire pour l'installer ou l'utiliser.

## Présentation

Mnemo permet de créer des modules thématiques contenant des items (question + réponse), puis de s'entraîner via deux modes :

- **Mode Anki** - questions infinies avec feedback immédiat et suivi de progression (algorithme SM-2)
- **Mode Test** - série de N questions avec score final et récapitulatif des erreurs

Une bibliothèque publique permet de partager et dupliquer des modules entre utilisateurs.

## Fonctionnalités

### Apprentissage
- Création de modules avec items (question/réponse, image optionnelle)
- Import en masse d'items via CSV
- Mode Anki avec algorithme SM-2 (répétition espacée)
- Mode Test avec résultats détaillés
- Bibliothèque publique - partage et duplication de modules
- Suivi de progression et historique des scores

### Communauté
- Réactions emoji sur les articles
- Commentaires avec réponses imbriquées
- Signalement de commentaires et modules
- Modification et suppression de ses propres commentaires (historique conservé)

### Panel d'administration
- Gestion des utilisateurs (CRUD, rôles, bannissements, import/export CSV)
- Gestion des modules et articles (posts) avec éditeur TinyMCE
- Système de thèmes (mode sombre/clair personnalisable)
- Signalements avec workflow : en attente - sanctionné - non sanctionné
- Historique des commentaires modifiés/supprimés
- Sanctions et mutes temporaires ou définitifs
- Notifications personnalisées aux utilisateurs
- Logs d'activité avec purge automatique des entrées de plus de 30 jours
- Redirections, pages statiques, navbar configurable

### Sécurité
- Authentification Laravel Breeze
- Double authentification (2FA) TOTP optionnelle
- Système de mute (bloque commentaires et réactions)
- Fuseau horaire configurable depuis les paramètres

## Stack technique

- **Back-end** - PHP 8.3+, Laravel 13
- **Base de données** - SQLite (développement) / MySQL (production)
- **Front-end** - Bootstrap 5.3, Bootstrap Icons, Alpine.js, JavaScript natif
- **Éditeur** - TinyMCE 6 (hébergé localement)
- **Build** - Vite 8
- **Stockage** - Laravel Storage (`storage/app/public`)

## Prérequis

- PHP >= 8.3 avec extensions : `pdo`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`
- Composer
- Node.js >= 18
- Git

## Installation

### Option 1 - Via l'installateur web

1. Déposer les fichiers sur le serveur
2. Ouvrir `http://votre-domaine/install` dans un navigateur
3. Suivre les étapes de l'assistant

### Option 2 - Installation manuelle

```bash
# 1. Cloner le dépôt
git clone https://github.com/YoannFm/Mnemo.git
cd Mnemo

# 2. Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# 3. Copier et configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
# SQLite (défaut) : DB_CONNECTION=sqlite
# MySQL : renseigner DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Migrer la base et créer le lien storage
php artisan migrate
php artisan storage:link

# 6. Installer les assets front-end
npm install
npm run copy-vendors
npm run build

# 7. Lancer le serveur de développement
php artisan serve
```

L'application est disponible sur `http://localhost:8000`.

## Mises à jour et plugins

Les mises à jour et le catalogue de plugins sont distribués via GitHub, sans aucun serveur tiers :

- **Mises à jour** : la page **Admin - Mises à jour** lit la dernière [release GitHub](https://github.com/YoannFm/Mnemo/releases) et installe son archive `mnemo.zip`.
- **Plugins** : la page **Admin - Plugins** lit le catalogue [`plugins.json`](plugins.json) (voir [PLUGINS.md](PLUGINS.md)).

Pour une instance basée sur un fork, les variables `MNEMO_GITHUB_REPOSITORY` et `MNEMO_PLUGINS_CATALOG` du `.env` permettent de pointer vers d'autres sources.

### Publier une version

1. Mettre à jour `version` dans `config/mnemo.php` (ex. `1.0.2`) et le `CHANGELOG.md`, puis pousser
2. Créer et pousser le tag correspondant : `git tag v1.0.2 && git push origin v1.0.2`
3. Le workflow `.github/workflows/release.yml` construit l'archive `mnemo.zip` (code, dépendances PHP, assets compilés) et publie la release. Il échoue si le tag ne correspond pas à `config/mnemo.php`.

## Mise à jour (serveur)

```bash
git pull origin main
php artisan migrate
php artisan view:clear && php artisan config:clear
```

## Structure du projet

```
app/
- Http/Controllers/         # Contrôleurs publics et admin
- Http/Middleware/           # Auth, 2FA, maintenance, fuseau horaire...
- Models/                   # Modèles Eloquent
database/
- migrations/               # Schéma de base de données
resources/views/
- layouts/                  # Layouts (app, admin, guest)
- admin/                    # Vues du panel admin
- modules/                  # CRUD modules
- posts/                    # Articles publics
- library/                  # Bibliothèque publique
public/
- vendor/tinymce/           # TinyMCE 6 (local)
- vendor/bootstrap-icons/   # Bootstrap Icons (local)
```

## Licence

Ce projet est open source, publié sous licence [MIT](LICENSE). Vous êtes libre de l'utiliser, le modifier et le redistribuer selon les termes de cette licence. L'application fonctionne entièrement sans clé de licence ni vérification en ligne.

## Auteur

Développé par **YoannFM** - [github.com/YoannFM-rascol](https://github.com/YoannFM-rascol/)  
Lycée Rascol, Albi
