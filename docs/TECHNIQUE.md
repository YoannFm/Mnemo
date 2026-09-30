# Documentation technique - Mnémo

## Organisation du code

L'application suit l'architecture MVC de Laravel :

- **Contrôleurs** (`app/Http/Controllers/`) : chaque fonctionnalité majeure dispose de son propre contrôleur. `ModuleController` et `ItemController` gèrent le CRUD des données. `AnkiController` et `TestController` pilotent les deux modes d'entraînement. `LibraryController` expose les modules publics. `ProgressController` agrège les statistiques par utilisateur.
- **Modèles** (`app/Models/`) : cinq modèles Eloquent - `User`, `Module`, `Item`, `Progress`, `Score` - avec leurs relations (`hasMany`, `belongsTo`).
- **Vues** (`resources/views/`) : organisées par domaine fonctionnel, elles utilisent le moteur Blade et héritent toutes du layout principal (`layouts/app.blade.php`).

## Moteur de quiz - QuizGenerator

`QuizGenerator` (placé dans `app/Http/Controllers/` pour proximité avec les contrôleurs) génère les questions QCM de manière générique. Huit types de questions (Q1–Q8) couvrent toutes les combinaisons possibles entre les quatre attributs d'un item (photo, nom FR, nom EN, fonction). Pour chaque question, le générateur sélectionne un item cible, détermine le type de question selon la disponibilité des données (un item sans photo ne peut pas déclencher Q1), puis pioche trois distracteurs parmi les autres items du module. Cette généricité permet d'appliquer le même moteur à n'importe quel module sans configuration supplémentaire.

## Mode Anki et algorithme SM-2

Le mode Anki implémente l'algorithme de répétition espacée SM-2. Chaque entrée dans la table `progress` conserve un facteur de facilité (`easiness_factor`, défaut 2,5), un intervalle en jours (`interval_days`) et la date de prochaine révision (`next_review`). Après chaque réponse, le contrôleur recalcule ces valeurs : un succès augmente l'intervalle et améliore légèrement le facteur, un échec remet l'intervalle à 1 jour. La sélection des items à réviser priorise ceux dont `next_review` est dépassée ou nulle.

## Choix techniques

- **Laravel** : framework structuré, écosystème mature (Breeze pour l'auth, Storage pour les uploads, Artisan pour les migrations).
- **SQLite en développement** : aucune installation serveur requise, base de données dans un fichier unique, facile à réinitialiser.
- **Bootstrap 5.3** : composants prêts à l'emploi, thème sombre natif via `data-bs-theme="dark"`, icônes Bootstrap Icons.

## Difficultés rencontrées

- **Gestion de la session quiz** : conserver l'état d'une session de test entre plusieurs requêtes HTTP sans base de données a nécessité de stocker les questions générées et les réponses en session Laravel.
- **Upload de photos** : la gestion des chemins relatifs entre `storage/app/public` et `public/storage` (lien symbolique) a demandé une attention particulière pour l'affichage et la suppression des fichiers.
- **Dark theme Bootstrap** : l'application du thème sombre sur les composants tiers (Select2, alertes Flash) a requis des surcharges CSS ciblées pour maintenir la cohérence visuelle.
