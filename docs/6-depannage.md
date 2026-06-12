# Mnemo — Dépannage

## Erreurs fréquentes et solutions

---

### Erreur 500 — "Internal Server Error"

**Symptôme :** Page blanche ou message "500 | Server Error".

**Causes et solutions :**

1. **Fichier `.env` manquant ou mal configuré**
   ```bash
   # Vérifier que le fichier existe
   ls -la /var/www/mnemo/.env

   # Le créer depuis l'exemple si absent
   cp .env.example .env
   php artisan key:generate
   ```

2. **Permissions de dossiers incorrectes**
   ```bash
   chmod -R 775 /var/www/mnemo/storage
   chmod -R 775 /var/www/mnemo/bootstrap/cache
   chown -R www-data:www-data /var/www/mnemo/storage /var/www/mnemo/bootstrap/cache
   ```

3. **Cache corrompu**
   ```bash
   php artisan optimize:clear
   ```

4. **Consulter le log Laravel pour le détail de l'erreur**
   ```bash
   tail -n 50 /var/www/mnemo/storage/logs/laravel.log
   ```

---

### Erreur "No application encryption key has been specified"

**Cause :** La clé d'application `APP_KEY` est absente ou vide dans le fichier `.env`.

**Solution :**
```bash
php artisan key:generate
```

---

### Migration échouée

**Symptôme :** `php artisan migrate` retourne une erreur.

**Cause 1 : Connexion à la base de données impossible**
```bash
# Vérifier les paramètres de connexion dans .env
DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Tester la connexion manuellement
php artisan tinker
DB::connection()->getPdo();
```

**Cause 2 : Migration en conflit (colonne/table déjà existante)**
```bash
# Vérifier le statut des migrations
php artisan migrate:status

# Si une migration est marquée comme non exécutée alors que la table/colonne existe
# → Marquer la migration comme exécutée sans la rejouer
php artisan migrate --pretend
```

**Cause 3 : SQLite — dossier `database/` absent ou non accessible**
```bash
mkdir -p /var/www/mnemo/database
touch /var/www/mnemo/database/database.sqlite
chmod 664 /var/www/mnemo/database/database.sqlite
```

---

### Upload de photo ou d'audio ne fonctionne pas

**Symptôme :** Le formulaire d'ajout d'item revient vide, ou la photo n'apparaît pas après soumission.

**Cause 1 : Lien symbolique `storage` absent**
```bash
php artisan storage:link

# Vérifier que le lien existe
ls -la /var/www/mnemo/public/storage
```

**Cause 2 : Permissions insuffisantes sur le dossier de stockage**
```bash
chmod -R 775 /var/www/mnemo/storage/app/public
chown -R www-data:www-data /var/www/mnemo/storage
```

**Cause 3 : Taille de fichier dépassant la limite PHP**

Vérifiez les directives PHP dans `php.ini` :
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20
```

Puis rechargez PHP-FPM :
```bash
systemctl reload php8.3-fpm
```

**Cause 4 : Extension GD ou Imagick manquante** (requise pour le traitement d'images)
```bash
php -m | grep -E "gd|imagick"

