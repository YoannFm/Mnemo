# Mnemo — Annexes

## A. Tableau complet des routes

### Routes d'installation (publiques)

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/install` | `install.index` | Page d'accueil de l'assistant d'installation |
| GET | `/install/prerequisites` | `install.prerequisites` | Vérification des prérequis |
| GET | `/install/database` | `install.database` | Configuration de la base de données |
| GET | `/install/admin` | `install.admin` | Formulaire de création du compte admin |
| POST | `/install/admin` | `install.admin.store` | Création du compte admin |
| GET | `/install/complete` | `install.complete` | Confirmation de fin d'installation |

### Routes publiques

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/` | `home` | Redirige vers le dashboard |
| GET | `/p/{slug}` | `pages.show` | Page statique publique |
| GET | `/news/{slug}` | `posts.show` | Afficher un article |
| GET | `/sitemap.xml` | `sitemap` | Sitemap XML dynamique |

### Routes authentifiées — Profil

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/dashboard` | `dashboard` | Tableau de bord utilisateur |
| GET | `/profile` | `profile.edit` | Formulaire de modification du profil |
| PATCH | `/profile` | `profile.update` | Sauvegarder les modifications |
| PATCH | `/profile/accent` | `profile.accent` | Changer la couleur d'accentuation |
| GET | `/profile/accent/reset` | `profile.accent.reset` | Réinitialiser la couleur |
| PATCH | `/profile/email-notifications` | `profile.email-notifications` | Préférences de notifications e-mail |
| DELETE | `/profile` | `profile.destroy` | Supprimer le compte |
| GET | `/profile/2fa` | `profile.2fa.show` | Page de gestion de la 2FA |
| GET/POST | `/profile/2fa/enable` | `profile.2fa.enable` | Activer la 2FA |
| POST | `/profile/2fa/confirm` | `profile.2fa.confirm` | Confirmer l'activation |
| DELETE | `/profile/2fa` | `profile.2fa.disable` | Désactiver la 2FA |

### Routes authentifiées — Modules

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/modules` | `modules.index` | Liste des modules de l'utilisateur |
| GET | `/modules/create` | `modules.create` | Formulaire de création |
| POST | `/modules` | `modules.store` | Créer un module |
| GET | `/modules/{id}` | `modules.show` | Détail d'un module |
| GET | `/modules/{id}/edit` | `modules.edit` | Formulaire de modification |
| PUT/PATCH | `/modules/{id}` | `modules.update` | Mettre à jour un module |
| DELETE | `/modules/{id}` | `modules.destroy` | Mettre en corbeille |
| GET | `/modules/trash` | `modules.trash` | Corbeille des modules |
| POST | `/modules/{id}/restore` | `modules.restore` | Restaurer depuis la corbeille |
| DELETE | `/modules/{id}/force-delete` | `modules.force-delete` | Suppression définitive |
| GET | `/modules/{id}/preview` | `modules.preview` | Prévisualisation JSON (modal bibliothèque) |
| GET | `/modules/import` | `modules.import.form` | Formulaire d'import ZIP |
| POST | `/modules/import` | `modules.import` | Importer un module ZIP |
| GET | `/modules/{id}/export` | `modules.export` | Exporter un module en ZIP |
| DELETE | `/modules/{id}/progress/reset` | `modules.progress.reset` | Réinitialiser la progression |
| POST | `/modules/{id}/duplicate` | `modules.duplicate` | Dupliquer un module public |
| POST | `/modules/{id}/report` | `modules.report` | Signaler un module |
| POST | `/modules/{id}/rate` | `modules.rate` | Noter un module |
| POST | `/modules/{id}/share` | `shared-exam.create` | Créer un examen partagé |

