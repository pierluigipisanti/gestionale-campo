#!/usr/bin/env bash
# Installazione offline del Gestionale Campo su una macchina in emergenza.
# Requisiti: PHP 8.3+ con estensione sqlite3, Composer.
set -euo pipefail
cd "$(dirname "$0")"

# Installa da sé PHP + estensioni se mancano (Debian/Ubuntu)
if ! command -v php >/dev/null 2>&1; then
  echo "==> PHP non trovato: provo a installarlo…"
  if command -v apt-get >/dev/null 2>&1; then
    sudo apt-get update -y && sudo apt-get install -y php php-sqlite3 php-zip php-gd php-mbstring php-xml
  else
    echo "Installa PHP 8.3+ (con sqlite3, zip, gd, mbstring) e riprova."; exit 1
  fi
fi
if ! command -v composer >/dev/null 2>&1 && [ ! -d vendor ]; then
  echo "==> Composer non trovato: provo a installarlo…"
  command -v apt-get >/dev/null 2>&1 && sudo apt-get install -y composer || true
fi

if [ -d vendor ]; then
  echo "==> Dipendenze già incluse nel pacchetto (vendor presente)"
else
  echo "==> Dipendenze PHP (composer)"
  composer install --no-dev --optimize-autoloader
fi

echo "==> Configurazione (.env)"
[ -f .env ] || cp .env.example .env
php artisan key:generate --force

echo "==> Database SQLite (un file, nessun server)"
mkdir -p database
touch database/database.sqlite
php artisan migrate:fresh --seed --force

php artisan config:cache >/dev/null 2>&1 || true
php artisan route:cache >/dev/null 2>&1 || true

cat <<'MSG'

==> Fatto.
Avvia il gestionale con:
    ./start.sh          (mostra anche l'indirizzo per gli altri PC in rete)

Accessi iniziali (cambiali subito da Configurazione > Utenti,
oppure crea il tuo admin con:  php artisan crea:admin):
    admin@campo.local     / password   (amministratore)
    operatore@campo.local / password   (operatore)

Per caricare dati dimostrativi (tende, persone, automezzi di prova):
    php artisan db:seed --class=DemoSeeder
MSG
