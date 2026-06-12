# Mnemo - Guide utilisateur

## Inscription et connexion

### Créer un compte

1. Accédez à la page d'accueil de l'application
2. Cliquez sur **S'inscrire** (lien dans la page de connexion)
3. Renseignez votre nom, votre adresse e-mail et un mot de passe sécurisé
4. Validez le formulaire - un e-mail de confirmation peut être envoyé selon la configuration de l'administrateur

### Se connecter

1. Saisissez votre e-mail et votre mot de passe sur la page de connexion
2. Si l'authentification à deux facteurs (2FA) est activée sur votre compte, saisissez ensuite le code généré par votre application d'authentification (Google Authenticator, Authy...)

### Authentification à deux facteurs (2FA)

Pour renforcer la sécurité de votre compte :

1. Accédez à **Profil - Sécurité (2FA)**
2. Cliquez sur **Activer la 2FA**
3. Scannez le QR code avec votre application d'authentification
4. Saisissez le code à 6 chiffres pour confirmer l'activation
5. Conservez précieusement les codes de récupération affichés

Pour désactiver la 2FA : **Profil - Sécurité (2FA) - Désactiver**.

## Gestion du profil

### Modifier son profil

**Profil - Informations** permet de modifier :
- Nom d'affichage
- Adresse e-mail
- Mot de passe

### Personnaliser la couleur d'accentuation

**Profil - Apparence** :
- Choisissez une couleur personnalisée pour teinter l'interface
- Cliquez sur **Réinitialiser** pour revenir à la couleur par défaut du thème actif

### Notifications e-mail

**Profil - Notifications** : activez ou désactivez les notifications par e-mail.

### Supprimer son compte

**Profil - Zone de danger - Supprimer le compte** : action irréversible, supprime toutes vos données (modules, items, progression, scores).

## Créer un module

1. Dans le tableau de bord, cliquez sur **Nouveau module** ou accédez à **Mes modules - Créer**
2. Renseignez les informations du module :
   - **Titre** (obligatoire) : nom du module
   - **Description** : texte de présentation du module (éditeur riche)
   - **Module public** : cochez pour rendre le module visible dans la bibliothèque publique
   - **Autoriser la duplication** : cochez pour permettre aux autres utilisateurs de copier ce module
3. **Configurer les champs actifs** : sélectionnez les champs qui seront disponibles pour les items de ce module

| Champ | Usage |
|---|---|
| Nom (FR) | Appellation principale en français |
| Nom alternatif | Appellation secondaire (anglais, latin, abréviation...) |
| Photo | Image illustrant l'item |
| Description/Fonction | Texte décrivant le rôle ou la définition |
| Audio | Fichier audio (prononciation, mélodie...) |

4. Cliquez sur **Créer le module**

> Les champs que vous n'activez pas n'apparaîtront pas dans les formulaires d'item et ne seront pas utilisés pour générer des questions.

## Ajouter des items à un module

### Ajout manuel

1. Ouvrez votre module - cliquez sur **Ajouter un item**
2. Renseignez les champs disponibles (selon la configuration du module)
3. Pour les champs **Photo** et **Audio** : cliquez sur la zone d'upload pour sélectionner un fichier depuis votre ordinateur
4. Cliquez sur **Enregistrer**

**Formats acceptés :**
- Photo : JPG, PNG, WebP (taille recommandée : moins de 2 Mo)
- Audio : MP3, WAV, OGG (taille recommandée : moins de 5 Mo)

### Import en masse depuis un fichier CSV

1. Ouvrez votre module - cliquez sur **Importer des items (CSV)**
2. Téléchargez le modèle CSV proposé
3. Remplissez le fichier avec vos données (une ligne = un item)
4. Importez le fichier complété

**Structure du fichier CSV :**

```csv
name_fr,name_alt,function_text
Biceps brachial,Biceps brachii,"Muscle fléchisseur du coude, supinateur de l'avant-bras"
Triceps brachial,Triceps brachii,"Muscle extenseur du coude"
```

> Les colonnes `photo_path` et `audio_path` sont ignorées lors de l'import CSV. Ajoutez les médias manuellement après l'import.

## S'entraîner - Mode Anki

Le mode Anki est le mode d'entraînement principal. Il est adapté à l'apprentissage quotidien et à la révision progressive.

### Démarrer une session Anki

1. Ouvrez un module - cliquez sur **Anki**
2. Choisissez le **mode de question** :
   - **Aléatoire** : tous les types de questions, progression SM-2
   - Un mode spécifique (ex. "Photo - Nom") pour cibler un sens d'apprentissage particulier
3. Cliquez sur **Commencer**

### Pendant la session

- La question s'affiche (image, texte ou audio selon le type)
- Choisissez parmi les 4 options proposées
- La correction apparaît immédiatement avec la bonne réponse
- Utilisez le bouton **Suivant** (ou appuyez sur `Espace` / `Entrée`) pour passer à la question suivante
- Les touches `1`, `2`, `3`, `4` permettent de sélectionner directement une option

### Raccourcis clavier - Mode Anki

| Touche | Action |
|---|---|
| `1` `2` `3` `4` | Sélectionner l'option correspondante |
| `Espace` | Valider la réponse / Passer à la suivante |
| `Entrée` | Valider la réponse / Passer à la suivante |

