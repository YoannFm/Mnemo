# Mnemo - Présentation des fonctionnalités

## Vue d'ensemble

Mnemo est organisé autour de trois entités principales : les **modules** (ensembles de connaissances), les **items** (unités individuelles à mémoriser) et la **progression** (suivi par utilisateur et par item). Ces trois entités alimentent les différents modes d'entraînement et les outils de partage.

## Modules

Un module est un ensemble thématique d'items à mémoriser. Il appartient à un utilisateur (propriétaire) et peut être rendu public pour être partagé avec la communauté.

### Champs d'un module

| Champ | Type | Description |
|---|---|---|
| `title` | Texte | Nom du module (obligatoire) |
| `description` | Texte long | Description libre, supporte le HTML via TinyMCE |
| `is_public` | Booléen | Si activé, le module apparaît dans la bibliothèque publique |
| `allow_duplication` | Booléen | Autorise les autres utilisateurs à dupliquer le module |
| `field_name_fr` | Booléen | Active le champ "Nom (français)" pour les items |
| `field_name_alt` | Booléen | Active le champ "Nom alternatif" (ex. anglais, latin...) |
| `field_photo` | Booléen | Active le champ photo pour les items |
| `field_function` | Booléen | Active le champ description/fonction pour les items |
| `field_audio` | Booléen | Active le champ fichier audio pour les items |

### Actions disponibles sur un module

- **Créer** un module avec configuration des champs actifs
- **Modifier** le titre, la description et la configuration des champs
- **Supprimer** (mise en corbeille avec restauration possible)
- **Exporter** au format ZIP (contient les items et les fichiers médias)
- **Importer** depuis un fichier ZIP exporté précédemment
- **Dupliquer** un module public dans son espace personnel
- **Réinitialiser** sa progression sur un module
- **Signaler** un module public inapproprié
- **Noter** un module (système d'avis avec réactions et réponses)

## Items

Un item est l'unité atomique de connaissance à mémoriser au sein d'un module. Ses champs disponibles dépendent de la configuration du module parent.

### Champs d'un item

| Champ | Type | Description |
|---|---|---|
| `name_fr` | Texte | Nom principal (généralement en français) |
| `name_alt` | Texte | Nom alternatif (anglais, latin, abréviation, etc.) |
| `function_text` | Texte long | Description, rôle ou définition de l'item |
| `photo_path` | Fichier | Image représentant l'item (JPG, PNG, WebP) |
| `audio_path` | Fichier | Fichier audio associé (MP3, WAV, OGG) |

### Gestion des items

- **Ajout manuel** : formulaire de saisie item par item
- **Import CSV** : import en masse depuis un fichier tableur
- **Modification** : édition de tous les champs, remplacement des fichiers médias
- **Suppression** : suppression individuelle d'un item

### Format CSV d'import

Le fichier CSV doit comporter une ligne d'en-tête avec les colonnes suivantes (dans n'importe quel ordre) :

```
name_fr,name_alt,function_text,photo_path,audio_path
```

- Les colonnes non utilisées par le module peuvent être omises
- Les colonnes `photo_path` et `audio_path` sont ignorées lors de l'import CSV (les médias doivent être ajoutés manuellement après l'import)
- L'encodage attendu est **UTF-8**
- Le séparateur est la **virgule** (`,`)

## Les 3 modes de quiz

### Mode Anki (répétition espacée)

Le mode Anki est le coeur de l'application. Il propose des questions en continu avec un feedback immédiat après chaque réponse, et adapte la fréquence de révision de chaque item en fonction des performances de l'utilisateur (algorithme SM-2).

