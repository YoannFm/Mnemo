# Mnemo — Introduction

## Présentation générale

**Mnemo** est une application web de mémorisation par répétition espacée, développée pour le **Lycée Rascol d'Albi**. Elle permet aux enseignants de créer des modules de vocabulaire ou de connaissances structurées, et aux élèves de les mémoriser efficacement grâce à plusieurs modes d'entraînement.

L'application repose sur le principe scientifiquement éprouvé de la **répétition espacée** (algorithme SM-2), qui optimise les intervalles de révision en fonction des performances de chaque utilisateur. Un item bien maîtrisé sera revu moins souvent ; un item difficile sera présenté plus fréquemment.

---

## Objectif

- Permettre la création de **modules de mémorisation** multi-médiaux (photo, audio, texte)
- Proposer trois modes d'entraînement adaptés à des objectifs pédagogiques différents
- Offrir un suivi personnalisé de la progression de chaque élève
- Faciliter le partage de modules et d'examens au sein d'une classe

---

## Public cible

| Profil | Usage principal |
|---|---|
| **Enseignants** | Créer des modules, partager des examens, consulter les résultats |
| **Élèves** | S'entraîner sur les modules, passer des examens partagés |
| **Administrateurs** | Gérer les utilisateurs, les rôles, les paramètres de la plateforme |

---

## Stack technique

| Composant | Technologie | Version |
|---|---|---|
| Langage serveur | PHP | 8.3+ |
| Framework PHP | Laravel | 13.x |
| Interface utilisateur | Bootstrap | 5.3 |
| Base de données (production) | MySQL / MariaDB | 8.0+ |
| Base de données (développement) | SQLite | 3.x |
| Éditeur de texte riche | TinyMCE | (via CDN ou clé API) |
| Algorithme d'apprentissage | SM-2 (SuperMemo 2) | — |
| Génération de QR code | bacon/bacon-qr-code | 3.1+ |
| Export PDF | barryvdh/laravel-dompdf | 3.1+ |
| Export Excel | phpoffice/phpspreadsheet | 5.8+ |
| Traitement d'images | intervention/image | 4.1+ |
| Authentification 2FA | pragmarx/google2fa-laravel | 3.0+ |
| Gestion des jobs | Laravel Queue (driver database) | — |
| Frontend assets | Vite + npm | — |

---

## Architecture globale

L'application suit l'architecture **MVC** (Modèle - Vue - Contrôleur) imposée par Laravel :

- **Modèles** (`app/Models/`) : `User`, `Module`, `Item`, `Progress`, `Score`, `SharedExam`, etc.
- **Contrôleurs** (`app/Http/Controllers/`) : un contrôleur par fonctionnalité majeure
- **Vues** (`resources/views/`) : templates Blade organisés par domaine fonctionnel
- **Services** (`app/Services/`) : `QuizGenerator` pour la génération des questions
- **Plugins** (`plugins/`) : extensions optionnelles (ex. import Quizlet, tags)

---

## Fonctionnalités principales

1. Création et gestion de **modules** avec champs configurables (nom FR, nom alternatif, photo, description/fonction, audio)
2. **Mode Anki** : flashcards infinies avec suivi SM-2 et feedback immédiat
3. **Mode Test** : QCM à nombre de questions fixe avec score final
4. **Mode Examen** : QCM exhaustif (tous les items) avec score et correction
5. **Examens partagés** : partage d'un examen via lien/QR code, consultation des résultats par l'enseignant
6. **Bibliothèque publique** : accès aux modules partagés par d'autres utilisateurs
7. **Système de rôles** : permissions granulaires par profil
8. **Panel d'administration** : gestion complète des utilisateurs, modules et paramètres

---

## Liens rapides

- [Installation](2-installation.md)
- [Guide utilisateur](3-guide-utilisateur.md)
- [Guide administrateur](4-guide-administrateur.md)
- [Schéma de base de données](SCHEMA.md)
- [Documentation technique](TECHNIQUE.md)
