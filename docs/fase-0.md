# Fase 0 — Perimetro e decisioni

L'[analisi iniziale](analisi-tecnica-iniziale.md) descrive la visione ampia (9 moduli,
18 entità, 4 sprint). Quella è un buon prodotto v1, non un MVP. La **fase 0** è il
sottoinsieme che valida il prodotto: la spina dorsale, prima delle costole.

## Cosa costruiamo in fase 0

Solo il loop centrale — quello che oggi nessuno fa bene e che giustifica il prodotto:

```
persona (ospite/volontario/sanitario) → check-in → assegnazione posto → presenze → report
```

- anagrafica **persone alloggiate** (ospiti, volontari, sanitari...) + nuclei familiari
- **categorie di persona gestite dall'admin** (lookup: aggiunge VVF, ENEL, Polizia... da UI)
- struttura campo gerarchica (settore → fila → tenda → posto)
- check-in / check-out / trasferimento con storico movimenti
- **documenti al check-in**: CIE, passaporto, tessera volontario (emessa dall'app), manuale
- occupazione posti
- presenze giornaliere
- **registro varco / accessi** (chi transita senza alloggiare) — probabilmente la funzione più usata
- report PDF/CSV
- 2 ruoli: `admin` / `operatore`
- visualizzazione posti come **griglia**, non mappa

**Chi alloggia vs chi transita** — due concetti separati, entrambi in fase 0:
- **alloggia** (ospiti, volontari, sanitari...): occupa un posto, ha movimenti e
  presenze → tabella `persone`.
- **transita** (VVF di passaggio, ENEL, polizia, autorità, fornitori, stampa): log
  entrata/uscita, niente posto → tabella `accessi`.

Condividono lo stesso vocabolario `categorie_persona` (un VVF può dormire nel campo
*o* passare per il giorno): una lookup sola, gestita dall'admin.

## Cosa rimandiamo (fase 1.5+)

Satelliti utili ma non essenziali a validare: se sparissero, il campo funzionerebbe.

- attributi specifici dei volontari (ruolo, ente, disponibilità, turni) — la *persona*
  volontario esiste già in fase 0; i suoi campi operativi arrivano dopo
- materiali essenziali
- pasti e bisogni speciali
- diario eventi
- **dashboard aggregata multi-campo** (lo schema è pronto, il cruscotto no)
- **mappa grafica** con strade disegnate e drag-and-drop

## Decisioni

Registro leggero delle scelte prese e del perché. Aggiornare quando cambiano.

### D1 — PHP/Laravel
Linguaggio noto a molti → adozione e contributi dalla community. Laravel copre
nativamente auth, ruoli, PDF, audit, validazione: niente pacchetti per ciò che il
framework già fa.

### D2 — Single-tenant, ma schema multi-campo pronto
Un solo campo all'avvio. Però `enti` (1 riga) e `campi` esistono già, e ogni entità
operativa porta `campo_id`. I report filtrano già per campo.
**Costo oggi:** due colonne e un `where`. La dashboard "vista dall'alto" cross-campo
si costruisce quando arriva il secondo campo, non il primo.

### D3 — Settore e fila come colonne, non tabelle
La gerarchia dell'attendamento (settore, fila/strada) è modellata come colonne su
`tende`. Sono etichette di raggruppamento senza dati propri. Diventano tabelle solo
il giorno in cui un settore dovrà portare attributi suoi.

### D4 — `posti` come righe
È l'unico livello con vera granularità: il check-in assegna al posto, lo stato
"inagibile" è per-posto, l'occupazione si conta da qui.

### D5 — Posizione corrente denormalizzata sulla persona
`persone.posto_id` e `persone.stato` sono la posizione corrente, aggiornata nella
**stessa transazione** che inserisce il movimento. `movimenti` resta la verità
storica/audit. Evita di ricalcolare "l'ultimo movimento" a ogni lista presenze.

### D7 — Una tabella `persone`, categoria come lookup gestita dall'admin
Ospiti, volontari e sanitari alloggiano tutti nel campo e condividono la stessa
macchina (posto + movimenti + presenze). Stanno in un'unica tabella `persone`.
La categoria non è né tre tabelle né una stringa libera, ma un **FK a una lookup
`categorie_persona` che l'admin gestisce da UI**: aggiunge VVF, ENEL, Polizia,
Carabinieri... come righe, senza migration né uno sviluppatore. La stringa libera
darebbe "VVF"/"V.V.F."/"Vigili del Fuoco" incoerenti; la lookup dà un vocabolario
unico e coerente. Le categorie non si cancellano se in uso (`restrictOnDelete`): si
disattivano (`attiva=false`).
Movimenti e presenze puntano a `persona_id`, così il check-in funziona per tutte le
categorie senza logica in più. Gli attributi specifici (nucleo per gli ospiti,
ruolo/ente per i volontari) sono colonne nullable o satelliti aggiunti dopo — non un
supertipo `Persona` astratto.
La segregazione delle tende per categoria si esprime col `settore`; l'eventuale
*enforcement* (un volontario solo in tenda volontari) è una validazione da aggiungere
se servirà, non ora.

### D8 — Registro varco (`accessi`) separato da `persone`
Chi transita senza dormire (VVF di passaggio, ENEL, polizia, fornitori...) **non** è
una `persona` alloggiata: niente posto, niente presenze. È una tabella `accessi` a
sé, log entrata/uscita. Overloadare `persone` con posto/movimenti nullable sarebbe
lo stesso errore che D7 evita al contrario.
**Una riga per visita**, `uscita_at` nullable: `uscita_at IS NULL` = persona dentro
il campo ora — la domanda più frequente al varco (sicurezza, evacuazione), risolta
con un solo indice. `nominativo` a **testo libero** per rapidità: chi transita spesso
non è pre-registrato. Categoria dallo stesso `categorie_persona` di D7.
Non colleghiamo gli accessi a un'anagrafica persistente: se servirà una rubrica dei
visitatori ricorrenti si aggiunge dopo. La priorità è la velocità d'inserimento.

### D9 — Documento al check-in: stringa, non lookup; tessera = persona + numero
Il documento presentato (CIE, passaporto, tessera volontario, manuale) è **identità
della persona** → campi su `persone` (`codice_fiscale`, `documento_tipo`,
`documento_numero`), catturati al check-in.
`documento_tipo` è una **stringa**, non una lookup admin come le categorie: il set è
legalmente stabile e "manuale" è l'escape hatch. Le categorie sono aperte (emergenza),
i documenti no → niente terza CRUD.
La **tessera volontario "creabile dall'app"** non è una tabella a sé: è una
`persona` (categoria volontario) con `documento_tipo=tessera_volontario` e un
`documento_numero` generato. Ha già nome+cognome+CF sulla persona. L'emissione/stampa
è un'azione piccola, aggiunta quando serve — non un supertipo `Tessera`.

### D6 — Griglia prima della mappa
Il check-in ha bisogno di sapere quale posto è libero, non di una mappa disegnata.
La struttura dati incarna le buone pratiche di attendamento; la visualizzazione parte
da una griglia CSS colorata per occupazione. La mappa geografica è vanità finché un
campo reale non la chiede.

## Modello dati (fase 0)

```
categorie_persona  (lookup gestita dall'admin: Ospite, VVF, ENEL, Polizia...)
   ▲              ▲
   │              │
enti (1 riga) → campi → tende (settore/fila = colonne) → posti
                  ├─ nuclei
                  ├─ persone ──< movimenti   (audit storico immutabile)
                  │    │  categoria_id → categorie_persona
                  │    │  posto_id + stato = posizione corrente denormalizzata
                  │    └──< presenze         (1 riga per persona per giorno)
                  └─ accessi                 (registro varco: transito, no posto)
                       categoria_id → categorie_persona
                       uscita_at NULL = dentro il campo ora
```

Migration in [`../database/migrations/`](../database/migrations/).
`users` è quella nativa di Laravel + colonna `ruolo` (`admin`/`operatore`); i permessi
sono un Gate `admin`, non un sistema RBAC.

## Fatto

Progetto Laravel 13 reale, schema su PostgreSQL, logica + UI verificate (34 test verdi).

**Logica di dominio** (azioni in `app/Actions/`, transazionali con lock):
- **Check-in** — [`EseguiCheckIn`](../app/Actions/EseguiCheckIn.php): movimento +
  `persone.posto_id`/`stato` + `posti.stato`, lock sul posto, cattura documento.
- **Check-out** — [`EseguiCheckOut`](../app/Actions/EseguiCheckOut.php): chiude permanenza,
  libera il posto, mantiene lo storico.
- **Trasferimento** — [`EseguiTrasferimento`](../app/Actions/EseguiTrasferimento.php):
  sposta la persona, lock su entrambi i posti in ordine di id (no deadlock).
- **Check-in nucleo** — [`CheckInNucleo`](../app/Actions/CheckInNucleo.php): crea il nucleo
  e assegna tutti i membri a posti liberi della **stessa tenda**, tutto o niente.
- **Crea tende** — [`CreaTende`](../app/Actions/CreaTende.php): N tende × M posti in blocco,
  numerazione codici continua per settore/fila.
- **Registro accessi** — [`RegistraEntrata`](../app/Actions/RegistraEntrata.php) /
  [`ChiudiUscita`](../app/Actions/ChiudiUscita.php): entrata (insert), uscita (update
  condizionale atomico). Scope `Accesso::dentro()` = chi è nel campo ora.

**UI** (Blade su layout condiviso [`layouts/app`](../resources/views/layouts/app.blade.php)).
Nav: Dashboard · Varco · Posti · Presenze · Struttura · **Utility ▾** (Report) ·
**Configurazione ▾** (admin: Campo, Categorie, Loghi, Utenti). Ricerca sempre in topbar.
- **Dashboard** [`/`](../resources/views/dashboard.blade.php): numeri a colpo d'occhio
  (presenti, posti liberi/occupati/inagibili, al varco, tende) + ricerca + azioni rapide.
- **Ricerca** [`/cerca`](../resources/views/cerca.blade.php): per cognome/nome/CF su
  alloggiati **e** accessi al varco ("è al campo il sig. X?").
- **Varco** [`/varco`](../resources/views/varco.blade.php): entrata con **cognome/nome/CF
  separati** + lettura documento (CIE/tessera); roster "chi è nel campo ora" con uscita.
- **Posti** [`/posti`](../resources/views/posti/index.blade.php): griglia occupazione per
  settore/tenda (verde/rosso/grigio, celle ad altezza fissa); da posto libero → check-in
  con **lettura documento** (scanner: CIE via MRZ TD1 → cognome/nome/data/sesso/CF;
  tessera sanitaria → CF+data+sesso); da posto occupato → dettaglio con **check-out** e
  **trasferimento**; link **check-in nucleo**.
- **Presenze** [`/presenze`](../resources/views/presenze/index.blade.php): chiusura
  giornaliera — per data, consolida lo stato (presente/assente/uscito) di chi è in forza
  in una fotografia in `presenze`. Idempotente (riconferma aggiorna, non duplica).
- **Struttura** [`/struttura`](../resources/views/struttura/index.blade.php): crea tende
  (N × M posti) e **modifica** ([`struttura.edit`](../resources/views/struttura/edit.blade.php)):
  metadati tenda + posti (aggiungi, rimuovi, **segna inagibile**). Elimina tenda: admin.
- **Campo** [`/campo`](../resources/views/campo/edit.blade.php): solo admin, modifica nome
  del campo e dati ente.
- **Utenti** [`/utenti`](../resources/views/utenti/index.blade.php): solo admin, elenco +
  creazione utenti.
- **Categorie** [`/categorie`](../resources/views/categorie/index.blade.php): solo admin, la
  lookup di D7 gestita da UI — aggiungi, **modifica inline**, disattiva/riattiva, **elimina**
  se inutilizzata, con conteggio d'uso.
- **Loghi** [`/loghi`](../resources/views/loghi/index.blade.php): solo admin, upload loghi
  (ente/comune/PC) per report e tesserini; serviti da route dedicata (no `storage:link`).
- **Report** [`/report`](../resources/views/report/index.blade.php): PDF (dompdf, loghi in
  intestazione) e CSV (BOM + `;` per Excel italiano) di **presenze**, **occupazione posti**,
  **accessi al varco**. [`ReportController`](../app/Http/Controllers/ReportController.php).

**Autenticazione** (a mano, senza Breeze → niente build npm):
- login/logout ([`auth/login`](../resources/views/auth/login.blade.php)), tutte le rotte
  app dietro `auth`, throttle sul login.
- 2 ruoli su `users.ruolo` (`admin`/`operatore`), Gate `admin` (gestione utenti,
  eliminazione tende). `operatore_id` ora popolato sulle azioni.
- Seed: `admin@campo.local` / `operatore@campo.local`, password `password` (da cambiare).

**Test (93):** CheckIn, Movimenti, Accessi, CreaTende, Nucleo, Presenze, Auth, Categorie,
Loghi, Report, Campo, Dashboard(+ricerca), Utenti, Persone + web di Varco, Posti,
Struttura, Trasferimento. **CRUD completa** (create/modifica/elimina) su categorie,
tende/posti, campo/ente, **utenti** (+password, guardie ultimo-admin/self), **persone**
(scheda modificabile, elimina solo senza storico), **loghi** (rinomina), **accessi**.
Modifica raggiungibile da ricerca, dettaglio posto e roster varco.

Dipendenza aggiunta: `barryvdh/laravel-dompdf` (PDF pure-PHP, nessun binario).

## Estensioni (post fase-0)

Modulo **Automezzi + QR + Stampe** (in corso, ordine A→B→C):
- **A (fatto):** entità `automezzi` (registro CRUD, targa unica/campo, referente+cellulare,
  stato dentro/fuori) + `transiti_automezzo` (log). Varco a due tab **Persone/Automezzi**
  con entrata/uscita espliciti. Tile automezzi in dashboard. **100 test.**
- **B (prossimo):** QR stampabile per persone/automezzi; lo scanner QR (tastiera, come
  barcode/MRZ) fa entrata/uscita rapida. Libreria PHP offline.
- **C:** menu **Stampe** — QR, cartello tenda A4, foglio referenti per bacheca
  (referente = flag + ruolo sulla persona).

Rimane anche: emissione tessera volontario, cambio password self, filtri/date sui report.

Aggiunto il campo **cellulare** a persone (visibile) e accessi.
