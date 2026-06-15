# Changelog

## 2026-06-15

### Mode Anki - persistance et reprise de session

**Fonctionnalité principale**
- Nouvelle table `anki_sessions` (migration) : persiste le mode choisi et la liste d'items restants en mode apprentissage
- La session Anki survit à la fermeture du navigateur ou à l'expiration de session PHP
- Restauration automatique depuis la DB quand la session PHP est absente ou expirée
- Page setup : section "Session en pause" avec bouton "Reprendre", mode actif, avancement et date de dernière activité
- Page setup : statistiques de progression (A réviser / Maîtrisés / Total)
- Page setup : bouton "Remettre à zéro" directement accessible, connecté au reset existant (efface progression ET session sauvegardée)
- Mode aléatoire : les items maîtrisés ET non-dus sont exclus du pool (au lieu d'un simple poids faible)
- Quand tous les items sont maîtrisés et non-dus : redirection vers la page setup avec message approprié
- `quit()` sauvegarde l'état courant avant de vider la session PHP, redirige vers la page setup
- `ModuleController::resetProgress()` efface aussi l'`AnkiSession` associée
- `submit()` synchronise `learn_remaining` en DB à chaque bonne réponse en mode apprentissage
- Fin de mode apprentissage : suppression de l'`AnkiSession` DB pour repartir proprement

**Corrections post-déploiement**
- Correction 405 Method Not Allowed : formulaire de reset imbriqué dans le formulaire principal (HTML invalide), déplacé hors du formulaire
- Correction condition du bouton reset : `$dueCount < $totalCount` incorrecte (masquait le bouton même avec de la progression), remplacée par `$newCount < $totalCount`
- Correction barre de progression manquante après reprise : `session('learn_total', 0)` retourne `null` si la clé existe avec valeur `null` (le défaut PHP n'est pas utilisé dans ce cas), ajout d'un fallback `?? count($learn_remaining)`
- Correction sélection de mode accidentelle : le formulaire pré-sélectionne désormais le mode de la session sauvegardée, évitant un basculement non voulu vers le mode aléatoire (sans barre de progression)
- Correction barre de progression mode aléatoire bloquée à 0 : la barre affichait `mastered_count` (streak >= 3, nécessite 3 bonnes réponses consécutives) ; remplacée par `learned_count` (success_count > 0) qui progresse dès la première bonne réponse par item
- Unification de la définition "maîtrisé" : un item est maîtrisé dès le premier "Je sais" (success_count > 0), appliqué à la fois sur la barre de progression en session et sur le compteur "Maîtrisés" de la page setup

## 2026-06-12 (suite 2)

### Export des logs - structure ZIP ameliorée

- L'archive exportée contient désormais un dossier `logs/` structuré :
  - `logs/latest.csv` ou `logs/latest.json` : données d'activité
  - `logs/files/` : fichiers liés aux actions loggées (archives de mises à jour, sauvegardes)
- Les fichiers présents dans `storage/app/updates/` et `storage/app/backups/` sont automatiquement inclus dans `logs/files/`

---

## 2026-06-12 (suite)

### Audit du code - second passage

Réalisation d'un second audit. Découverte des problèmes suivants :

- 11 vues affichaient les notifications flash en double (le layout les affiche déjà)
- Nombreuses actions non loggées : Anki, Test, Exam, Group, 2FA, export/import module, reset progression, reset mdp, vérification email, toutes les actions admin Update
- La vue de détail d'un log (oeil) n'avait pas d'état avant/après sur les modifications de modules, items et utilisateurs
- Mode examen : l'option sélectionnée ne montrait pas de contour visible au clic
- Mode examen : demander 8 réponses avec moins de 8 items provoquait un comportement incorrect
- Sécurité : `item_id` dans Anki non vérifié contre le module courant
- Sécurité : valeurs `.env` non échappées avant écriture (risque d'injection)
- Diverses actions non sécurisées ou mal ordonnées (log après logout, index option non vérifié, etc.)
- Pas de moyen d'exporter les logs

### Corrections et améliorations

**Interface admin**
- Header horizontal : espace ajouté entre icône et texte sur Support/Documentation
- Header horizontal : bouton retour au site supprimé
- Header horizontal : bouton soleil/lune déplacé juste avant le menu utilisateur, sans encadré
- Ordre des boutons de mode inversé sur la page module : Anki en premier, puis Test

**Logs d'activité - actions ajoutées**
- Anki : `started_anki`, `completed_anki`, `started_anki_review`
- Test : `started_test`
- Examen : `started_exam`
- Groupes : `created_group`, `deleted_group`, `added_group_member`, `removed_group_member`
- Modules : `reset_progress`, `reported_module`, `exported_module`, `imported_module`
- Profil : `2fa_enabled`, `2fa_disabled`
- Auth : `password_reset`, `email_verified`
- Admin Update : `downloaded_update`, `installed_update`, `backup_database`, `backup_files`

**Logs d'activité - état avant/après**
- `updated_module` : capture l'état du module avant et après modification
- `updated_item` : capture l'état de l'item avant et après modification
- `admin_updated_user` : capture l'état de l'utilisateur avant et après modification

**Export des logs**
- Bouton "Exporter (ZIP)" sur la page admin des logs
- Modal avec sélecteur de format (CSV ou JSON) et champ mot de passe admin
- Vérification du mot de passe côté serveur avant tout export
- Téléchargement en archive `.zip`, pas de fichier temporaire laissé sur le serveur
- L'export lui-même est loggé (`exported_logs`)

**Corrections bugs**
- Examen : contour de sélection désormais visible au clic (JS manquant)
- Examen : nombre d'options plafonné automatiquement au nombre d'items disponibles
- Examen/Test : information affichée dans le formulaire sur le nombre d'items requis
- Double notifications flash supprimées dans 11 vues

---

## 2026-06-12

### Audit du code

Réalisation d'un audit complet du projet. Découverte des problèmes suivants :

- Logs d'activité : `created_at` absent du `$fillable` - tous les logs s'inséraient sans date et disparaissaient de l'interface admin
- `GuestExamController` : validation `max:3` bloquait les réponses 4-7 quand l'examen avait plus de 4 options
- `ExamController` : `allowedTypes = []` transmis à `generateQuestion` - le mode sélectionné était ignoré, n'importe quelle question pouvait sortir
- `ExamController` : rechargement de la page résultat créait un score `0/0` en base
- `ExamController` : modeMap sans les 4 modes audio (Q13-Q16) - sélection silencieuse d'un mode aléatoire
- `TestController` : mode aléatoire pouvait générer des questions avec photo/audio comme réponse, l'interface n'étant pas prévue pour ça
- `TestController` / `ExamController` : `Score::create` exécuté sans vérifier l'authentification - insert avec `user_id = null` possible
- `QuizGenerator` : flags `field_photo`, `field_audio`, `field_function` du module jamais vérifiés - types incompatibles toujours dans le pool
- `Score` : pas de casts, impossible de distinguer un score de test d'un score d'examen
- `Item` : pas de `SoftDeletes` contrairement à `Module` - progressions orphelines à la suppression d'un item
- Vue `exam/start` : pas de filtrage par champs actifs du module, modes audio absents

### Corrections apportées

- **Logs d'activité** : ajout de `created_at` dans `$fillable` de `ActivityLog` - les logs s'enregistrent à nouveau correctement ; ajout des événements `completed_test` et `completed_exam`
- **GuestExamController** : validation corrigée `max:3` - `max:7`
- **ExamController** : `$types` correctement transmis à `generateQuestion` comme `allowedTypes`
- **ExamController** : vérification que `$answers` n'est pas vide avant de purger la session et d'enregistrer le score
- **ExamController** : modeMap complété avec `audio_to_name_fr`, `audio_to_name_alt`, `name_fr_to_audio`, `name_alt_to_audio`
- **TestController** : exclusion de Q3/Q5/Q9/Q15/Q16 (photo/audio en réponse) du pool aléatoire
- **TestController** / **ExamController** : `Score::create` et `LogHelper::log` protégés par `Auth::check()`
- **QuizGenerator** : filtrage des types selon les flags `field_photo`, `field_audio`, `field_function` du module ; PHPDocs mis à jour (Q1-Q4 - Q1-Q16, index 0-2 - index 0-7)
- **Score** : ajout des casts `integer`, ajout du champ `mode` (`test` / `exam`) + migration
- **Item** : ajout de `SoftDeletes` + migration *(nécessite `php artisan migrate` sur le serveur)*
- **Vue exam/start** : options de mode filtrées par champs actifs du module, modes audio ajoutés

### Nouvelles fonctionnalités

- **Bouton dupliquer sur la page module** : le propriétaire peut désormais dupliquer un module directement depuis sa page de détail, y compris les modules privés. Les autres utilisateurs connectés voient également le bouton si la duplication est autorisée sur ce module.

---

## 2026-06-09 (suite 3)

### Plugins

- **Plugin Webhook** v1.0.0 : envoi automatique des résultats d'examen vers une URL externe (Zapier, Make, Google Sheets...) à la fin de chaque passage
- **Plugin Import Quizlet** v1.0.0 : import d'un set Quizlet par copier-coller (terme/définition) pour créer un module Mnemo en un clic

---

## 2026-06-09 (suite 2)

### Ajouts

- **Feature flags** : page admin `/admin/settings/features` pour activer/désactiver chaque fonctionnalité avec description détaillée
- **Raccourcis AZERTY** : touches `&` `é` `"` `'` en plus de `1` `2` `3` `4` dans tous les modes (Test, Anki, Examen)
- **Q12** : 12ème type de question ajouté (Description → Traduction), couvrant toutes les combinaisons possibles
- **Libellés de questions** : labels courts (1 mot) pour les 12 types : Identification, Traduction, Reconnaissance, Correspondance, Définition, etc.

### Modifications

- **Champ `name_en` renommé en `name_alt`** : le champ accepte désormais n'importe quelle langue (latin, chinois, etc.), migration incluse
- **Plugins** : vérification insensible à la casse + test du dossier physique pour empêcher la réinstallation d'un plugin déjà présent

---

## 2026-06-09 (suite)

### Ajouts

- **Raccourcis clavier Anki** : touches `1` `2` `3` `4` pour répondre, `Espace` pour passer à la question suivante
- **Révision rapide** : bouton sur la page module pour relancer Anki uniquement sur les items ratés non maîtrisés
- **Statistiques par item** : badge Maîtrisé / En cours / erreurs sur chaque carte de module
- **Profil enrichi** : 6 widgets de stats (maîtrisés, pratiqués, tests, score moyen, meilleure série, modules)
- **Impression résultats** : bouton Imprimer sur les pages résultat d'examen et résultats partagés, CSS adapté
- **Export PDF** : export des résultats d'examen partagé en fichier `.pdf` (tableau des notes)
- **QR Code** : affiché sur la page résultats d'examen partagé, zoom au clic
- **Classement anonymisé** : l'élève voit sa note /20 et son classement à la fin d'un examen partagé
- **Date limite par défaut** : paramètre admin pour pré-remplir la date d'expiration des examens
- **Groupes / Classes** : création de groupes, ajout et retrait de membres par e-mail, navigation dédiée `/groupes`
- **Graphiques admin** : dashboard avec 2 graphiques sur 30 jours (tests/jour, inscriptions/jour)

### Corrections

- **Mode Test** : sélection exclusive des réponses (reset visuel des autres options au clic)
- **Mode Test** : mise à jour de la progression par item (`fail_count`, `streak`) pour activer la révision rapide

---

## 2026-06-09

### Ajouts

- **Examens partagés** : génération de liens partageables pour faire passer un examen à des participants connectés
- **Examens partagés** : page `/mes-examens` listant tous les examens créés avec liens copiables
- **Examens partagés** : page résultats avec tableau des participants (score, note /20, %, date)
- **Examens partagés** : tentatives multiples configurables (+1 à +10 par participant)
- **Examens partagés** : réinitialisation d'une tentative individuelle
- **Examens partagés** : envoi des résultats par notification (individuel ou tous)
- **Examens partagés** : note /20 calculée automatiquement en plus du score brut
- **Examens partagés** : export CSV notes (Participant / Note /20)
- **Examens partagés** : export CSV détail (toutes les questions/réponses)
- **Examens partagés** : export Excel (.xlsx) avec 2 feuilles - "Notes" et "Réponses"
- **Mes résultats** : page élève `/mes-resultats` avec accès aux résultats uniquement après envoi par le créateur
- **Plugin Streak** : widget de jours consécutifs affiché sur le tableau de bord
- **Plugins** : marketplace en 2 onglets (Installés / Disponibles sur MnemoCloud)
- **Plugins** : détection et affichage des mises à jour disponibles (badge + bouton)
- **Mises à jour** : page `/admin/update` avec version actuelle vs dernière version MnemoCloud
- **Mises à jour** : sauvegarde des fichiers du site (ZIP) avant mise à jour
- **Mises à jour** : export de la base de données (SQL) avant mise à jour
- **Mises à jour** : les sauvegardes sont conservées sur le serveur et redistribuables via lien unique
- **Mises à jour** : toutes les actions loguées dans le journal d'activité avec lien de retéléchargement
- **Footer** : modal affichant le contenu du fichier LICENSE au clic sur "Licence MIT"
- **Paramètres** : `home_message` remplace le texte de bienvenue par défaut sur le tableau de bord
- **Paramètres** : `site_description` intégrée en meta SEO (`<meta name="description">`)
- **Paramètres** : `registration_conditions` affichée comme case à cocher avec support des liens markdown `[texte](url)`
- **Couleur accent** : personnalisable par utilisateur
- **Fichier LICENSE** : MIT License - Copyright (c) 2026 Yoann LE BORGNE

### Modifications

- **Footer** : texte simplifié en `© 2026 YoannFM · Licence MIT` sur toutes les pages (app + admin)
- **Footer admin** : ajout copyright + signature YoannFM
- **Examens partagés** : participants doivent être connectés (suppression de la saisie du prénom)
- **Examens partagés** : mode toujours aléatoire, sans sélecteur
- **Examens partagés** : boutons "Annuler" et "Commencer l'examen" retirés de la page Mode Examen
- **Endpoint mises à jour** : correction de l'URL `/api/v1/updates/check`

### Corrections

- **Plugin Streak** : widget vide corrigé (suppression des `@push/@endpush` dans la vue)
- **Plugins** : empêcher l'installation d'un plugin déjà installé (côté serveur et interface)
- **Examens partagés** : balise `<form>` manquante sur la page de démarrage corrigée
- **Migration** : `nav_items` seed compatible avec le schéma initial (colonne `value` optionnelle)
- **Migration** : `SHOW INDEX` remplacé par `Schema::hasIndex()` pour compatibilité SQLite/CI

---

## v2026.06.08 - 2026-06-05 au 2026-06-08

### Interface & Responsivité

- Menu mobile plein écran avec animation hamburger vers croix
- Sidebar admin en panneau glissant sur mobile/tablette
- Sous-menu utilisateur repliable dans le menu mobile
- Responsivité complète mobile/tablette sur toutes les pages

### Modules

- Système de notes 5 étoiles avec commentaires sur les modules
- Réponses imbriquées aux avis (replies)
- Signalement des avis et réponses avec modération admin
- Bouton loupe sur les images de question (zoom sans valider)
- Images carrées dans les réponses (grille 2x2)
- Export et import de modules au format ZIP
- Description et photo optionnelles à la création d'item
- Raccourci réinitialisation de progression sur les cartes de module
- Transfert de propriété d'un module entre utilisateurs

### Emojis & Articles

- Système d'emojis custom : upload admin + picker avec recherche et catégories
- Import de packs d'emojis via ZIP + manifest
- Section "récemment utilisés" dans le picker
- Articles avec réactions emoji et commentaires activables par article
- Signalement de commentaires avec motif et modération admin
- Modification et suppression de commentaires (historique conservé)

### Admin

- Actions complètes sur les modules depuis l'admin (voir, exporter, dupliquer, supprimer)
- Contournement admin pour les restrictions de propriété et confidentialité
- Export des données utilisateur (CSV individuel et ZIP complet)
- Import d'utilisateurs en masse via CSV
- Fusion des signalements modules et commentaires dans une seule page filtrée
- Statuts des signalements avec workflow (en attente / sanctionné / non sanctionné)
- Transcription des commentaires signalés dans le panel
- Historique des modifications et suppressions de commentaires
- Stats du tableau de bord et filtres avancés dans les logs
- Purge des logs de plus de 30 jours

### Sécurité & Authentification

- Correction de la boucle de redirection 2FA
- Confirmation du mot de passe requis pour désactiver le 2FA

### Navigation & Thèmes

- Thèmes dark/light avec génération automatique des variantes
- Correction des liens navbar (colonne `url` remplacée par `value`)
- Liens de navigation entre login, register et mot de passe oublié
- Couleurs du thème appliquées aux pages d'authentification (guest layout)

### Éditeur

- Remplacement du CDN TinyMCE par hébergement local (sans clé API)
- Support du mode sombre/clair dans l'éditeur TinyMCE
- Pages statiques : éditeur TinyMCE, description, restriction par rôle, route publique

### Corrections

- Fix Anki : stats de session globales, fix stats toujours à 0
- Fix questions avec champs vides
- Fix permission duplication modules
- Fix compteur commentaires (exclut les supprimés)
- Fix emoji trop court dans la base de données (10 -> 100 chars)

---

## v2026.06.04-1.0.0-1 - 2026-06-04

Première version stable de Mnemo, application web de mémorisation par répétition espacée développée au Lycée Rascol (Albi).

### Apprentissage

- Création de modules avec items (question/réponse, image optionnelle)
- Import en masse d'items via CSV
- Mode Anki avec algorithme SM-2 (répétition espacée adaptative)
- Mode Test avec score final et récapitulatif des erreurs
- Bibliothèque publique - partage et duplication de modules entre utilisateurs
- Suivi de progression et historique des scores

### Communauté

- Réactions emoji sur les articles (grille de 80 emojis)
- Commentaires avec réponses imbriquées
- Modification et suppression de ses propres commentaires (historique conservé)
- Signalement de commentaires et modules

### Panel d'administration

- Gestion des utilisateurs - CRUD, rôles, bannissements, import/export CSV
- Gestion des articles avec éditeur TinyMCE 6
- Thèmes dark/light entièrement personnalisables
- Signalements avec workflow - en attente / sanctionné / non sanctionné
- Historique des commentaires modifiés et supprimés
- Mutes temporaires ou définitifs avec notifications automatiques
- Logs d'activité avec purge des entrées de plus de 30 jours
- Pages statiques, redirections, navbar configurable

### Sécurité

- Authentification Laravel Breeze
- Double authentification (2FA) TOTP
- Fuseau horaire configurable depuis les paramètres généraux

### Stack technique

- PHP 8.3+ / Laravel 11 / Bootstrap 5.3 / Vite 8
- SQLite (dev) / MySQL (prod)
- TinyMCE 6 et Bootstrap Icons hébergés localement
