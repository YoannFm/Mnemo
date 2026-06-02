@echo off
setlocal enabledelayedexpansion

REM Auto-installer for Mnemo on Windows with Laragon

echo.
echo ========================================
echo    Mnemo - Automatic Installation
echo ========================================
echo.

REM Check if git is installed
where git >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Git is not installed or not in PATH
    pause
    exit /b 1
)

set PROJECT_DIR=%~dp0

REM Clone if doesn't exist
if not exist "%PROJECT_DIR%.git" (
    echo Cloning Mnemo repository...
    git clone https://github.com/YoannFM-rascol/Mnemo.git "%PROJECT_DIR%"
) else (
    echo Updating repository...
    cd /d "%PROJECT_DIR%"
    git checkout claude/lucid-maxwell-OTvnN
    git pull origin claude/lucid-maxwell-OTvnN
)

cd /d "%PROJECT_DIR%"

REM Clean up public folder
if exist "public\install.php" (
    echo Cleaning up...
    del "public\install.php" 2>nul
)

REM Ensure install.php is at root
if not exist "install.php" (
    echo ERROR: install.php not found at root!
    pause
    exit /b 1
)

echo.
echo ========================================
echo    Starting PHP Development Server...
echo ========================================
echo.

REM Find PHP executable
set PHP_EXE=C:\laragon\bin\php\php-cgi.exe

if not exist "%PHP_EXE%" (
    for /f "delims=" %%A in ('where php') do set PHP_EXE=%%A
)

if "!PHP_EXE!"=="" (
    echo ERROR: PHP not found. Please install PHP or Laragon.
    pause
    exit /b 1
)

echo Starting server on http://localhost:8000
echo.
echo Opening browser in 5 seconds...
timeout /t 5 /nobreak

REM Open browser
start http://localhost:8000/install.php

REM Start PHP server
php artisan serve

pause
