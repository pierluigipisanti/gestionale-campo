# Guida all'installazione

Gestionale campo di accoglienza — pensato per l'uso **offline in emergenza** su un PC in
campo. Un solo PC fa da server; gli altri lo raggiungono in **rete locale** dal browser.

Database **SQLite**: un file, nessun server database da installare.

---

## Windows

### Requisiti
- **PHP 8.3 o superiore** con le estensioni `pdo_sqlite`, `zip`, `gd`, `mbstring` (tutte
  incluse nelle build standard di PHP per Windows).
- **Composer**.

Il modo più semplice per averli entrambi è installare **[Laragon](https://laragon.org)**
(porta PHP + Composer in un colpo solo). In alternativa: PHP da
[windows.php.net](https://windows.php.net/download) + Composer da
[getcomposer.org](https://getcomposer.org).

### Passi
1. Scarica/estrai il progetto in una cartella (es. `C:\gestionale-campo`).
2. Doppio clic su **`install.bat`** — installa le dipendenze, crea il database e i dati demo.
3. Doppio clic su **`start.bat`** — avvia il gestionale.
4. Sul PC server apri **http://localhost:8000**.
   Dagli altri PC in rete locale: **http://IP-DEL-PC:8000** (trovi l'IP con `ipconfig`).

### Verifica delle estensioni PHP (se `install.bat` dà errore)
Apri `php.ini` e assicurati che queste righe non abbiano il `;` davanti:
```
extension=pdo_sqlite
extension=zip
extension=gd
extension=mbstring
```

---

## Linux / macOS

### Requisiti
- **PHP 8.3+** con estensioni `sqlite3`, `zip`, `gd`, `mbstring`.
- **Composer**.

Su Debian/Ubuntu:
```bash
sudo apt install php php-sqlite3 php-zip php-gd php-mbstring php-xml composer
```

### Passi
```bash
cd gestionale-campo
./install.sh
php artisan serve --host=0.0.0.0 --port=8000
```
`install.sh` fa tutto (dipendenze, `.env`, chiave, database SQLite, migrazioni, dati demo).
Apri **http://localhost:8000** (o `http://IP-DEL-PC:8000` dagli altri PC in rete locale).

---

## Accesso dagli altri PC in rete

Il server ascolta su tutta la rete locale. Sul PC server apri `http://localhost:8000`;
dagli **altri PC** usa l'indirizzo IP del server: `http://192.168.x.x:8000`.
Non serve indovinarlo — `start.sh` (Linux) e `start.bat` (Windows) **stampano l'indirizzo
esatto** da usare all'avvio.

## Primo accesso

Utenti di default creati dal seed (**cambiali subito** da Configurazione → Utenti):

| Ruolo | Email | Password |
|---|---|---|
| Amministratore | `admin@campo.local` | `password` |
| Operatore | `operatore@campo.local` | `password` |

Per creare un amministratore con **credenziali tue** (consigliato):
```bash
php artisan crea:admin
```

## Dati dimostrativi (facoltativo)

Per provare l'app con tende, persone e automezzi di esempio:
```bash
php artisan db:seed --class=DemoSeeder
```

## Aggiornamenti

Per aggiornare il codice senza perdere i dati, **non** rifare l'installazione: applica solo
le nuove migrazioni.
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

## Backup

Il database è un unico file: **`database/database.sqlite`**. Copialo per fare un backup;
i loghi caricati stanno in `storage/app/public/loghi/`.

## PostgreSQL (opzionale)

Per usare PostgreSQL invece di SQLite, imposta i parametri `DB_*` in `.env` prima di
`php artisan migrate`. Le query sono portabili.
