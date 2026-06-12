# Mnemo - Guide d'installation

## Prérequis

Avant d'installer Mnemo, assurez-vous que les composants suivants sont disponibles sur le serveur :

| Composant | Version minimale | Notes |
|---|---|---|
| PHP | 8.3 | Extensions requises : `pdo`, `pdo_mysql` ou `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` ou `imagick` |
| Composer | 2.x | Gestionnaire de dépendances PHP |
| Node.js | 18.x+ | Pour la compilation des assets frontend |
| npm | 9.x+ | Inclus avec Node.js |
| MySQL / MariaDB | 8.0+ | En production (SQLite suffisant pour le développement) |
| Git | 2.x | Pour cloner le dépôt |

## Étapes d'installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-organisation/mnemo.git
cd mnemo
```

### 2. Installer les dépendances PHP

```bash
composer install --no-dev --optimize-autoloader
```

> En développement, omettez `--no-dev` pour inclure les outils de test et de débogage.

### 3. Installer les dépendances Node.js

```bash
npm install
```

### 4. Configurer le fichier d'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Ouvrez ensuite le fichier `.env` et renseignez toutes les variables nécessaires (voir section suivante).

### 5. Créer le lien symbolique de stockage

```bash
php artisan storage:link
```

Cette commande crée le lien `public/storage -> storage/app/public` nécessaire pour afficher les photos et fichiers audio uploadés.

### 6. Exécuter les migrations de base de données

```bash
php artisan migrate
```

### 7. Peupler la base de données (données initiales)

```bash
php artisan db:seed
```

> Le seeder crée les rôles par défaut, les paramètres initiaux et les éléments de navigation.

### 8. Compiler les assets frontend

```bash
# En production
npm run build

# En développement (avec hot reload)
npm run dev
```

### 9. Lancer le serveur (développement)

```bash
php artisan serve
```

L'application est accessible sur `http://localhost:8000`.

**En développement complet (avec queue et logs en temps réel) :**

```bash
composer run dev
```

Cette commande lance simultanément le serveur PHP, le listener de queue, le tail des logs et Vite.

## Configuration - Variables d'environnement

### Application

```ini
APP_NAME=Mnemo
APP_ENV=production          # local | production
APP_KEY=                    # généré par php artisan key:generate
APP_DEBUG=false             # true uniquement en développement
APP_URL=https://votre-domaine.fr

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
```

### Base de données

**SQLite (développement) :**

```ini
DB_CONNECTION=sqlite
# Le fichier database/database.sqlite sera créé automatiquement
```

**MySQL / MariaDB (production) :**

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mnemo
DB_USERNAME=mnemo_user
DB_PASSWORD=mot_de_passe_securise
```

### Sessions et cache

```ini
SESSION_DRIVER=database
SESSION_LIFETIME=120        # Durée de session en minutes
SESSION_ENCRYPT=false

CACHE_STORE=database
```

### File d'attente (queue)

```ini
QUEUE_CONNECTION=database
```

> La queue est utilisée pour les exports PDF/Excel et les envois d'e-mails volumineux. Démarrez un worker avec `php artisan queue:work`.

### Stockage des fichiers

```ini
FILESYSTEM_DISK=local
```

### Journalisation (logs)

```ini
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error             # debug | info | warning | error (production : error)
```

### Messagerie e-mail

```ini
MAIL_MAILER=smtp            # log (désactive l'envoi) | smtp | sendmail
MAIL_HOST=smtp.votre-fournisseur.fr
MAIL_PORT=587
MAIL_USERNAME=no-reply@votre-domaine.fr
MAIL_PASSWORD=mot_de_passe_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@votre-domaine.fr"
MAIL_FROM_NAME="${APP_NAME}"
```

> En développement, utilisez `MAIL_MAILER=log` pour enregistrer les e-mails dans les logs sans les envoyer.

### TinyMCE (éditeur de texte riche)

```ini
TINYMCE_API_KEY=votre_cle_api_tinymce
```

> Obtenez une clé gratuite sur https://www.tiny.cloud/. Sans clé, TinyMCE fonctionne mais affiche un avertissement.

### Authentification à deux facteurs (2FA)

Le 2FA est intégré via `pragmarx/google2fa-laravel` et ne nécessite pas de configuration particulière dans `.env`. Il peut être activé / forcé depuis le panel d'administration.

## Installation via l'assistant web

Mnemo propose un assistant d'installation accessible à l'URL `/install` lors du premier démarrage. Cet assistant guide l'administrateur à travers les étapes suivantes :

1. **Vérification des prérequis** (`/install/prerequisites`) : PHP version, extensions, permissions des dossiers
2. **Configuration de la base de données** (`/install/database`) : test de connexion et exécution des migrations
3. **Création du compte administrateur** (`/install/admin`) : nom, e-mail, mot de passe
4. **Confirmation** (`/install/complete`) : l'installation est terminée

> L'assistant est désactivé automatiquement après la première installation. Pour relancer l'assistant, supprimez la table `installation` de la base de données.

## Permissions des dossiers

Les dossiers suivants doivent être accessibles en écriture par le serveur web :

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Configuration du serveur web (production)

### Nginx

```nginx
server {
    listen 80;
    server_name votre-domaine.fr;
    root /var/www/mnemo/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Apache

Assurez-vous que `mod_rewrite` est activé et que le fichier `public/.htaccess` est pris en compte :

```apache
<VirtualHost *:80>
    ServerName votre-domaine.fr
    DocumentRoot /var/www/mnemo/public

    <Directory /var/www/mnemo/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Worker de queue (production)

En production, il est recommandé de faire tourner le worker de queue en arrière-plan via un processus superviseur (Supervisor, systemd...) :

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Exemple de configuration Supervisor

```ini
[program:mnemo-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mnemo/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/mnemo/storage/logs/worker.log
stopwaitsecs=3600
```

## Mise à jour rapide (après installation)

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
