@echo off
title Dental Clinic - Local Dev Server
color 0A

echo ============================================
echo   Dental Clinic Management System
echo   Local Development Startup
echo ============================================
echo.

:: -- Config ------------------------------------------------------------------
set PROJECT_DIR=%~dp0
:: ----------------------------------------------------------------------------

:: 1. Start Laravel artisan serve
echo [1/2] Starting Laravel dev server at http://127.0.0.1:8000 ...
start "Laravel Artisan Serve" cmd /k "cd /d "%PROJECT_DIR%" && php artisan serve"
echo.

:: 2. Start Vite dev server
echo [2/2] Starting Vite asset server...
start "Vite Dev Server" cmd /k "cd /d "%PROJECT_DIR%" && npm run dev"
echo.

:: 3. Open browser
echo Opening browser...
timeout /t 3 /nobreak >nul
start "" http://127.0.0.1:8000

echo.
echo ============================================
echo   All services are running!
echo.
echo   App URL  : http://127.0.0.1:8000
echo   Database : dental_clinic_db (MySQL @ 3306)
echo.
echo   Close the Laravel and Vite windows to stop
echo   the dev servers when you are done.
echo ============================================
echo.
pause
