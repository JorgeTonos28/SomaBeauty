@echo off
title Alice — Helper PR Codex (Git + Laravel)
setlocal ENABLEEXTENSIONS

REM --- Detectar rama principal: main o master ---
git rev-parse --verify main >nul 2>&1
if %ERRORLEVEL%==0 (
  set "MAIN=main"
) else (
  set "MAIN=master"
)

REM --- Preguntar número de PR ---
set PR=
set /p PR=Introduce el NUMERO del PR (solo el numero): 
if "%PR%"=="" (
  echo [ERROR] Debes introducir un numero de PR.
  pause
  exit /b
)

REM --- Preguntar si desea reiniciar la BD ---
choice /M "¿Reiniciar la BD con 'php artisan migrate:fresh --seed'?"
if errorlevel 2 (
  set "FRESH=0"
) else (
  set "FRESH=1"
)

echo.
echo ===== INICIANDO: PR #%PR% =====

REM --- Cambiar a rama principal ---
git checkout %MAIN%
if errorlevel 1 goto :fail

REM --- Fetch y checkout del PR ---
git fetch origin pull/%PR%/head
if errorlevel 1 goto :fail

git checkout -B codex-pr-%PR% FETCH_HEAD
if errorlevel 1 goto :fail

REM --- npm install y build (si aplica) ---
if exist package.json (
  call npm install || goto :fail
  call npm run build || goto :fail
)

REM --- Migraciones Laravel ---
if "%FRESH%"=="1" (
  php artisan migrate:fresh --seed || goto :fail
) else (
  php artisan migrate || goto :fail
  php artisan db:seed || goto :fail
)

REM --- Cachés Laravel ---
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM --- Levantar servidor Laravel ---
start "Laravel Dev Server" php artisan serve

echo.
echo ✅ PR #%PR% aplicado y servidor iniciado.
echo.
pause
exit /b

:fail
echo ❌ Ocurrió un error. Verifica los pasos anteriores.
pause
exit /b
