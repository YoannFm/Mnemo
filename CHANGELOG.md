# Changelog

## [Non publié] - 2026-06-09

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
- **MnemoCloud** : URL fixée en dur (`https://mnemo.novadev.ovh`), non modifiable via `.env`
- **Endpoint mises à jour** : correction de l'URL `/api/v1/updates/check`

### Corrections
- **Plugin Streak** : widget vide corrigé (suppression des `@push/@endpush` dans la vue)
- **Plugins** : empêcher l'installation d'un plugin déjà installé (côté serveur et interface)
- **Examens partagés** : balise `<form>` manquante sur la page de démarrage corrigée
- **Migration** : `nav_items` seed compatible avec le schéma initial (colonne `value` optionnelle)
- **Migration** : `SHOW INDEX` remplacé par `Schema::hasIndex()` pour compatibilité SQLite/CI
- **Tirets longs** : remplacement de `—` par `-` sur l'ensemble des vues

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
