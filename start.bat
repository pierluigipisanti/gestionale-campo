@echo off
REM Avvia il Gestionale Campo. Doppio clic per accendere il server.
cd /d "%~dp0"
echo Gestionale Campo in avvio...
echo   Su questo PC:            http://localhost:8000
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
  echo   Dagli altri PC in rete: http://%%a:8000
)
echo Chiudi questa finestra per spegnere.
php artisan serve --host=0.0.0.0 --port=8000
