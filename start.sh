#!/usr/bin/env bash
# Avvia il Gestionale Campo e mostra a quale indirizzo lo raggiungono gli altri PC.
cd "$(dirname "$0")"

echo "Gestionale Campo in avvio…"
echo "  Su questo PC:            http://localhost:8000"
for ip in $(hostname -I 2>/dev/null); do
  echo "  Dagli altri PC in rete:  http://$ip:8000"
done
echo "Premi Ctrl+C per spegnere."
php artisan serve --host=0.0.0.0 --port=8000
