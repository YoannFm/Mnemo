# Schéma de base de données — Mnémo

## Diagramme des tables

```
users (id, name, email, password, email_verified_at, created_at, updated_at)
  |
  | 1..N
  v
modules (id, owner_id→users, title, description, is_public, created_at, updated_at)
  |
  | 1..N
  v
items (id, module_id→modules, name_fr, name_en, function_text, photo_path, created_at, updated_at)
  |
  | 1..N (via user_id + item_id)
  v
progress (id, user_id→users, item_id→items, success_count, fail_count, streak, easiness_factor, interval_days, next_review, last_seen, created_at, updated_at)

scores (id, user_id→users, module_id→modules, score, total, created_at, updated_at)
```

## Description des tables

### `users`
Gérée par Laravel Breeze. Contient les comptes utilisateurs. `email_verified_at` est renseigné lors de la vérification e-mail (ou manuellement pour les comptes de démo).

### `modules`
Chaque module appartient à un utilisateur (`owner_id`). Le champ `is_public` contrôle la visibilité dans la bibliothèque publique. La suppression d'un utilisateur entraîne la suppression en cascade de ses modules.

### `items`
Un item appartient à un module (`module_id`). Il possède obligatoirement un nom français (`name_fr`), un nom anglais (`name_en`) et un texte de fonction (`function_text`). Le champ `photo_path` est nullable : il stocke le chemin relatif dans `storage/app/public`. La suppression d'un module entraîne la suppression en cascade de ses items.

### `progress`
Enregistre la progression d'un utilisateur sur un item précis. La contrainte d'unicité sur `(user_id, item_id)` garantit une seule entrée par paire. Les champs SM-2 (`easiness_factor`, `interval_days`, `next_review`) ont été ajoutés via une migration ultérieure (`add_sm2_to_progress_table`). `streak` comptabilise les bonnes réponses consécutives : à partir de 3, l'item est considéré comme maîtrisé.

### `scores`
Historique des sessions de test terminées. Chaque ligne correspond à une session : `score` est le nombre de bonnes réponses, `total` est le nombre de questions posées. Permet d'afficher l'évolution des performances par module.

## Index importants

| Table      | Colonnes indexées           | Raison                                               |
|------------|----------------------------|------------------------------------------------------|
| `modules`  | `owner_id`                 | Clé étrangère, requêtes fréquentes par utilisateur   |
| `modules`  | `is_public`                | Filtrage bibliothèque publique                       |
| `items`    | `module_id`                | Clé étrangère, chargement des items d'un module      |
| `progress` | `(user_id, item_id)` UNIQUE| Contrainte métier + accès rapide par paire           |
| `progress` | `next_review`              | Sélection des items à réviser en mode Anki           |
| `scores`   | `user_id`, `module_id`     | Clés étrangères, historique par utilisateur/module   |