# Installer GD si absent
apt-get install php8.3-gd
systemctl reload php8.3-fpm
```

---

### Les photos ne s'affichent pas (images cassées)

**Symptôme :** Les images s'affichent comme des icônes brisées dans les modules ou les questions.

**Cause 1 : Lien symbolique absent**
```bash
php artisan storage:link
```

**Cause 2 : `APP_URL` incorrect dans `.env`**

L'URL dans `.env` doit correspondre exactement à l'URL de l'application (avec ou sans `https`, avec ou sans `www`).
```ini
APP_URL=https://mnemo.lycee-rascol.fr
```

Puis vider le cache de configuration :
```bash
php artisan config:clear
php artisan config:cache
```

**Cause 3 : Le fichier n'existe plus sur le disque**

La base de données référence un fichier qui a été supprimé manuellement.
```bash
# Vérifier si le fichier existe
ls /var/www/mnemo/storage/app/public/chemin/vers/le/fichier.jpg
```

---

### Cache des vues corrompu ("Blade compilation error")

**Symptôme :** Erreur de compilation Blade sur une page qui fonctionnait avant une mise à jour.

**Solution :**
```bash
php artisan view:clear
```

Si le problème persiste :
```bash
rm -rf /var/www/mnemo/storage/framework/views/*
php artisan view:cache
```

---

### Session expirée en cours de quiz

**Symptôme :** Message "Aucun test en cours" ou "Aucune session Anki en cours" en plein milieu d'un quiz.

**Causes :**
- La durée de session (`SESSION_LIFETIME`) est trop courte
- Le driver de session n'est pas correctement configuré

**Solution :**

Dans `.env`, augmentez la durée de session :
```ini
SESSION_LIFETIME=240
```

Vérifiez que le driver de session est bien `database` et que la table de sessions existe :
```bash
php artisan migrate
php artisan config:cache
```

---

### Erreur "CSRF token mismatch"

**Symptôme :** Message "419 | Page Expired" lors de la soumission d'un formulaire.

**Causes :**
- Session expirée (utilisateur inactif trop longtemps)
- Cookie de session perdu (navigation privée, changement de domaine)
- `APP_URL` incorrect

**Solutions :**
1. Actualiser la page et resoumettre le formulaire
2. Vérifier et corriger `APP_URL` dans `.env`
3. Vider le cache de configuration : `php artisan config:cache`

---

### Mode 2FA — "Code invalide"

**Symptôme :** Le code TOTP est refusé malgré une saisie correcte.

**Causes :**
- Horloge du serveur désynchronisée (le TOTP est sensible à l'heure)
- Application d'authentification et serveur ont des heures différentes

**Solution :**
```bash
# Synchroniser l'horloge du serveur
timedatectl set-ntp true
systemctl restart systemd-timesyncd

# Vérifier l'heure actuelle
date
```

---

### Erreur "Queue job failed" (jobs de la file d'attente échoués)

**Symptôme :** Les exports PDF/Excel ou les e-mails ne partent pas.

**Diagnostic :**
```bash
# Voir les jobs échoués
php artisan queue:failed

# Détail d'un job échoué (remplacez {id} par l'identifiant du job)
php artisan queue:failed --id={id}
```

**Solutions :**
1. Relancer les jobs échoués :
   ```bash
   php artisan queue:retry all
   ```

2. Vérifier que le worker de queue est actif :
   ```bash
   # Démarrer un worker
   php artisan queue:work --sleep=3 --tries=3

   # Vérifier si un worker est en cours (systemd/supervisor)
   systemctl status mnemo-worker
   ```

3. Supprimer les jobs échoués et recommencer :
   ```bash
   php artisan queue:flush
   ```

---

### Problème d'envoi d'e-mails

**Symptôme :** Les e-mails de vérification, de résultats d'examen ou de notification ne sont pas reçus.

**Diagnostic :**
```bash
# Tester l'envoi depuis Tinker
php artisan tinker
Mail::raw('Test email depuis Mnemo', fn($m) => $m->to('test@example.fr')->subject('Test'));
```

**Solutions :**
1. Vérifier la configuration SMTP dans `.env` (ou via **Admin → Paramètres → Email**)
2. Utiliser le bouton **Envoyer un e-mail de test** dans le panel d'administration
3. Vérifier les logs Laravel pour les erreurs SMTP :
   ```bash
   grep -i "mail\|smtp" /var/www/mnemo/storage/logs/laravel.log
   ```
4. Vérifier que le port SMTP n'est pas bloqué par le pare-feu :
   ```bash
   telnet smtp.votre-fournisseur.fr 587
   ```

---

### Erreur "Class not found" après une mise à jour

**Symptôme :** Erreur PHP "Class 'App\Models\...' not found" ou similaire.

**Cause :** L'autoloader Composer n'a pas été régénéré après l'ajout de nouvelles classes.

**Solution :**
```bash
composer dump-autoload --optimize
```

---

### La bibliothèque publique est vide ou ne se met pas à jour

**Symptôme :** Des modules publics n'apparaissent pas dans la bibliothèque.

**Cause :** Cache d'application obsolète.

**Solution :**
```bash
php artisan cache:clear
```

---

## Commandes de diagnostic

### Vérification rapide de l'état de l'application

```bash
# Version de PHP
php --version

# Extensions PHP actives
php -m

# Statut des migrations
php artisan migrate:status

# Vérification de la configuration
php artisan config:show database
php artisan config:show mail

# Vérification des routes
php artisan route:list

# Informations sur l'application (mode, URL, version Laravel)
php artisan about
```

### Vérification des permissions

```bash
# Vérifier les permissions des dossiers critiques
ls -la /var/www/mnemo/storage/
ls -la /var/www/mnemo/storage/app/public/
ls -la /var/www/mnemo/bootstrap/cache/
ls -la /var/www/mnemo/public/storage  # Doit être un lien symbolique
```

### Analyse des logs

```bash
# Dernières erreurs
grep -i "ERROR\|CRITICAL\|ALERT\|EMERGENCY" /var/www/mnemo/storage/logs/laravel.log | tail -20

# Toutes les lignes de log du jour
grep $(date +%Y-%m-%d) /var/www/mnemo/storage/logs/laravel.log

# Taille du fichier de log
ls -lh /var/www/mnemo/storage/logs/laravel.log
```

### Vérification de la base de données

```bash
php artisan tinker

# Nombre d'utilisateurs
App\Models\User::count();

# Nombre de modules
App\Models\Module::count();

# Nombre de jobs en attente
DB::table('jobs')->count();

# Jobs échoués
DB::table('failed_jobs')->count();
```
