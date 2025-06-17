@echo off
REM ================================================
REM  Script: alice.bat
REM  Propósito: Actualizar y cambiar entre PRs Codex
REM  Autor: ChatGPT (Alicia)
REM ================================================

REM Preguntar al usuario el número de PR que quiere trabajar
set /p PR_NUM=Introduce el número del Pull Request (solo número): 

REM Obtener la rama actual
for /f "delims=" %%i in ('git rev-parse --abbrev-ref HEAD') do set CURRENT_BRANCH=%%i

echo Rama actual: %CURRENT_BRANCH%
echo PR objetivo: codex-pr-%PR_NUM%
echo.

REM Construir nombre de la rama destino
set TARGET_BRANCH=codex-pr-%PR_NUM%

IF /I "%CURRENT_BRANCH%"=="%TARGET_BRANCH%" (
    echo Ya estas en la rama %TARGET_BRANCH%. Actualizando...
    REM Calcular PR anterior
    set /a PREV_PR=%PR_NUM%-1
    set PREV_BRANCH=codex-pr-%PREV_PR%
    echo Cambiando temporalmente a %PREV_BRANCH%...
    git checkout %PREV_BRANCH%
    echo.
    echo Trayendo cambios del PR %PR_NUM%...
    git fetch origin pull/%PR_NUM%/head:%TARGET_BRANCH%
    git checkout %TARGET_BRANCH%
) ELSE (
    echo Trayendo cambios del PR %PR_NUM%...
    git fetch origin pull/%PR_NUM%/head:%TARGET_BRANCH%
    git checkout %TARGET_BRANCH%
)

echo.

REM Preguntar si desea correr migraciones
set /p RUN_MIGRATE=¿Deseas ejecutar las migraciones (php artisan migrate)? [s/n]: 
if /I "%RUN_MIGRATE%"=="s" (
    echo Ejecutando migraciones...
    php artisan migrate
)

REM Preguntar si desea correr seeders
set /p RUN_SEED=¿Deseas ejecutar los seeders (php artisan db:seed)? [s/n]: 
if /I "%RUN_SEED%"=="s" (
    echo Ejecutando seeders...
    php artisan db:seed
)

echo.
echo ===== Refrescando cachés =====
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo ===== Instalando dependencias FrontEnd =====
npm install
npm run build

echo.
echo ===== Proceso completado. Ejecuta 'php artisan serve' si deseas levantar el servidor local. =====
