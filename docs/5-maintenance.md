# Mnemo — Maintenance

## Sauvegardes

### Sauvegarde de la base de données

Il est recommandé d'effectuer des sauvegardes régulières (quotidiennes en production) de la base de données.

**MySQL / MariaDB :**

```bash
# Sauvegarde complète
mysqldump -u mnemo_user -p mnemo > /backups/mnemo_$(date +%Y%m%d_%H%M%S).sql

# Sauvegarde compressée
mysqldump -u mnemo_user -p mnemo | gzip > /backups/mnemo_$(date +%Y%m%d_%H%M%S).sql.gz
```

**SQLite (développement) :**

```bash
cp /var/www/mnemo/database/database.sqlite /backups/mnemo_$(date +%Y%m%d_%H%M%S).sqlite
```

**Restauration MySQL :**

```bash
mysql -u mnemo_user -p mnemo < /backups/mnemo_20260612_120000.sql
```

### Sauvegarde des fichiers (médias uploadés)

Les photos et fichiers audio sont stockés dans `storage/app/public/`. Ce dossier doit être inclus dans les sauvegardes.

```bash
# Archiver les fichiers media
tar -czf /backups/mnemo_storage_$(date +%Y%m%d).tar.gz /var/www/mnemo/storage/app/public/

# Restaurer
tar -xzf /backups/mnemo_storage_20260612.tar.gz -C /
```

### Sauvegarde complète (fichiers + base de données)

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/backups/mnemo

mkdir -p $BACKUP_DIR

# Base de données
mysqldump -u mnemo_user -p mnemo | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Fichiers media
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/mnemo/storage/app/public/

# Nettoyer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Sauvegarde terminée : $DATE"
```

> Planifiez ce script via cron : `0 2 * * * /scripts/backup_mnemo.sh` (toutes les nuits à 2h).

### Sauvegarde via le panel d'administration

Le panel d'administration propose également des outils de sauvegarde intégrés (voir section **Mises à jour** dans le guide administrateur) :

- **Admin → Mise à jour → Sauvegarder les fichiers**
- **Admin → Mise à jour → Sauvegarder la base de données**

---

## Mises à jour de l'application

### Via le panel d'administration (recommandé)

1. Connectez-vous en tant qu'administrateur
2. Accédez à **Admin → Mise à jour**
3. Cliquez sur **Vérifier les mises à jour**
4. Si une mise à jour est disponible, cliquez sur **Télécharger**
5. **Sauvegardez** les fichiers et la base de données (boutons dédiés)
6. Cliquez sur **Installer**
7. Attendez la confirmation de succès

### Via la ligne de commande (SSH)

```bash
cd /var/www/mnemo

# 1. Passer en mode maintenance
php artisan down --message="Mise à jour en cours, retour dans quelques minutes."

# 2. Récupérer les dernières modifications
git pull origin main

# 3. Mettre à jour les dépendances PHP
composer install --no-dev --optimize-autoloader

# 4. Mettre à jour les assets frontend
npm ci && npm run build

# 5. Exécuter les nouvelles migrations
php artisan migrate --force

# 6. Vider et régénérer les caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Redémarrer le worker de queue
php artisan queue:restart

# 8. Désactiver le mode maintenance
php artisan up
```

---

## Commandes Artisan utiles

### Gestion de la base de données

```bash
# Exécuter les migrations en attente
php artisan migrate

# Voir le statut des migrations
php artisan migrate:status

# Annuler la dernière migration (développement uniquement)
php artisan migrate:rollback

# Réinitialiser complètement la base de données (DANGER — développement uniquement)
php artisan migrate:fresh --seed
```

### Gestion des caches

```bash
# Vider tous les caches (config, routes, vues, application)
php artisan optimize:clear

# Vider uniquement le cache de configuration
php artisan config:clear

# Vider uniquement le cache des routes
php artisan route:clear

# Vider uniquement le cache des vues
php artisan view:clear

# Vider le cache de l'application
php artisan cache:clear

# Régénérer les caches (production)
php artisan optimize
```

### Gestion de la queue

```bash
# Démarrer un worker de queue (traitement en arrière-plan)
php artisan queue:work

# Démarrer avec des options avancées
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

