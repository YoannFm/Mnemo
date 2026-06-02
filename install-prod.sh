#!/bin/bash

set -e

echo "🚀 Installation de Mnemo - Production (Nginx)"
echo ""

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
DOMAIN="${1:-mnemo.local}"
APP_USER="www-data"
APP_PATH="/var/www/mnemo"
LOG_PATH="/var/log/mnemo"

# Vérifier si root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Ce script doit être lancé en root (sudo)${NC}"
    exit 1
fi

echo -e "${BLUE}1️⃣  Vérification des prérequis...${NC}"
# Vérifier Nginx
if ! command -v nginx &> /dev/null; then
    echo -e "${RED}❌ Nginx n'est pas installé${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Nginx trouvé${NC}"

# Vérifier PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP n'est pas installé${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP trouvé${NC}"

# Vérifier Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer n'est pas installé${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer trouvé${NC}"
echo ""

echo -e "${BLUE}2️⃣  Installation des dépendances PHP...${NC}"
cd $APP_PATH 2>/dev/null || cd .
composer install -q --no-dev --optimize-autoloader
echo -e "${GREEN}✓ Dépendances installées${NC}"
echo ""

echo -e "${BLUE}3️⃣  Configuration du fichier .env...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i "s/APP_ENV=local/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env
    sed -i "s/APP_URL=http:\/\/localhost/APP_URL=https:\/\/$DOMAIN/" .env
    php artisan key:generate -q
    echo -e "${GREEN}✓ Fichier .env créé et configuré${NC}"
else
    echo -e "${GREEN}✓ Fichier .env existe déjà${NC}"
fi
echo ""

echo -e "${BLUE}4️⃣  Création des dossiers et permissions...${NC}"
mkdir -p database storage/logs storage/app/public bootstrap/cache $LOG_PATH
chown -R $APP_USER:$APP_USER .
chmod -R 755 bootstrap/cache storage logs $LOG_PATH
chmod -R 775 storage/logs $LOG_PATH
echo -e "${GREEN}✓ Dossiers créés et permissions configurées${NC}"
echo ""

echo -e "${BLUE}5️⃣  Exécution des migrations...${NC}"
php artisan migrate --force -q
echo -e "${GREEN}✓ Migrations exécutées${NC}"
echo ""

echo -e "${BLUE}6️⃣  Optimization pour la production...${NC}"
php artisan config:cache -q
php artisan route:cache -q
php artisan view:cache -q
echo -e "${GREEN}✓ Cache optimisé${NC}"
echo ""

echo -e "${BLUE}7️⃣  Configuration Nginx...${NC}"
cat > /etc/nginx/sites-available/mnemo <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    root $APP_PATH/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log $LOG_PATH/access.log;
    error_log $LOG_PATH/error.log;
}
EOF

# Activer le site
ln -sf /etc/nginx/sites-available/mnemo /etc/nginx/sites-enabled/mnemo 2>/dev/null || true
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Tester la configuration Nginx
if ! nginx -t &>/dev/null; then
    echo -e "${RED}❌ Erreur de configuration Nginx${NC}"
    exit 1
fi

systemctl restart nginx
echo -e "${GREEN}✓ Nginx configuré et redémarré${NC}"
echo ""

echo -e "${GREEN}✨ Installation production complète !${NC}"
echo ""
echo -e "${BLUE}Informations:${NC}"
echo "  • Domaine: https://$DOMAIN"
echo "  • Chemin: $APP_PATH"
echo "  • Logs: $LOG_PATH"
echo "  • User: $APP_USER"
echo ""
echo -e "${BLUE}Prochaines étapes:${NC}"
echo "  1. Configurer un certificat SSL (Let's Encrypt):"
echo "     sudo certbot certonly --nginx -d $DOMAIN"
echo "  2. Créer le compte admin via le wizard:"
echo "     https://$DOMAIN/install"
echo ""