### Routes authentifiées — Items

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/modules/{id}/items/create` | `modules.items.create` | Formulaire d'ajout d'item |
| POST | `/modules/{id}/items` | `modules.items.store` | Créer un item |
| GET | `/modules/{id}/items/{item}/edit` | `modules.items.edit` | Formulaire de modification |
| PUT/PATCH | `/modules/{id}/items/{item}` | `modules.items.update` | Mettre à jour un item |
| DELETE | `/modules/{id}/items/{item}` | `modules.items.destroy` | Supprimer un item |
| GET | `/modules/{id}/items/import` | `modules.items.import.form` | Formulaire d'import CSV |
| POST | `/modules/{id}/items/import` | `modules.items.import` | Importer des items CSV |

### Routes authentifiées — Quiz

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/modules/{id}/anki` | `anki.show` | Page de configuration Anki |
| POST | `/modules/{id}/anki/start` | `anki.start` | Démarrer une session Anki |
| GET | `/modules/{id}/anki/question` | `anki.question` | Question courante Anki |
| POST | `/modules/{id}/anki/submit` | `anki.submit` | Soumettre une réponse Anki (JSON) |
| POST | `/modules/{id}/anki/quit` | `anki.quit` | Quitter la session Anki |
| POST | `/modules/{id}/anki/review` | `anki.review` | Démarrer une révision ciblée |
| GET | `/modules/{id}/test` | `test.show` | Page de configuration du Test |
| POST | `/modules/{id}/test/start` | `test.start` | Démarrer un Test |
| GET | `/modules/{id}/test/question` | `test.question` | Question courante du Test |
| POST | `/modules/{id}/test/submit` | `test.submit` | Soumettre une réponse Test |
| GET | `/modules/{id}/test/result` | `test.result` | Résultats du Test |
| GET | `/modules/{id}/exam` | `exam.show` | Page de configuration de l'Examen |
| POST | `/modules/{id}/exam/start` | `exam.start` | Démarrer un Examen |
| GET | `/modules/{id}/exam/question` | `exam.question` | Question courante de l'Examen |
| POST | `/modules/{id}/exam/submit` | `exam.submit` | Soumettre une réponse Examen |
| GET | `/modules/{id}/exam/result` | `exam.result` | Résultats de l'Examen |

