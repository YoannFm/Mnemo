# Documentation des plugins Mnemo

Les plugins permettent d'étendre les fonctionnalités de Mnemo sans modifier le cœur de l'application.

---

## Installation d'un plugin

Les plugins présents dans le dossier `plugins/` sont fournis avec Mnemo : ils sont déjà installés, il suffit de les activer.

Pour installer un plugin du catalogue :

1. Va dans **Admin → Plugins**, section **Plugins disponibles**
2. Clique sur **Installer** à côté du plugin souhaité
3. Une fois installé, clique sur **Activer** dans la section **Plugins installés**
4. Le plugin est immédiatement actif sur le site

Pour désactiver un plugin sans le supprimer, clique sur **Désactiver** dans la section **Plugins installés**.

---

## Catalogue des plugins

La liste des plugins disponibles est le fichier [`plugins.json`](plugins.json) à la racine du dépôt GitHub. L'application le lit à l'adresse définie par `MNEMO_PLUGINS_CATALOG` (par défaut `https://raw.githubusercontent.com/YoannFm/Mnemo/main/plugins.json`).

Format :

```json
{
    "plugins": [
        {
            "slug": "mon-plugin",
            "name": "Mon plugin",
            "description": "Ce que fait le plugin.",
            "author": "Auteur",
            "version": "1.0.0",
            "download_url": "https://github.com/auteur/mon-plugin/releases/download/v1.0.0/mon-plugin.zip"
        }
    ]
}
```

| Champ | Obligatoire | Description |
|---|---|---|
| `slug` | oui | Identifiant du plugin (minuscules, chiffres et tirets). Doit être égal à l'`id` du `plugin.json` du plugin |
| `name` | oui | Nom affiché |
| `version` | oui | Dernière version publiée, comparée à celle du plugin installé pour proposer la mise à jour |
| `download_url` | oui | URL `https://` de l'archive ZIP du plugin |
| `description`, `author` | non | Informations affichées dans l'admin |

L'archive ZIP doit contenir directement les fichiers du plugin (`plugin.json` à la racine de l'archive, sans dossier parent) : elle est extraite dans `plugins/{slug}/`. Le plus simple est de l'attacher à une release GitHub du dépôt du plugin.

Pour publier un plugin ou une nouvelle version, ajoute ou modifie son entrée dans `plugins.json` et propose la modification sur le dépôt.

---

## Plugin Streak

**Version** : 1.0.0  
**Auteur** : YoannFM

### Description
Affiche un widget sur le tableau de bord indiquant le nombre de jours consécutifs d'apprentissage de l'utilisateur. La série est incrémentée chaque jour où l'utilisateur réalise au moins une activité (test, Anki, examen).

### Utilisation
Aucune configuration requise. Une fois activé, le widget apparaît automatiquement sur le tableau de bord de chaque utilisateur connecté.

### Données suivies
- **Série actuelle** : nombre de jours consécutifs d'activité
- **Meilleure série** : record personnel de l'utilisateur

---

## Plugin Webhook

**Version** : 1.0.0  
**Auteur** : YoannFM

### Description
Envoie automatiquement les résultats d'un examen partagé vers une URL externe dès qu'un élève termine l'examen. Compatible avec Zapier, Make, Google Sheets (via Apps Script), Notion, et tout service acceptant des requêtes HTTP POST.

### Configuration
Lors de la création d'un examen partagé, un champ **URL Webhook** est disponible. Colle l'URL fournie par ton service externe (ex : URL de scénario Zapier, webhook Make, etc.).

Le champ est optionnel - si vide, aucune requête n'est envoyée.

### Format des données envoyées
Les données sont envoyées en JSON via une requête POST :

```json
{
    "exam": "Nom de l'examen",
    "module": "Titre du module",
    "participant": "Prénom Nom",
    "score": 14,
    "total": 20,
    "grade": "14.00",
    "percentage": 70,
    "finished_at": "2026-06-09T10:30:00+02:00"
}
```

### Exemples d'utilisation
- **Google Sheets** : via Google Apps Script, enregistrer chaque résultat dans une feuille de calcul
- **Zapier / Make** : déclencher un e-mail personnalisé, alimenter une base Airtable, envoyer une notification Slack
- **Serveur custom** : traiter les résultats dans ton propre backend

---

## Plugin Import Quizlet

**Version** : 1.0.0  
**Auteur** : YoannFM

### Description
Permet d'importer un set Quizlet existant comme module Mnemo en quelques secondes, sans ressaisir les données manuellement.

### Comment exporter depuis Quizlet
1. Ouvre ton set sur [quizlet.com](https://quizlet.com)
2. Clique sur **···** (plus d'options)
3. Clique sur **Exporter**
4. Copie le texte affiché (format : Terme `Tab` Définition, une ligne par carte)

### Utilisation dans Mnemo
1. Va sur **/quizlet-import** (lien dans le menu de navigation)
2. Donne un nom au module
3. Choisis le séparateur utilisé (Tab par défaut pour Quizlet, mais aussi Point-virgule ou Pipe)
4. Colle le texte exporté dans la zone de texte
5. Clique sur **Créer le module**

Le module est créé immédiatement avec tous les items. Le terme devient le champ `name_fr` et la définition devient le champ `function_text`.

### Séparateurs supportés
| Séparateur | Symbole | Cas d'usage |
|------------|---------|-------------|
| Tab | `\t` | Export Quizlet par défaut |
| Point-virgule | `;` | Export CSV français |
| Pipe | `\|` | Formats personnalisés |

### Après l'import
Le module est créé en mode **privé**. Tu peux ensuite :
- Ajouter des photos aux items
- Renseigner le champ `name_alt` (traduction, terme latin, etc.)
- Rendre le module public depuis ses paramètres