**Caractéristiques :**
- Questions infinies, pas de limite de temps ni de nombre
- Feedback immédiat après chaque réponse (bonne ou mauvaise)
- Suivi de la progression par item (`success_count`, `fail_count`, `streak`)
- Un item est considéré **maîtrisé** quand `streak >= 3` (3 bonnes réponses consécutives)
- Les items difficiles (beaucoup d'erreurs) apparaissent plus souvent
- Les items maîtrisés apparaissent moins souvent

**Modes de sélection des questions :**

| Mode | Description |
|---|---|
| Aléatoire | Tous les types de questions, items pondérés par la progression |
| Photo → Nom FR | Q1 uniquement |
| Photo → Nom alternatif | Q8 uniquement |
| Photo → Description | Q2 uniquement |
| Description → Photo | Q3 uniquement |
| Description → Nom FR | Q6 uniquement |
| Description → Nom alternatif | Q11 uniquement |
| Nom FR → Nom alternatif | Q4 uniquement |
| Nom FR → Photo | Q9 uniquement |
| Nom FR → Description | Q10 uniquement |
| Nom alternatif → Photo | Q5 uniquement |
| Nom alternatif → Nom FR | Q7 uniquement |
| Audio → Nom FR | Q13 uniquement |
| Audio → Nom alternatif | Q14 uniquement |
| Nom FR → Audio | Q15 uniquement |
| Nom alternatif → Audio | Q16 uniquement |

**Mode apprentissage :** quand un mode spécifique est sélectionné (autre que aléatoire), le mode apprentissage est activé. Chaque item est présenté au moins une fois ; lorsqu'il est répondu correctement, il est retiré du pool. La session se termine quand tous les items ont été répondus correctement.

**Mode révision :** révise uniquement les items qui ont été ratés au moins une fois et qui ne sont pas encore maîtrisés (`fail_count > 0` et `streak < 3`).

### Mode Test (QCM à questions fixes)

Le mode Test propose un questionnaire à choix multiples (4 options) avec un nombre de questions fixé par l'utilisateur avant le début de la session. Le score est affiché à la fin.

**Caractéristiques :**
- Nombre de questions paramétrable (de 1 à 100, limité au nombre d'items du module)
- 4 options de réponse (1 correcte + 3 distracteurs issus du même module)
- Pas de feedback immédiat - la correction complète est affichée à la fin
- Le score est enregistré en base de données (`scores`)
- La progression par item est également mise à jour (comme en mode Anki)

**Flux :**
1. Choisir le nombre de questions et le mode de sélection
2. Répondre à chaque question (QCM)
3. Consulter la correction complète et le score final (X/N, pourcentage)

### Mode Examen (QCM exhaustif)

Le mode Examen est similaire au mode Test mais porte sur **tous les items du module** sans exception. Il est conçu pour évaluer la maîtrise complète d'un module.

**Caractéristiques :**
- Toutes les questions sont posées (un item = au minimum une question)
- Score final avec pourcentage
- Peut être partagé via la fonctionnalité "Examens partagés"

## Examens partagés

La fonctionnalité "Examens partagés" permet à un utilisateur de créer une session d'examen basée sur un de ses modules et de la partager avec des participants via un lien unique ou un QR code.

### Côté créateur

- **Créer un examen partagé** depuis la page d'un module
- Définir le nombre de tentatives autorisées par participant
- **Partager** le lien ou le QR code avec les participants
- **Consulter les résultats** en temps réel
- **Exporter les résultats** en CSV, Excel ou PDF
- **Envoyer les résultats** par e-mail aux participants
- **Réinitialiser** les tentatives d'un participant
- **Supprimer** un examen partagé

### Côté participant

- Accéder à l'examen via le lien ou le QR code
- Passer l'examen (QCM, même moteur que le mode Examen)
- Consulter son score à la fin
- Recommencer si le créateur a autorisé plusieurs tentatives

## Bibliothèque publique

La bibliothèque (`/bibliotheque`) liste tous les modules marqués comme publics par leurs propriétaires. Elle permet de :

- **Rechercher** des modules par titre ou description
- **Prévisualiser** un module (liste des items) avant de l'ajouter
- **Dupliquer** un module public dans son espace personnel (si la duplication est autorisée)
- **Noter** et commenter un module (système d'avis avec notes)
- **Signaler** un module ou un avis inapproprié

## Suivi de la progression

La page de progression (`/progression`) offre une vue consolidée des statistiques d'apprentissage de l'utilisateur :

- Taux de maîtrise par module (nombre d'items maîtrisés / total)
- Historique des scores (mode Test)
- Items dus pour révision (dont `next_review` est dépassée)

## Système de rôles et permissions

Les rôles permettent de définir finement les droits de chaque utilisateur. Chaque rôle peut activer ou désactiver les permissions suivantes :

| Permission | Description |
|---|---|
| `can_train_own` | S'entraîner sur ses propres modules (Anki) |
| `can_train_public` | S'entraîner sur les modules publics (Anki) |
| `can_test_own` | Tester ses propres modules (mode Test) |
| `can_test_public` | Tester les modules publics (mode Test) |
| Création de modules | Créer de nouveaux modules |
| Publication | Rendre ses modules publics |

Les administrateurs (`is_admin = true`) contournent toutes les restrictions de rôle.

## Notifications

Le système de notifications informe les utilisateurs des événements importants :

- Notifications envoyées par les administrateurs (broadcast)
- Marquage comme lu (individuel ou en masse)

## Plugins

L'architecture plugin permet d'étendre les fonctionnalités sans modifier le coeur de l'application :

- **Import Quizlet** : importer des sets depuis Quizlet
- **Streak** : suivi de la régularité des sessions d'entraînement
- **Webhook** : déclencher des actions externes lors d'événements de la plateforme

Les plugins peuvent être activés, désactivés ou mis à jour depuis le panel d'administration.

## Personnalisation de l'interface

Chaque utilisateur peut personnaliser son expérience :

- **Couleur d'accentuation** : teinte principale de l'interface (couleur Bootstrap personnalisée)
- **Réinitialisation** de la couleur d'accentuation aux valeurs par défaut
- **Notifications e-mail** : activer ou désactiver les notifications par e-mail

## Communauté

Les modules publics disposent d'un espace d'échange :

- **Réactions emoji** sur les modules et les articles
- **Commentaires** et réponses aux avis
- **Signalements** pour signaler un contenu inapproprié aux administrateurs
