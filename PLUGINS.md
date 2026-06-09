# Documentation des plugins Mnemo

Les plugins permettent d'étendre les fonctionnalités de Mnemo sans modifier le cœur de l'application.

---

## Installation d'un plugin

1. Va dans **Admin → Plugins → Disponibles**
2. Clique sur **Installer** à côté du plugin souhaité
3. Une fois installé, clique sur **Activer**
4. Le plugin est immédiatement actif sur le site

Pour désactiver un plugin sans le supprimer, clique sur **Désactiver** dans l'onglet **Installés**.

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

Le champ est optionnel — si vide, aucune requête n'est envoyée.

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
