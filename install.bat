@echo off
REM Installazione offline del Gestionale Campo su Windows.
REM Requisiti: PHP 8.3+ (con pdo_sqlite) e Composer nel PATH.
setlocal
cd /d "%~dp0"

if exist "vendor" (
  echo ==^> Dipendenze gia incluse nel pacchetto
) else (
  echo ==^> Dipendenze PHP (composer)
  call composer install --no-dev --optimize-autoloader || goto :err
)

echo ==^> Configurazione (.env)
if not exist ".env" copy ".env.example" ".env" >nul
php artisan key:generate --force || goto :err

echo ==^> Database SQLite (un file, nessun server)
if not exist "database" mkdir "database"
if not exist "database\database.sqlite" type nul > "database\database.sqlite"
php artisan migrate:fresh --seed --force || goto :err

php artisan config:cache >nul 2>&1
php artisan route:cache >nul 2>&1

echo.
echo ==^> Fatto. Avvia con start.bat  (o: php artisan serve)
echo     Apri http://localhost:8000
echo.
echo     Accessi iniziali (cambiali da Configurazione ^> Utenti):
echo       admin@campo.local     / password
echo       operatore@campo.local / password
echo.
pause
goto :eof

:err
echo.
echo Errore durante l'installazione. Controlla che PHP e Composer siano installati.
pause
exit /b 1
