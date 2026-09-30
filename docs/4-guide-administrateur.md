# Mnemo - Guide administrateur

## Accès au panel d'administration

Le panel d'administration est accessible à l'URL `/admin`. L'accès est réservé aux utilisateurs dont l'attribut `is_admin` est `true` en base de données.

> Lors de la première installation via l'assistant (`/install`), le compte créé reçoit automatiquement les droits d'administration.

## Tableau de bord

Le tableau de bord (`/admin`) affiche une vue synthétique de l'activité de la plateforme :

- Nombre total d'utilisateurs, de modules, d'items
- Dernières activités enregistrées
- Alertes (signalements en attente, mises à jour disponibles...)

## Gestion des utilisateurs

### Liste des utilisateurs (`/admin/users`)

Affiche la liste de tous les comptes avec leurs informations principales :
- Nom, e-mail, rôle, date d'inscription, dernière connexion
- Statut (actif, banni, e-mail non vérifié)

Actions disponibles sur chaque utilisateur :
- **Modifier** les informations et le rôle
- **Forcer le changement de mot de passe** (l'utilisateur devra changer son mot de passe à la prochaine connexion)
- **Exporter les données** d'un utilisateur (modules, progression, scores)
- **Bannir / Débannir**
- **Supprimer** le compte

### Créer un utilisateur (`/admin/users/create`)

Formulaire de création d'un compte directement depuis l'administration, sans passer par le formulaire d'inscription public.

Champs : nom, e-mail, mot de passe, rôle, statut de vérification de l'e-mail.

### Import CSV d'utilisateurs (`/admin/users/import`)

Permet de créer plusieurs comptes en une seule opération. Le fichier CSV doit respecter ce format :

```csv
name,email,password,role
Jean Dupont,jean.dupont@example.fr,MotDePasse123!,utilisateur
Marie Martin,marie.martin@example.fr,MotDePasse456!,utilisateur
```

- La colonne `role` doit correspondre au **slug** d'un rôle existant
- Les mots de passe sont hachés automatiquement avant l'insertion

### Exporter tous les utilisateurs (`/admin/users/export-all`)

Télécharge un fichier CSV listant tous les comptes (nom, e-mail, rôle, date d'inscription).

### Gestion des bannissements

- **Bannir** un utilisateur : `/admin/users/{user}/bans` - l'utilisateur ne peut plus se connecter
- **Lever un bannissement** : `/admin/users/{user}/bans/{ban}`
- **Liste des bannissements actifs** : `/admin/bans`

## Gestion des rôles et permissions

### Liste des rôles (`/admin/roles`)

Affiche tous les rôles disponibles avec leur ordre d'affichage et leurs permissions.

### Créer / modifier un rôle

Chaque rôle dispose des permissions suivantes, activables individuellement :

| Permission | Description |
|---|---|
| `can_train_own` | S'entraîner sur ses propres modules (mode Anki) |
| `can_train_public` | S'entraîner sur les modules publics (mode Anki) |
| `can_test_own` | Tester ses propres modules (mode Test / Examen) |
| `can_test_public` | Tester les modules publics (mode Test / Examen) |
| Création de modules | Créer de nouveaux modules |
| Publication | Rendre ses modules publics |

> Les utilisateurs `is_admin = true` contournent toutes les vérifications de permission.

### Réordonner les rôles

Les rôles peuvent être réordonnés par glisser-déposer depuis la liste (`/admin/roles`). L'ordre détermine l'affichage dans les menus déroulants.

## Gestion des modules

### Liste des modules publics (`/admin/modules`)

L'administrateur peut consulter tous les modules publics de la plateforme et les supprimer si nécessaire (contenu inapproprié, violation des règles).

### Modules privés (`/admin/private-modules`)

Liste des modules privés (non publics) de tous les utilisateurs, avec possibilité de suppression.

## Signalements et modération

### Signalements de modules (`/admin/reports`)

Liste les modules signalés par des utilisateurs, avec :
- Le motif du signalement
- Les actions : **Traiter** (marquer comme résolu) ou **Rejeter** (signalement non fondé)

### Signalements d'avis et de réponses

Les avis (notes) et leurs réponses peuvent également être signalés. Actions disponibles :
- **Traiter** : marquer le signalement comme traité
- **Supprimer** l'avis ou la réponse incriminée
- **Sanctionner l'auteur** (mise en sourdine)

### Workflow de modération

Chaque signalement suit un cycle de statut :

| Statut | Description |
|---|---|
| En attente | Signalement reçu, non encore traité |
| Traité | Signalement examiné et résolu |
| Sanctionné | L'auteur du contenu a été sanctionné |

### Sanctions (`/admin/sanctions`)

Liste toutes les sanctions actives (mises en sourdine). Possibilité de lever une sanction individuellement.

### Historique des commentaires (`/admin/comment-history`)

Consulte l'historique des commentaires postés sur les articles, avec possibilité de suppression.

## Notifications administrateur

### Envoyer une notification à tous les utilisateurs

1. Accédez à **Admin - Notifications - Créer**
2. Rédigez le titre et le contenu de la notification
3. Cliquez sur **Envoyer** - tous les utilisateurs verront la notification dans leur cloche de notification

### Gérer les notifications existantes

- **Supprimer** une notification envoyée

## Articles (actualités)

Le module **Posts** permet de publier des articles visibles sur la plateforme :

1. **Admin - Articles - Créer** : rédigez l'article avec TinyMCE
2. Les articles publiés sont accessibles à l'URL `/news/{slug}`
3. Les utilisateurs connectés peuvent réagir (emojis) et commenter les articles

## Pages statiques

Le module **Pages** gère les pages d'information :

1. **Admin - Pages - Créer** : créez une page statique (mentions légales, FAQ, etc.)
2. Les pages sont accessibles à l'URL `/p/{slug}`

## Navigation

### Personnaliser la barre de navigation (`/admin/navbar`)

- **Créer** un lien de navigation (titre, URL ou route, icône, ordre)
- **Réordonner** les liens par glisser-déposer
- **Modifier** ou **Supprimer** un lien existant

## Paramètres généraux

### Paramètres principaux (`/admin/settings`)

| Paramètre | Description |
|---|---|
| Nom de l'application | Affiché dans le titre de la page et les e-mails |
| Logo | Image du logo (format PNG recommandé) |
| Favicon | Icône de l'onglet navigateur |

### Page d'accueil (`/admin/settings/home`)

Configurez le contenu affiché sur la page d'accueil publique.

### Authentification (`/admin/settings/auth`)

| Paramètre | Description |
|---|---|
| Inscription publique | Autoriser ou bloquer les nouvelles inscriptions |
| Vérification e-mail obligatoire | Forcer la vérification e-mail à l'inscription |
| 2FA obligatoire | Forcer l'activation de la 2FA pour tous les utilisateurs |
| Rôle par défaut | Rôle attribué automatiquement aux nouveaux inscrits |

### Configuration e-mail (`/admin/settings/mail`)

Configurez les paramètres SMTP depuis l'interface (alternative au fichier `.env`) :
- Serveur SMTP, port, encryption
- Identifiants SMTP
- Adresse et nom d'expédition
- **Envoyer un e-mail de test** pour vérifier la configuration

### Maintenance (`/admin/settings/maintenance`)

- Activer le **mode maintenance** (affiche une page de maintenance aux utilisateurs non-admin)
- Message personnalisé affiché pendant la maintenance

### Fonctionnalités (`/admin/settings/features`)

Activer ou désactiver des fonctionnalités de la plateforme :
- Bibliothèque publique
- Système de notation des modules
- Examens partagés
- Groupes

## Plugins

### Liste des plugins (`/admin/plugins`)

Affiche tous les plugins disponibles (installés et non installés) avec leur état :

| État | Description |
|---|---|
| Actif | Le plugin est installé et activé |
| Inactif | Le plugin est installé mais désactivé |
| Non installé | Le plugin est disponible mais non encore installé |

### Actions sur les plugins

- **Activer / Désactiver** un plugin sans le supprimer
- **Installer** un nouveau plugin depuis le catalogue
- **Mettre à jour** un plugin vers sa dernière version
- **Supprimer** un plugin (irréversible)
- **Recharger** la liste des plugins disponibles

## Thèmes

### Gérer les thèmes (`/admin/themes`)

Un thème définit l'apparence globale de la plateforme (couleurs, polices, styles CSS).

- **Créer** un thème personnalisé
- **Modifier** les couleurs primaires, secondaires, et les couleurs de mode clair/sombre
- **Dupliquer** un thème existant pour le personnaliser
- **Activer** un thème pour l'appliquer à toute la plateforme
- **Supprimer** un thème (sauf le thème actif)

## Gestion des images (`/admin/images`)

Bibliothèque centralisée d'images pour les articles et pages :
- Upload de nouvelles images
- Suppression des images inutilisées

## Redirections (`/admin/redirects`)

Gestion des redirections HTTP (301/302) :
- Créer une redirection (source - destination)
- Modifier ou supprimer une redirection existante

## Emojis (`/admin/emojis`)

Gestion de la bibliothèque d'emojis personnalisés utilisables dans les réactions :
- Ajouter un emoji (image SVG ou PNG)
- Importer un pack d'emojis (fichier ZIP)
- Supprimer un emoji

## Logs d'activité

### Consulter les logs (`/admin/logs`)

L'historique de toutes les actions effectuées sur la plateforme (connexions, créations, suppressions, modifications) est disponible depuis **Admin - Logs**.

Chaque entrée contient :
- L'utilisateur concerné
- L'action effectuée
- La date et l'heure
- L'ancienne et la nouvelle valeur (pour les modifications)

### Actions sur les logs

- **Consulter** le détail d'un log
- **Vider** les logs récents
- **Purger** les logs de plus de 30 jours (nettoyage automatique recommandé)

## Mises à jour

### Vérifier les mises à jour (`/admin/update`)

L'interface de mise à jour permet de mettre à jour l'application depuis le panel sans accès SSH :

1. **Vérifier** si une mise à jour est disponible (bouton **Vérifier**)
2. **Télécharger** le package de mise à jour
3. **Sauvegarder** les fichiers et la base de données avant l'installation
4. **Installer** la mise à jour
5. Télécharger les sauvegardes créées avant la mise à jour

> Il est fortement recommandé de sauvegarder la base de données et les fichiers **avant** toute mise à jour.

### Sauvegarde avant mise à jour

L'interface propose :
- **Sauvegarde des fichiers** : archive ZIP de tous les fichiers de l'application
- **Sauvegarde de la base de données** : dump SQL complet
- **Télécharger une sauvegarde** : récupérer les archives créées