### Routes authentifiées — Examens partagés

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/mes-examens` | `shared-exam.index` | Liste des examens partagés créés |
| GET | `/mes-resultats` | `my-exam-results` | Résultats personnels aux examens partagés |
| GET | `/shared-exam/{id}/results` | `shared-exam.results` | Résultats d'un examen partagé (créateur) |
| DELETE | `/shared-exam/{id}` | `shared-exam.destroy` | Supprimer un examen partagé |
| GET | `/e/{uuid}` | `guest.exam.show` | Accéder à un examen partagé |
| POST | `/e/{uuid}/start` | `guest.exam.start` | Démarrer l'examen partagé |
| GET | `/e/{uuid}/question` | `guest.exam.question` | Question courante |
| POST | `/e/{uuid}/answer` | `guest.exam.answer` | Soumettre une réponse |
| GET | `/e/{uuid}/finish` | `guest.exam.finish` | Page de fin |
| GET | `/shared-exam/{id}/export-grades` | `shared-exam.export-grades` | Exporter les notes (CSV) |
| GET | `/shared-exam/{id}/export-excel` | `shared-exam.export-excel` | Exporter les notes (Excel) |
| GET | `/shared-exam/{id}/export-pdf` | `shared-exam.export-pdf` | Exporter les notes (PDF) |

### Routes authentifiées — Autres

| Méthode | URL | Nom | Description |
|---|---|---|---|
| GET | `/bibliotheque` | `library.index` | Bibliothèque publique |
| GET | `/progression` | `progress.index` | Tableau de progression |
| GET | `/notifications` | `notifications.index` | Centre de notifications |
| GET | `/groupes` | `groups.index` | Liste des groupes |
| GET | `/groupes/{id}` | `groups.show` | Détail d'un groupe |

---

## B. Structure de la base de données

### Table `users`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `name` | varchar | Nom d'affichage |
| `email` | varchar (unique) | Adresse e-mail |
| `password` | varchar | Mot de passe haché (bcrypt) |
| `email_verified_at` | timestamp (nullable) | Date de vérification de l'e-mail |
| `is_admin` | boolean | Droits d'administration |
| `role_id` | bigint (FK, nullable) | Rôle attribué |
| `two_factor_secret` | text (nullable) | Secret TOTP pour la 2FA |
| `two_factor_confirmed_at` | timestamp (nullable) | Confirmation de la 2FA |
| `accent_color` | varchar (nullable) | Couleur d'accentuation personnalisée |
| `email_notifications` | boolean | Activer les notifications e-mail |
| `created_at` / `updated_at` | timestamp | Horodatages |

### Table `modules`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `owner_id` | bigint (FK → users) | Propriétaire du module |
| `title` | varchar | Titre du module |
| `description` | text (nullable) | Description (HTML) |
| `is_public` | boolean | Visible dans la bibliothèque |
| `allow_duplication` | boolean | Duplication autorisée |
| `field_name_fr` | boolean | Champ "Nom FR" actif |
| `field_name_alt` | boolean | Champ "Nom alternatif" actif |
| `field_photo` | boolean | Champ "Photo" actif |
| `field_function` | boolean | Champ "Description/Fonction" actif |
| `field_audio` | boolean | Champ "Audio" actif |
| `deleted_at` | timestamp (nullable) | Soft delete (corbeille) |
| `created_at` / `updated_at` | timestamp | Horodatages |

### Table `items`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `module_id` | bigint (FK → modules) | Module parent |
| `name_fr` | varchar (nullable) | Nom principal (français) |
| `name_alt` | varchar (nullable) | Nom alternatif |
| `function_text` | text (nullable) | Description / fonction |
| `photo_path` | varchar (nullable) | Chemin relatif de la photo |
| `audio_path` | varchar (nullable) | Chemin relatif du fichier audio |
| `created_at` / `updated_at` | timestamp | Horodatages |

### Table `progress`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `user_id` | bigint (FK → users) | Utilisateur |
| `item_id` | bigint (FK → items) | Item concerné |
| `success_count` | integer (défaut 0) | Total des bonnes réponses |
| `fail_count` | integer (défaut 0) | Total des mauvaises réponses |
| `streak` | integer (défaut 0) | Bonnes réponses consécutives actuelles |
| `easiness_factor` | float (défaut 2.5) | Facteur de facilité SM-2 |
| `interval_days` | integer (défaut 1) | Intervalle de révision actuel (jours) |
| `next_review` | date (nullable) | Date de prochaine révision prévue |
| `last_seen` | timestamp (nullable) | Dernière fois que l'item a été présenté |
| `created_at` / `updated_at` | timestamp | Horodatages |

> Contrainte UNIQUE sur `(user_id, item_id)` : une seule entrée par paire utilisateur/item.

### Table `scores`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `user_id` | bigint (FK → users) | Utilisateur |
| `module_id` | bigint (FK → modules) | Module testé |
| `score` | integer | Nombre de bonnes réponses |
| `total` | integer | Nombre total de questions |
| `created_at` / `updated_at` | timestamp | Horodatages |

### Table `shared_exams`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `uuid` | uuid (unique) | Identifiant public (URL) |
| `module_id` | bigint (FK → modules) | Module utilisé |
| `owner_id` | bigint (FK → users) | Créateur de l'examen |
| `max_attempts` | integer | Nombre de tentatives autorisées |
| `created_at` / `updated_at` | timestamp | Horodatages |

### Table `shared_exam_attempts`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `shared_exam_id` | bigint (FK → shared_exams) | Examen partagé |
| `user_id` | bigint (FK → users) | Participant |
| `score` | integer (nullable) | Score obtenu |
| `total` | integer (nullable) | Total de questions |
| `completed_at` | timestamp (nullable) | Date de fin |
| `created_at` / `updated_at` | timestamp | Horodatages |

### Table `roles`

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Identifiant unique |
| `name` | varchar | Nom du rôle |
| `slug` | varchar (unique) | Identifiant textuel |
| `icon` | varchar (nullable) | Icône Bootstrap Icons |
| `order` | integer | Ordre d'affichage |
| `can_train_own` | boolean | Permission : s'entraîner sur ses modules |
| `can_train_public` | boolean | Permission : s'entraîner sur les modules publics |
| `can_test_own` | boolean | Permission : tester ses modules |
| `can_test_public` | boolean | Permission : tester les modules publics |

---

## C. Types de questions Q1–Q16

Le `QuizGenerator` supporte 16 types de questions, couvrant toutes les combinaisons entre les 5 champs d'un item.

| Code | `field_question` | `field_answer` | Libellé | Description |
|---|---|---|---|---|
| Q1 | `photo_path` | `name_fr` | Identification | Voir une photo → trouver le nom FR |
| Q2 | `photo_path` | `function_text` | Description | Voir une photo → trouver la description |
| Q3 | `function_text` | `photo_path` | Reconnaissance | Lire une description → trouver la photo |
| Q4 | `name_fr` | `name_alt` | Traduction | Voir le nom FR → trouver le nom alternatif |
| Q5 | `name_alt` | `photo_path` | Correspondance | Voir le nom alternatif → trouver la photo |
| Q6 | `function_text` | `name_fr` | Désignation | Lire une description → trouver le nom FR |
| Q7 | `name_alt` | `name_fr` | Traduction | Voir le nom alternatif → trouver le nom FR |
| Q8 | `photo_path` | `name_alt` | Traduction | Voir une photo → trouver le nom alternatif |
| Q9 | `name_fr` | `photo_path` | Visualisation | Voir le nom FR → trouver la photo |
| Q10 | `name_fr` | `function_text` | Définition | Voir le nom FR → trouver la description |
| Q11 | `name_alt` | `function_text` | Signification | Voir le nom alternatif → trouver la description |
| Q12 | `function_text` | `name_alt` | Traduction | Lire une description → trouver le nom alternatif |
| Q13 | `audio_path` | `name_fr` | Identification | Écouter un audio → trouver le nom FR |
| Q14 | `audio_path` | `name_alt` | Traduction | Écouter un audio → trouver le nom alternatif |
| Q15 | `name_fr` | `audio_path` | Prononciation | Voir le nom FR → reconnaître l'audio |
| Q16 | `name_alt` | `audio_path` | Prononciation | Voir le nom alternatif → reconnaître l'audio |

> Un type de question n'est proposé que si l'item cible possède une valeur non vide pour `field_question` **et** qu'au moins 1 autre item du module possède une valeur non vide pour `field_answer` (pour servir de distracteur).

---

## D. Algorithme SM-2 (SuperMemo 2)

L'algorithme SM-2 détermine quand réviser un item pour maximiser la mémorisation à long terme.

### Principes

- Chaque item possède un **facteur de facilité** (`easiness_factor`, EF, défaut 2.5)
- Chaque item possède un **intervalle** (`interval_days`) entre deux révisions
- Après chaque réponse, EF et l'intervalle sont recalculés

### Formule

**Mise à jour du facteur de facilité :**

```
EF_nouveau = EF_actuel + (0.1 - (5 - qualité) × (0.08 + (5 - qualité) × 0.02))
EF_minimum = 1.3
```

**Calcul du prochain intervalle :**

| Condition | Intervalle suivant |
|---|---|
| Réponse incorrecte (qualité < 3) | 1 jour (recommencer depuis le début) |
| 1ère bonne réponse | 1 jour |
| 2ème bonne réponse | 6 jours |
| 3ème bonne réponse et au-delà | `intervalle_actuel × EF` |

**Implémentation dans Mnemo :**

- Réponse correcte → `qualité = 5`
- Réponse incorrecte → `qualité = 1`
- Maîtrise d'un item → `streak >= 3` (3 bonnes réponses consécutives)

### Pondération dans le mode Anki

| État de l'item | Poids dans le pool de sélection |
|---|---|
| Jamais vu (aucune entrée `progress`) | 5 |
| Maîtrisé et non dû pour révision | 1 (apparaît rarement) |
| Non maîtrisé et dû pour révision | max(5, fail_count - success_count + 5) |
| Maîtrisé et dû pour révision | 3 |
| Autres | max(1, fail_count - success_count + 3) |

---

## E. Format CSV d'import des items

### Structure

Le fichier CSV doit être encodé en **UTF-8** avec des virgules comme séparateurs.

```csv
name_fr,name_alt,function_text
Biceps brachial,Biceps brachii,"Muscle fléchisseur du coude, supinateur de l'avant-bras"
Triceps brachial,Triceps brachii,Muscle extenseur du coude
Deltoïde,Deltoid,"Muscle de l'épaule, abducteur du bras"
```

### Règles

- La première ligne doit être la ligne d'en-tête
- Les colonnes non reconnues sont ignorées
- Les colonnes `photo_path` et `audio_path` ne sont pas traitées lors de l'import CSV
- Les valeurs contenant des virgules doivent être entourées de guillemets doubles `"`
- Les valeurs vides sont acceptées (item créé avec ce champ vide)

