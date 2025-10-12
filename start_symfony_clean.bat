@echo off
title 🚀 Démarrage propre du serveur Symfony
color 0A

echo.
echo ========================================
echo  🧹 Nettoyage des processus PHP et Symfony
echo ========================================
echo.

:: Tuer les anciens processus PHP et Symfony
taskkill /F /IM symfony.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1

echo Ancien serveur arrêté.
timeout /t 1 >nul

echo.
echo ========================================
echo  🗑️  Suppression du cache Symfony CLI
echo ========================================
echo.

:: Supprime le dossier de logs Symfony CLI
set "SYM_DIR=%USERPROFILE%\.symfony5"
if exist "%SYM_DIR%" (
    rmdir /s /q "%SYM_DIR%"
    echo Dossier %SYM_DIR% supprimé.
) else (
    echo Aucun dossier .symfony5 à supprimer.
)

timeout /t 1 >nul

echo.
echo ========================================
echo  🚀 Lancement du serveur Symfony
echo ========================================
echo.

cd /d "%~dp0"
symfony serve -d

echo.
echo Le serveur Symfony a été relancé avec succès !
echo Ouvre ton navigateur sur http://127.0.0.1:8000
echo.
pause
