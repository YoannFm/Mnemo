#!/bin/bash

set -e

echo "🚀 Installation de Mnemo..."
echo ""

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 1. Installer les dépendances
echo -e "${BLUE}1️⃣  Installation des dépendances PHP...${NC}"
composer install -q
echo -e "${GREEN}✓ Dépendances installées${NC}"
echo ""

# 2. Copier .env
echo -e "${BLUE}2️⃣  Configuration du fichier .env...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Fichier .env créé${NC}"
else
    echo -e "${GREEN}✓ Fichier .env existe déjà${NC}"
fi
echo ""

# 3. Générer la clé
echo -e "${BLUE}3️⃣  Génération de la clé d'application...${NC}"
php artisan key:generate -q
echo -e "${GREEN}✓ Clé générée${NC}"
echo ""

# 4. Créer la base de données
echo -e "${BLUE}4️⃣  Création du dossier database...${NC}"
mkdir -p database
echo -e "${GREEN}✓ Dossier database créé${NC}"
echo ""

echo -e "${GREEN}✨ Installation complète !${NC}"
echo ""
echo -e "${BLUE}Démarrage du serveur...${NC}"
echo "📱 Ouvre http://localhost:8000 dans ton navigateur"
echo ""

# 5. Lancer le serveur
php artisan serve
