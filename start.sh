#!/bin/bash

# Auto-installer for Mnemo on Linux/Mac

echo ""
echo "========================================"
echo "   Mnemo - Automatic Installation"
echo "========================================"
echo ""

# Check if git is installed
if ! command -v git &> /dev/null; then
    echo "ERROR: Git is not installed"
    exit 1
fi

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Clone if doesn't exist
if [ ! -d "$PROJECT_DIR/.git" ]; then
    echo "Cloning Mnemo repository..."
    git clone https://github.com/YoannFM-rascol/Mnemo.git "$PROJECT_DIR"
else
    echo "Updating repository..."
    cd "$PROJECT_DIR"
    git checkout claude/lucid-maxwell-OTvnN
    git pull origin claude/lucid-maxwell-OTvnN
fi

cd "$PROJECT_DIR"

# Clean up public folder
if [ -f "public/install.php" ]; then
    echo "Cleaning up..."
    rm -f "public/install.php"
fi

# Ensure install.php is at root
if [ ! -f "install.php" ]; then
    echo "ERROR: install.php not found at root!"
    exit 1
fi

echo ""
echo "========================================"
echo "   Starting PHP Development Server..."
echo "========================================"
echo ""

echo "Starting server on http://localhost"
echo ""
echo "Opening browser in 5 seconds..."
sleep 5

# Open browser
if command -v xdg-open &> /dev/null; then
    xdg-open "http://localhost/install.php" &
elif command -v open &> /dev/null; then
    open "http://localhost/install.php" &
fi

# Start PHP server on port 80
php artisan serve --host=0.0.0.0 --port=80