# Traiter un seul job et s'arrêter
php artisan queue:work --once

# Écouter en mode développement (recharge à chaque modification)
php artisan queue:listen

# Voir les jobs en attente
php artisan queue:monitor

# Vider la queue (annuler tous les jobs en attente)
php artisan queue:clear

# Relancer les jobs échoués
php artisan queue:retry all

# Supprimer les jobs échoués
php artisan queue:flush

# Signaler au worker de redémarrer après le job en cours
php artisan queue:restart
```

### Gestion du stockage

```bash
# Créer le lien symbolique public/storage → storage/app/public
php artisan storage:link

# Lister les fichiers de stockage (tinker)
php artisan tinker
# Storage::files('public/items/photos');
```

### Gestion des sessions

```bash
# Supprimer les sessions expirées de la base de données
php artisan session:gc
```

### Maintenance

```bash
# Activer le mode maintenance
php artisan down

# Activer avec message personnalisé et délai de réponse
php artisan down --message="Maintenance planifiée" --retry=60

# Désactiver le mode maintenance
php artisan up
```

### Génération de clé

```bash
# Générer une nouvelle clé d'application (à faire une seule fois à l'installation)
php artisan key:generate
```

### Logs en temps réel (développement)

```bash
php artisan pail
```

---

## Purge des logs d'activité

Les logs d'activité peuvent s'accumuler rapidement. Il est recommandé de les purger régulièrement.

### Via le panel d'administration

**Admin → Logs → Purger** : supprime toutes les entrées de logs de plus de 30 jours.

### Via la ligne de commande

```bash
# Supprimer les logs de plus de 30 jours directement en base (MySQL)
mysql -u mnemo_user -p mnemo -e "DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);"
```

### Automatisation via cron

Ajoutez ces tâches à la crontab du serveur pour automatiser la maintenance :

```bash
# Ouvrir la crontab
crontab -e
```

```cron
# Purger les logs d'activité anciens (chaque dimanche à 3h)
0 3 * * 0 cd /var/www/mnemo && php artisan logs:purge

# Sauvegarde quotidienne à 2h du matin
0 2 * * * /scripts/backup_mnemo.sh

# Planificateur de tâches Laravel (obligatoire si le scheduler est utilisé)
* * * * * cd /var/www/mnemo && php artisan schedule:run >> /dev/null 2>&1
```

---

## Gestion du stockage des médias

### Vérifier l'espace utilisé

```bash
# Taille du dossier de stockage
du -sh /var/www/mnemo/storage/app/public/

# Détail par sous-dossier
du -sh /var/www/mnemo/storage/app/public/*/
```

### Nettoyer les fichiers orphelins

Des fichiers peuvent rester dans le stockage si des items ont été supprimés sans nettoyage des médias. Pour identifier et supprimer ces fichiers orphelins :

```bash
php artisan tinker
```

```php
// Lister tous les photo_path et audio_path enregistrés en base
$paths = \App\Models\Item::whereNotNull('photo_path')->pluck('photo_path')
    ->merge(\App\Models\Item::whereNotNull('audio_path')->pluck('audio_path'))
    ->unique()
    ->toArray();

// Lister tous les fichiers sur le disque
$files = \Storage::allFiles('public');

// Trouver les fichiers orphelins
$orphans = array_filter($files, fn($f) => !in_array($f, array_map(fn($p) => 'public/' . $p, $paths)));

// Supprimer les orphelins (vérifiez avant de supprimer !)
foreach ($orphans as $orphan) {
    \Storage::delete($orphan);
    echo "Supprimé : $orphan\n";
}
```

---

## Surveillance de l'application (monitoring)

### Vérification des logs d'erreur Laravel

```bash
# Afficher les dernières lignes du log
tail -n 100 /var/www/mnemo/storage/logs/laravel.log

# Suivre le log en temps réel
tail -f /var/www/mnemo/storage/logs/laravel.log

# Rechercher les erreurs
grep -i "error\|exception\|critical" /var/www/mnemo/storage/logs/laravel.log
```

### Vérification de la queue

```bash
# Voir les jobs échoués
php artisan queue:failed

# Détails d'un job échoué
php artisan queue:failed --id=1
```

### Vérification de l'état de la base de données

```bash
php artisan migrate:status
```