---

## F. Format ZIP d'export/import de module

Un fichier ZIP exporté depuis Mnemo contient :

```
module_export.zip
├── module.json          # Métadonnées du module (titre, description, champs actifs)
├── items.json           # Liste des items avec leurs champs texte
└── media/               # Fichiers médias (photos et audios)
    ├── items/
    │   ├── photos/
    │   │   ├── image1.jpg
    │   │   └── image2.png
    │   └── audio/
    │       ├── son1.mp3
    │       └── son2.wav
```

Le fichier `items.json` contient les chemins relatifs vers les médias, qui sont reconstruits lors de l'import.

---

## G. Raccourcis clavier

### Mode Anki

| Touche | Action |
|---|---|
| `1` | Sélectionner et valider l'option 1 |
| `2` | Sélectionner et valider l'option 2 |
| `3` | Sélectionner et valider l'option 3 |
| `4` | Sélectionner et valider l'option 4 |
| `Espace` | Valider la réponse sélectionnée / Passer à la question suivante |
| `Entrée` | Valider la réponse sélectionnée / Passer à la question suivante |

### Mode Test et Mode Examen

| Touche | Action |
|---|---|
| `1` | Sélectionner et soumettre l'option 1 |
| `2` | Sélectionner et soumettre l'option 2 |
| `3` | Sélectionner et soumettre l'option 3 |
| `4` | Sélectionner et soumettre l'option 4 |

