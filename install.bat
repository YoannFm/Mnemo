@echo off
setlocal enabledelayedexpansion

color 0A
echo.
echo 🚀 Installation de Mnemo...
echo.

REM 1. Installer les dépendances
echo 1️⃣  Installation des dépendances PHP...
composer install -q
echo ✓ Dépendances installées
echo.

REM 2. Copier .env
echo 2️⃣  Configuration du fichier .env...
if not exist .env (
    copy .env.example .env > nul
    echo ✓ Fichier .env créé
) else (
    echo ✓ Fichier .env existe déjà
)
echo.

REM 3. Générer la clé
echo 3️⃣  Génération de la clé d'application...
php artisan key:generate -q
echo ✓ Clé générée
echo.

REM 4. Créer la base de données
echo 4️⃣  Création du dossier database...
if not exist database mkdir database
echo ✓ Dossier database créé
echo.

echo ✨ Installation complète !
echo.
echo Démarrage du serveur...
echo 📱 Ouvre http://localhost:8000 dans ton navigateur
echo.

REM 5. Lancer le serveur
php artisan serve

pause