### Indicateurs de session

- **Correct** : nombre de bonnes réponses depuis le début de la session
- **Raté** : nombre de mauvaises réponses
- **Série** (streak) : nombre de bonnes réponses consécutives en cours

### Mode apprentissage

Quand un mode spécifique est sélectionné, chaque item doit être répondu correctement au moins une fois. La barre de progression indique le nombre d'items restants. La session se termine quand tous les items ont été correctement répondus.

### Mode révision

Accessible depuis la page d'un module (bouton **Réviser**). Présente uniquement les items ayant au moins une erreur et non encore maîtrisés, dans un mode apprentissage.

## S'évaluer - Mode Test

Le mode Test propose un QCM à nombre de questions fixe, idéal pour évaluer ses connaissances à un instant T.

### Démarrer un test

1. Ouvrez un module - cliquez sur **Test**
2. Sélectionnez le **nombre de questions** souhaité (1 à 100, limité au nombre d'items du module)
3. Choisissez le **mode de question** (aléatoire ou spécifique)
4. Cliquez sur **Commencer le test**

### Pendant le test

- Les questions défilent l'une après l'autre
- Cliquez sur l'une des 4 réponses proposées ou utilisez les touches `1` à `4`
- Aucun feedback immédiat - la correction est affichée uniquement à la fin

### Raccourcis clavier - Mode Test

| Touche | Action |
|---|---|
| `1` `2` `3` `4` | Sélectionner et valider directement l'option |

### Résultats du test

À la fin du test :
- Score (X / N questions, pourcentage)
- Correction complète : chaque question avec la réponse donnée et la bonne réponse
- Le score est automatiquement enregistré dans l'historique

## S'évaluer - Mode Examen

Le mode Examen est identique au mode Test, mais porte sur **tous les items du module** sans exception. Il est conçu pour valider la maîtrise complète d'un module.

1. Ouvrez un module - cliquez sur **Examen**
2. Confirmez le démarrage
3. Répondez à toutes les questions
4. Consultez le score final et la correction

## Participer à un examen partagé

Un utilisateur peut partager un examen via un lien ou un QR code. Pour y participer :

1. Accédez au lien fourni (format `/e/{uuid}`)
2. Connectez-vous si ce n'est pas déjà fait
3. Cliquez sur **Commencer l'examen**
4. Répondez à toutes les questions
5. Consultez votre score à la fin

> Si le créateur a autorisé plusieurs tentatives, un bouton **Recommencer** apparaît sur la page de résultat.

### Consulter ses résultats d'examens partagés

Accédez à **Mes résultats** (`/mes-resultats`) pour consulter l'historique de tous vos examens partagés passés.

## Créer un examen partagé

1. Ouvrez un de vos modules (ou un module public) - cliquez sur **Partager comme examen**
2. Confirmez la création
3. Accédez à **Mes examens** (`/mes-examens`) pour gérer vos examens partagés
4. Copiez le **lien** ou téléchargez le **QR code** pour le distribuer aux participants

### Gérer un examen partagé

Depuis **Mes examens - [examen] - Voir les résultats** :

- Consultez les résultats de chaque participant en temps réel
- **Envoyer les résultats** par e-mail à un participant ou à tous
- **Exporter** les notes en CSV, Excel ou PDF
- **Réinitialiser** la tentative d'un participant (pour lui permettre de recommencer)
- **Ajouter une tentative** supplémentaire à un participant spécifique

## Bibliothèque publique

La bibliothèque (`/bibliotheque`) donne accès à tous les modules partagés par la communauté.

1. Accédez à **Bibliothèque** depuis le menu de navigation
2. Recherchez un module par titre ou description
3. Cliquez sur un module pour le **prévisualiser** (liste des items)
4. Cliquez sur **Dupliquer** pour copier le module dans votre espace personnel
5. Cliquez sur **S'entraîner** pour utiliser directement le module sans le dupliquer

### Noter un module

Sur la page d'un module public, vous pouvez :
- Attribuer une note (étoiles)
- Laisser un commentaire
- Réagir aux avis existants

## Suivre sa progression

La page **Progression** (`/progression`) affiche :

- La liste de tous les modules sur lesquels vous vous êtes entraîné
- Pour chaque module : nombre d'items maîtrisés, nombre d'items à réviser
- L'historique des scores (sessions de test et d'examen)

Un item est considéré **maîtrisé** lorsqu'il a été répondu correctement 3 fois de suite (streak >= 3).

## Importer / Exporter un module

### Exporter un module

1. Ouvrez votre module - cliquez sur **Exporter (ZIP)**
2. Un fichier ZIP est téléchargé, contenant :
   - Les données des items au format JSON
   - Les fichiers photos et audio associés

### Importer un module ZIP

1. Accédez à **Mes modules - Importer un module**
2. Sélectionnez le fichier ZIP exporté depuis Mnemo
3. Cliquez sur **Importer**
4. Le module est créé dans votre espace avec tous ses items et ses fichiers médias

## Corbeille des modules

Les modules supprimés ne sont pas immédiatement effacés, mais placés en corbeille.

1. Accédez à **Mes modules - Corbeille**
2. **Restaurer** un module supprimé par erreur, ou
3. **Supprimer définitivement** pour effacer le module et tous ses items de manière irréversible
