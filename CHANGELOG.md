# Changelog

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
- **Libellés de questions** : labels courts (1 mot) pour les 12 types — Identification, Traduction, Reconnaissance, Correspondance, Définition, etc.

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
