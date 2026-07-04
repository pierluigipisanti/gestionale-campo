# Roadmap

Indicativa e guidata dal campo: il progetto è in **beta** e le priorità si spostano in base
al riscontro di gruppi comunali e associazioni che lo provano davvero. L'obiettivo resta
stretto — presidiare bene **persone · alloggi · presenze · volontari · materiali · report** —
senza inseguire le suite di emergenza su GIS e sala operativa.

Legenda: 🟢 near (prossimi) · 🟡 mid · ⚪ later.

## 🟢 Near — completano ciò che l'app già promette
- **Tessere volontari stampabili** — genera la tessera (cognome, nome, CF) con **barcode/QR**,
  così al varco basta scansionarla. Chiude il cerchio del check-in per scansione.
- **Stampa "Orario campo"** — placeholder già presente in Utility → Stampe.
- **Stampa "Referenti campo"** — placeholder già presente in Utility → Stampe.
- **QR/barcode sui documenti generati** (cartellini, cartelli) per l'uscita rapida.

## 🟡 Mid — nuovi moduli del blocco operativo
- **Turni volontari** — disponibilità → turno assegnato, con la stampa "Turni" (terzo placeholder).
- **Materiali / magazzino** essenziale — brande, coperte, kit: carico/scarico e giacenza.
- **Pasti / esigenze speciali** — conteggio pasti per turno e diete (allergie già sulla persona).
- **Diario eventi** — log operativo del turno (consegne, note, eventi).

## ⚪ Later — richiedono cambi strutturali
- **Resilienza offline "leggera"** — autosave dei form, coda di retry, messaggi di
  sincronizzazione. Oggi l'app è offline *come deploy*, ma non resiliente a rete debole nel browser.
- **Multi-campo** — oggi è single-campo; il controllo di accesso per-campo è latente e va
  aggiunto insieme al multi-tenant.

## Fuori scope (per scelta)
GIS avanzato, integrazioni profonde con COC/COM, workflow approvativi, app per il cittadino,
motori di ottimizzazione turni: allontanano dal prodotto snello e lo avvicinano alle suite
già esistenti. Vedi [analisi-tecnica-iniziale.md](analisi-tecnica-iniziale.md).

## Fatto — v1.0.0
Varco persone/automezzi, tende e posti, presenze, report PDF/CSV, cartelli tenda, import Excel
volontari/automezzi, area admin (campo, categorie, loghi, utenti), autenticazione a 2 ruoli.
Storico completo in [CHANGELOG.md](../CHANGELOG.md).