---

## H. Correspondance modes Anki ↔ types de questions

| Nom du mode (sélection) | Types de questions utilisés |
|---|---|
| Aléatoire | Tous types (Q1–Q16), sauf Q3, Q5, Q9, Q15, Q16 en mode aléatoire pur |
| Photo → Nom FR | Q1 |
| Photo → Nom alternatif | Q8 |
| Photo → Description | Q2 |
| Description → Photo | Q3 |
| Description → Nom FR | Q6 |
| Description → Nom alternatif | Q11 |
| Nom FR → Nom alternatif | Q4 |
| Nom FR → Photo | Q9 |
| Nom FR → Description | Q10 |
| Nom alternatif → Photo | Q5 |
| Nom alternatif → Nom FR | Q7 |
| Audio → Nom FR | Q13 |
| Audio → Nom alternatif | Q14 |
| Nom FR → Audio | Q15 |
| Nom alternatif → Audio | Q16 |

> En mode aléatoire, les types à réponse "media" (Q3, Q5, Q9, Q15, Q16) sont exclus pour éviter des QCM d'images ou d'audios difficiles à différencier dans l'interface.

---

## I. Variables d'environnement — Référence complète

| Variable | Valeur par défaut | Description |
|---|---|---|
| `APP_NAME` | `Laravel` | Nom de l'application |
| `APP_ENV` | `local` | Environnement (`local`, `production`) |
| `APP_KEY` | — | Clé de chiffrement (générer avec `artisan key:generate`) |
| `APP_DEBUG` | `true` | Afficher les erreurs détaillées |
| `APP_URL` | `http://localhost` | URL de base de l'application |
| `APP_LOCALE` | `fr` | Langue de l'application |
| `DB_CONNECTION` | `sqlite` | Driver de BDD (`sqlite`, `mysql`) |
| `DB_HOST` | `127.0.0.1` | Hôte MySQL |
| `DB_PORT` | `3306` | Port MySQL |
| `DB_DATABASE` | — | Nom de la base de données |
| `DB_USERNAME` | — | Utilisateur MySQL |
| `DB_PASSWORD` | — | Mot de passe MySQL |
| `SESSION_DRIVER` | `database` | Stockage des sessions |
| `SESSION_LIFETIME` | `120` | Durée de session en minutes |
| `QUEUE_CONNECTION` | `database` | Driver de queue |
| `CACHE_STORE` | `database` | Driver de cache |
| `LOG_CHANNEL` | `stack` | Canal de journalisation |
| `LOG_LEVEL` | `debug` | Niveau minimum de log |
| `MAIL_MAILER` | `log` | Driver d'envoi d'e-mails |
| `MAIL_HOST` | `127.0.0.1` | Serveur SMTP |
| `MAIL_PORT` | `2525` | Port SMTP |
| `MAIL_USERNAME` | — | Identifiant SMTP |
| `MAIL_PASSWORD` | — | Mot de passe SMTP |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Adresse d'expédition |
| `MAIL_FROM_NAME` | `${APP_NAME}` | Nom d'expédition |
| `TINYMCE_API_KEY` | — | Clé API TinyMCE |
| `FILESYSTEM_DISK` | `local` | Disque de stockage des fichiers |
