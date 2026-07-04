# Changelog

Tutte le modifiche rilevanti sono documentate qui.
Formato: [Keep a Changelog](https://keepachangelog.com/it/1.1.0/) · Versioni: [SemVer](https://semver.org/lang/it/).

## [1.0.0] — 2026-07-04

Prima release pubblica.

### Aggiunto
- **Varco** persone e automezzi: entrata/uscita per scansione documento (CF/MRZ/targa),
  con autocomplete e riconoscimento dal codice fiscale.
- **Tende**: check-in per ricerca, check-out, trasferimento, check-in nucleo; griglia
  occupazione posti (tende da 6 e da 8).
- **Presenze**: stato derivato automaticamente dalle entrate/uscite; consolidamento giornaliero.
- **Report** PDF/CSV (presenze, posti, accessi) e **cartelli tenda A4** da affiggere.
- **Import** Excel di volontari e automezzi con template scaricabili.
- **Area admin**: campo/ente, categorie, loghi (visibilità sulle stampe, ordine, grandezza), utenti.
- **Autenticazione** con due ruoli (admin/operatore); comando `php artisan crea:admin`.
- **Installazione offline**: SQLite di default, installer Linux/Windows, avvio con indirizzo
  di rete mostrato; seeder demo separato (`DemoSeeder`).

### Sicurezza
- Escaping delle liste autocomplete (nessun `innerHTML` con dati utente).
- Neutralizzazione della CSV formula injection negli export.

### Licenza
- Rilasciato con licenza CC BY-NC-SA 4.0.

[1.0.0]: https://github.com/pierluigipisanti/gestionale-campo/releases/tag/v1.0.0
