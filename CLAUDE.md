# Mnemo

Application web de flashcards pour apprendre et mémoriser du vocabulaire. Les utilisateurs créent des modules contenant des items avec plusieurs champs (nom français, traduction/nom alternatif, fonction, photo, audio) et révisent en mode Anki (répétition espacée SM-2), Test (QCM avec score) ou Examen (toutes les questions du module). Les modules peuvent être publics ou privés, partagés, dupliqués, et les examens peuvent être distribués à d'autres utilisateurs avec suivi des résultats.

## Stack technique

- **Back** : Laravel 13, PHP 8.5
- **Front** : Bootstrap 5.3, Blade, JS vanilla
- **BDD** : MySQL
- **Algo** : SM-2 (répétition espacée Anki)

## Architecture clé

- `app/Models/Module.php` - SoftDeletes, propriétaire (`owner_id`), public/privé
- `app/Models/Item.php` - SoftDeletes, appartient à un module
- `app/Models/ActivityLog.php` - logs d'activité, `$timestamps = false`, `created_at` dans `$fillable`
- `app/Models/Progress.php` - progression Anki par item/utilisateur, SM-2 (`easiness_factor`, `interval_days`, `next_review`), `isMastered()` = streak >= 3
- `app/Models/AnkiSession.php` - session Anki persistée par utilisateur/module (`mode`, `learn_remaining` JSON, `learn_total`), unique sur `(user_id, module_id)`
- `app/Services/QuizGenerator.php` - génère les questions Q1-Q16 (toutes combinaisons de champs)
- `app/Helpers/LogHelper.php` - `LogHelper::log($action, $targetType, $targetId, $data, $level, $oldValue, $newValue)`
- `app/Extensions/UpdateManager.php` - mises à jour de l'application, archives dans `storage/app/updates/`

## Mode Anki - fonctionnement

- **Progression** (`progress`) : persistée en DB par item/utilisateur (streak, SM-2, next_review). Jamais perdue.
- **Session** (`anki_sessions`) : mode choisi + liste `learn_remaining` (mode apprentissage). Survivent à la fermeture du navigateur.
- **Restauration** : si la session PHP est absente au chargement d'une question, `AnkiController::question()` restaure depuis `anki_sessions` en DB.
- **Pool aléatoire** : les items maîtrisés (streak >= 3) ET non-dus (`next_review` dans le futur) sont exclus. Les autres sont pondérés selon l'historique.
- **Mode apprentissage** : activé pour tout mode non-aléatoire. `learn_remaining` liste les items à maîtriser, synchro en DB à chaque bonne réponse.
- **Fin de session** : `quit()` sauvegarde l'état en DB, redirige vers la page setup (bouton "Reprendre" visible). `clearSession()` supprime l'`AnkiSession` quand la session est terminée.
- **Reset** : `ModuleController::resetProgress()` supprime les `Progress` ET l'`AnkiSession` du module.
- **Piège à éviter** : `session('key', $default)` retourne `null` si la clé existe avec valeur `null` (le défaut n'est utilisé que si la clé est ABSENTE). Toujours utiliser `?? $fallback` en plus du défaut de session pour les valeurs nullable en DB.

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

## Style

- Interdiction d'utiliser le tiret long (—), utiliser uniquement le tiret court (-). Si un tiret long est détecté dans un fichier du dépôt, le remplacer immédiatement.
- Interdiction d'inclure des liens claude.ai dans les fichiers du dépôt, les commits, les commentaires ou tout autre artefact. Si un lien claude.ai est détecté dans un fichier du dépôt, le supprimer immédiatement.

## Déploiement

L'utilisateur a accès SSH au serveur de production `mnemo.rascol.net` et peut exécuter des commandes directement.

Toujours terminer chaque réponse avec **la commande complète** de mise à jour adaptée au contexte. Ne jamais donner des commandes séparées à enchaîner manuellement - tout en un seul bloc.

- Si du code a été modifié et pushé (sans migration, sans assets) :
```bash
git fetch origin && git reset --hard origin/claude/lucid-maxwell-OTvnN
```
- Si une migration a été ajoutée :
```bash
git fetch origin && git reset --hard origin/claude/lucid-maxwell-OTvnN && php artisan migrate --force
```
- Si des assets ont changé :
```bash
git fetch origin && git reset --hard origin/claude/lucid-maxwell-OTvnN && npm run build
```
- Si migration ET assets :
```bash
git fetch origin && git reset --hard origin/claude/lucid-maxwell-OTvnN && php artisan migrate --force && npm run build
```
- Si seulement une discussion sans modification de code : ne pas donner de commande.

## Sécurité

- Ne jamais mentionner Claude dans le dépôt (commits, code, commentaires, PR)
- Ne jamais inclure de liens claude.ai dans les fichiers du dépôt

## Branche de développement

Toujours développer sur la branche `claude/lucid-maxwell-OTvnN`.

## Git & déploiement

**Push vers GitHub :**
```bash
git remote set-url origin https://YoannFM-rascol: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@github.com/YoannFm/Mnemo.git && git push -u origin claude/lucid-maxwell-OTvnN 2>&1; git remote set-url origin http://local_proxy@127.0.0.1:37379/git/YoannFM-rascol/Mnemo
```

**Repo GitHub :** `YoannFm/Mnemo` (attention : le remote MCP est `YoannFM-rascol/Mnemo`, mais le vrai repo GitHub s'appelle `YoannFm/Mnemo`)

**Remote serveur :** `http://local_proxy@127.0.0.1:37379/git/YoannFM-rascol/Mnemo`

Le serveur pull depuis `YoannFm/Mnemo` - toujours pousser sur cette branche avant de demander un `git reset --hard` côté serveur.

## Changelog

Mettre à jour `CHANGELOG.md` après chaque modification de code, avant ou avec le commit. Format : section datée (`## YYYY-MM-DD`) avec description claire des changements. Ne pas créer une entrée pour les commits qui ne modifient que le CHANGELOG lui-même.
