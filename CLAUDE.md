# Mnemo

Application web de flashcards pour apprendre et mémoriser du vocabulaire. Les utilisateurs créent des modules contenant des items avec plusieurs champs (nom français, traduction/nom alternatif, fonction, photo, audio) et révisent en mode Anki (répétition espacée SM-2), Test (QCM avec score) ou Examen (toutes les questions du module). Les modules peuvent être publics ou privés, partagés, dupliqués, et les examens peuvent être distribués à d'autres utilisateurs avec suivi des résultats.

## Stack technique

- **Back** : Laravel 13, PHP 8.5
- **Front** : Bootstrap 5.3, Blade, JS vanilla
- **BDD** : MySQL
- **Algo** : SM-2 (répétition espacée Anki)

## Architecture clé

- `app/Models/Module.php` — SoftDeletes, propriétaire (`owner_id`), public/privé
- `app/Models/Item.php` — SoftDeletes, appartient à un module
- `app/Models/ActivityLog.php` — logs d'activité, `$timestamps = false`, `created_at` dans `$fillable`
- `app/Services/QuizGenerator.php` — génère les questions Q1–Q16 (toutes combinaisons de champs)
- `app/Helpers/LogHelper.php` — `LogHelper::log($action, $targetType, $targetId, $data, $level, $oldValue, $newValue)`
- `app/Extensions/UpdateManager.php` — mises à jour de l'application, archives dans `storage/app/updates/`

## Fonctionnalités principales

- **Modules** : créer, modifier, dupliquer, exporter/importer, signaler, partager
- **Items** : nom FR, nom alternatif (traduction), fonction, photo, audio
- **Modes** : Anki (SM-2), Test (QCM, nombre de questions choisi), Examen (tout le module, 16 types de questions)
- **Examens partagés** : lien distribué, suivi des tentatives et résultats
- **Rôles** : permissions granulaires (can_test_own, can_test_public, etc.)
- **Admin** : utilisateurs, pages, articles, redirections, thèmes, emojis, navbar, plugins, mises à jour, sauvegardes
- **Logs** : toutes les actions tracées avec état avant/après (`old_value`/`new_value`), export ZIP (CSV ou JSON + fichiers liés)

## Conventions

- Mettre à jour `CHANGELOG.md` à chaque commit
- Logs : toujours appeler `LogHelper::log()` pour chaque action significative
- Les scores sont enregistrés dans `Score` avec le champ `mode` ('test' ou 'exam')
- Les questions de type réponse-média (Q3, Q5, Q9, Q15, Q16) sont exclues du mode aléatoire

---

# Instructions

## Déploiement

Toujours terminer chaque réponse avec la commande de mise à jour du serveur dans un bloc de code :

```bash
git fetch origin && git reset --hard origin/claude/lucid-maxwell-OTvnN
```

## Sécurité

- Ne jamais mentionner Claude dans le dépôt (commits, code, commentaires, PR)
- Ne jamais inclure de liens Claude dans les fichiers du dépôt

## Branche de développement

Toujours développer sur la branche `claude/lucid-maxwell-OTvnN`.

## Git & déploiement

**Push vers GitHub :**
```bash
git remote set-url origin https://YoannFM-rascol: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@github.com/YoannFm/Mnemo.git && git push -u origin claude/lucid-maxwell-OTvnN 2>&1; git remote set-url origin http://local_proxy@127.0.0.1:37379/git/YoannFM-rascol/Mnemo
```

**Repo GitHub :** `YoannFm/Mnemo` (attention : le remote MCP est `YoannFM-rascol/Mnemo`, mais le vrai repo GitHub s'appelle `YoannFm/Mnemo`)

**Remote serveur :** `http://local_proxy@127.0.0.1:37379/git/YoannFM-rascol/Mnemo`

Le serveur pull depuis `YoannFm/Mnemo` — toujours pousser sur cette branche avant de demander un `git reset --hard` côté serveur.

## Changelog

Mettre à jour `CHANGELOG.md` à chaque commit, dans un commit séparé ou groupé. Format : section datée avec description des changements.
