# Analisi tecnica della prima fase — Gestionale campo di accoglienza per Protezione Civile

## Obiettivo della prima fase

La prima fase ha l'obiettivo di realizzare un **MVP operativo** per la segreteria di un campo di accoglienza, focalizzato sulle attività che oggi vengono spesso gestite con fogli, moduli cartacei, comunicazioni destrutturate e strumenti eterogenei.[cite:5][cite:29] Il perimetro deve restare volutamente stretto: anagrafiche ospiti, check-in/check-out, assegnazione posti, presenze, volontari base, materiali essenziali, pasti/esigenze speciali, diario eventi e reportistica.[cite:5][cite:29]

Il razionale tecnico è coerente con il quadro emerso dalla ricerca: il software specifico per la gestione del **campo accoglienza** appare meno presidiato rispetto alle piattaforme più ampie di Protezione Civile orientate a sale operative, GIS, segnalazioni e coordinamento territoriale.[cite:19][cite:58] Questo rende sensato partire con un prodotto verticale, semplice e ad alta usabilità, invece di tentare subito una piattaforma generalista di command & control.[cite:2][cite:58]

## Obiettivi funzionali

La prima release deve consentire a un Comune, a un'associazione o a una struttura di coordinamento di fare cinque cose bene:

- registrare rapidamente ospiti e nuclei familiari;[cite:5]
- sapere in ogni momento chi è presente nel campo e dove è allocato;[cite:29]
- coordinare il minimo indispensabile di volontari, turni e materiali;[cite:29][cite:58]
- produrre report stampabili ed esportabili;[cite:58]
- mantenere uno storico affidabile degli eventi e delle operazioni del campo.[cite:58][cite:63]

L'MVP non deve includere nella prima fase GIS avanzato, integrazioni profonde con COC/COM, workflow approvativi complessi, app cittadino, automazioni intelligenti o motori di ottimizzazione dei turni, perché questi elementi aumentano fortemente la complessità architetturale e avvicinano il prodotto alle suite emergenza già presenti sul mercato.[cite:2][cite:58][cite:3]

## Perimetro della prima fase

### Moduli inclusi

| Modulo | Contenuto della prima fase | Motivazione |
|---|---|---|
| Ospiti e nuclei familiari | Anagrafica persona, nucleo, contatti, vulnerabilità, allergie, note | Funzione centrale per la segreteria campo [cite:5] |
| Check-in / check-out / trasferimenti | Ingresso, uscita, cambio posto, storico movimenti | Necessario per controllo operativo e conteggi aggiornati [cite:29] |
| Allocazione posti | Tenda, modulo, camerata, posto letto, stato occupazione | Permette gestione capacità e assegnazione ordinata |
| Presenze giornaliere | Presenti, assenti temporanei, rientri | Utile per sicurezza, pasti e report |
| Volontari base | Anagrafica, ruolo, disponibilità, turni semplici | Blocco operativo minimo coerente con i sistemi emergenza [cite:58][cite:2] |
| Materiali essenziali | Inventario base, quantità, stato, assegnazione | Riduce gestione manuale delle risorse [cite:2] |
| Pasti e bisogni speciali | Conteggi pasti, diete, allergie, categorie fragili | Alto valore pratico sul campo |
| Diario eventi | Registro cronologico di eventi, decisioni, problemi | Tracciabilità operativa e memoria del campo [cite:58][cite:63] |
| Reportistica | PDF e CSV per ospiti, presenze, posti, volontari, materiali | Requisito implicito molto forte nel contesto pubblico [cite:58] |

### Moduli esclusi

| Modulo | Motivo di esclusione dalla prima fase |
|---|---|
| GIS avanzato e cartografia territoriale | Dominio già coperto da piattaforme dedicate e complessità elevata [cite:58][cite:3] |
| Integrazione con sale operative / COC / COM | Richiede processi e interfacce più ampie del perimetro campo [cite:2][cite:58] |
| Motore turni avanzato | Meglio partire con gestione semplice prima di introdurre algoritmi |
| Offline-first completo | Complessità tecnica elevata; nella prima fase basta resilienza leggera |
| App mobile nativa | Una web app responsive è più veloce da validare |
| Firma digitale avanzata / fascicolo documentale completo | Non essenziale per validazione iniziale |
| Portale cittadino / notifiche massicce | Allarga troppo il prodotto rispetto al bisogno principale |

## Attori e casi d'uso

### Attori principali

- **Amministratore ente**: configura campo, utenti, ruoli e parametri di base.
- **Segreteria campo**: gestisce ospiti, nuclei, ingressi, uscite, trasferimenti, presenze e report.
- **Responsabile logistica**: aggiorna posti disponibili, materiali, scorte essenziali e assegnazioni.
- **Coordinatore volontari**: gestisce volontari, ruoli, disponibilità e turni semplici.
- **Lettura/consultazione**: consulta dashboard e report senza modifica dei dati.

### Casi d'uso prioritari

1. Registrazione rapida di un nuovo ospite o nucleo familiare.
2. Check-in con assegnazione a tenda/modulo/posto letto.
3. Trasferimento interno da un alloggio a un altro.
4. Check-out con storicizzazione dei movimenti.
5. Chiusura giornaliera delle presenze.
6. Conteggio pasti e gestione esigenze alimentari.
7. Inserimento volontario e assegnazione turno.
8. Registrazione entrata/uscita materiali essenziali.
9. Produzione report giornaliero del campo.
10. Scrittura evento nel diario operativo.

## Requisiti funzionali dettagliati

### 1. Gestione ospiti

Il sistema deve consentire inserimento manuale rapido e modifica delle anagrafiche, con ricerca testuale veloce per nome, nucleo, contatto e identificativo interno. Ogni ospite deve poter essere collegato a un nucleo familiare e a uno stato operativo, ad esempio pre-registrato, presente, temporaneamente assente, trasferito o dimesso.

Campi minimi consigliati:

- nome e cognome;
- data di nascita;
- sesso/genere amministrativo se richiesto dall'ente;
- telefono;
- nucleo familiare;
- comune/provenienza;
- fragilità o note sanitarie essenziali;
- allergie e dieta;
- note libere;
- stato attuale;
- data e ora di ultimo movimento.

### 2. Gestione nuclei familiari

Il nucleo è un'entità autonoma, non solo un campo testuale. Questo permette di assegnare insieme più persone, mantenere coerenza nei trasferimenti e produrre report per famiglia, non solo per individuo.

### 3. Check-in e check-out

Il check-in deve essere progettato come procedura guidata composta da pochi passaggi: ricerca o creazione anagrafica, conferma dati minimi, scelta alloggio, eventuali esigenze speciali, conferma finale. Il check-out deve chiudere la permanenza, liberare il posto e mantenere lo storico, senza cancellare dati.

### 4. Allocazione posti

La struttura del campo deve essere modellata in modo semplice ma rigoroso:

- campo;
- area/settore opzionale;
- unità abitativa, ad esempio tenda, modulo, camerata;
- posto letto o capacità complessiva.

Per la prima fase è preferibile un sistema di allocazione **manuale assistita**, non automatica. L'operatore deve vedere disponibilità residua, occupanti attuali e principali vincoli pratici, per esempio nucleo da tenere insieme o posto non adatto a persone fragili.

### 5. Presenze giornaliere

Ogni giorno deve essere possibile consolidare lo stato delle presenze, distinguendo almeno tra presenti, assenti temporanei, usciti e trasferiti. Questo dato è fondamentale per report, sicurezza e pianificazione dei pasti.

### 6. Volontari e turni

Per la prima fase il modulo volontari deve restare semplice. Non serve un motore di ottimizzazione, ma una gestione affidabile di anagrafica, ruolo, disponibilità, turno assegnato, recapiti e note operative. La complessità del workforce management va rinviata a fasi successive.

### 7. Materiali essenziali

Il sistema deve coprire l'inventario minimo di campo:

- categoria materiale;
- descrizione;
- quantità disponibile;
- quantità assegnata;
- stato;
- posizione/area;
- note.

L'obiettivo non è costruire un WMS o ERP logistico, ma dare visibilità alle risorse realmente usate durante la vita del campo.

### 8. Pasti e bisogni speciali

Il sistema deve consentire conteggi giornalieri dei pasti e segnalare esigenze particolari: intolleranze, allergie, dieta speciale, soggetti fragili. Questo blocco è piccolo ma ad altissimo impatto operativo.

### 9. Diario eventi

Il diario eventi deve essere un registro cronologico strutturato con timestamp, autore, categoria, descrizione e collegamenti opzionali a ospiti, volontari, materiali o aree del campo. È importante che questo modulo sia semplice da usare, perché il suo valore dipende dalla continuità di compilazione.[cite:58][cite:63]

### 10. Reportistica

La prima fase deve prevedere almeno questi report:

- presenze correnti;
- assegnazione posti;
- ospiti per nucleo;
- volontari attivi;
- riepilogo materiali essenziali;
- pasti e bisogni speciali;
- diario eventi giornaliero.

Gli export minimi consigliati sono **PDF** per stampa/condivisione e **CSV** per rielaborazione locale. Nel contesto Protezione Civile e PA la reportistica è un requisito quasi strutturale, non un extra.[cite:58][cite:2]

## Requisiti non funzionali

### Usabilità

La web app deve essere progettata per operatori sotto pressione, su notebook economici o tablet, con apprendimento rapido. Le schermate devono ridurre i campi obbligatori, evitare navigazione dispersiva e favorire azioni ripetitive veloci.

### Prestazioni

Le operazioni comuni, come ricerca anagrafica, apertura scheda, registrazione check-in e generazione lista presenze, devono rispondere in modo rapido anche con dataset medio-piccoli. L'obiettivo pratico della prima fase è sostenere un singolo campo o pochi campi con carico limitato.

### Affidabilità e audit

Ogni modifica rilevante deve lasciare traccia minima: autore, timestamp, tipo di operazione. Questo è importante sia per accountability sia per ricostruzione degli eventi.

### Sicurezza e privacy

Trattandosi di dati personali e talvolta sensibili, il sistema deve prevedere almeno:

- autenticazione con password robuste;
- ruoli e permessi;
- cifratura TLS in transito;
- backup regolari;
- minimizzazione del dato;
- policy di retention definita con l'ente;
- log accessi e modifiche critiche.

### Resilienza operativa

Per la prima fase non serve un offline-first completo, ma è opportuno prevedere una modalità di **resilienza leggera**, per esempio pagine chiave minimamente fruibili con rete debole, autosave locale temporaneo del form, coda di retry per alcune operazioni e messaggi chiari di sincronizzazione fallita.

## Modello dati logico

Le principali entità della prima fase sono:

- `Tenant/Ente`
- `Campo`
- `Area`
- `UnitàAbitativa`
- `Posto`
- `NucleoFamiliare`
- `Ospite`
- `MovimentoOspite`
- `PresenzaGiornaliera`
- `Volontario`
- `Turno`
- `Materiale`
- `MovimentoMateriale`
- `PastoGiornaliero`
- `EventoDiario`
- `Utente`
- `Ruolo`
- `AuditLog`

Relazioni principali:

- un ente può gestire uno o più campi;
- un campo può avere più aree e più unità abitative;
- un'unità abitativa può avere più posti;
- un nucleo familiare contiene più ospiti;
- un ospite può avere più movimenti nel tempo;
- una presenza giornaliera si riferisce a ospite, giorno e stato;
- un volontario può avere più turni;
- un materiale può avere più movimenti;
- un evento diario può riferirsi opzionalmente a più entità operative.

## Architettura applicativa proposta

### Approccio generale

Per la prima fase è consigliabile una **web application responsive** con backend monolitico modulare. Questo approccio è più rapido da sviluppare, più semplice da distribuire e coerente con il tipo di stack già osservato in soluzioni riusabili o verticali del dominio, dove compaiono tecnologie web come PHP, PostgreSQL e componenti OSS consolidati.[cite:5][cite:58]

### Architettura logica

| Livello | Responsabilità |
|---|---|
| Frontend web | Interfaccia operatore, form, tabelle, ricerca, stampa, dashboard minima |
| API applicativa | Validazione, regole di business, permessi, orchestrazione workflow |
| Database relazionale | Persistenza anagrafiche, movimenti, presenze, materiali, audit |
| Servizio documenti | Generazione PDF e CSV |
| Logging e monitoraggio | Errori applicativi, audit, performance di base |

### Scelte tecniche consigliate

| Componente | Scelta consigliata | Motivo |
|---|---|---|
| Frontend | Web app responsive | Unica codebase, test rapidi, accessibile da notebook e tablet |
| Backend | Monolite modulare | Riduce overhead rispetto a microservizi |
| Database | PostgreSQL | Affidabile, noto nel dominio PA/emergenza [cite:58] |
| API | REST JSON | Semplice integrazione e sviluppo |
| Export | PDF + CSV server-side | Necessario per operatività e condivisione [cite:58] |
| Auth | Sessione o token con RBAC semplice | Sufficiente per la prima fase |
| Deploy | Docker + VM/cloud semplice | Facile installazione in contesti diversi |

## Flussi applicativi critici

### Flusso 1: Registrazione ospite con assegnazione posto

1. L'operatore cerca l'ospite o crea una nuova anagrafica.
2. Collega o crea il nucleo familiare.
3. Inserisce eventuali note critiche e bisogni speciali.
4. Visualizza disponibilità posti.
5. Assegna tenda/modulo/posto.
6. Conferma il check-in.
7. Il sistema scrive movimento, aggiorna occupazione e rende disponibile l'ospite nei report presenze.

### Flusso 2: Chiusura presenze giornaliere

1. La segreteria filtra gli ospiti presenti nel campo.
2. Aggiorna eventuali assenze temporanee o rientri.
3. Conferma la giornata.
4. Il sistema genera dataset di presenze e report giornaliero.

### Flusso 3: Conteggio pasti

1. Il sistema parte dal numero presenti.
2. Applica filtri per esigenze speciali.
3. Produce riepilogo per categoria.
4. Esporta o stampa il riepilogo.

## Rischi tecnici della prima fase

| Rischio | Descrizione | Mitigazione |
|---|---|---|
| Scope creep | Tendenza a trasformare il prodotto in suite Protezione Civile completa | Blocco rigoroso del perimetro MVP |
| Complessità permessi | Ruoli diversi possono generare casi edge | Partire con pochi ruoli ben definiti |
| Report sottovalutati | La stampa richiede formati chiari e dati coerenti | Progettare report fin dall'inizio |
| Offline troppo ambizioso | Sync e conflitti fanno esplodere tempi e bug | Limitarsi a resilienza leggera |
| Modello dati debole | Errori iniziali su nuclei, posti e movimenti impattano tutto | Modellazione accurata prima del coding |
| UX troppo amministrativa | Flussi lenti riducono adozione sul campo | Ridurre campi obbligatori e click |

## Sequenza di sviluppo consigliata

### Sprint 0 — Analisi e fondazioni

- definizione ruoli;
- definizione entità e relazioni;
- wireframe principali;
- matrice report;
- setup ambiente e CI minima.

### Sprint 1 — Nucleo anagrafico

- utenti e ruoli;
- ospiti e nuclei;
- ricerca e schede;
- audit di base.

### Sprint 2 — Operatività campo

- check-in/check-out/trasferimenti;
- posti e allocazioni;
- presenze giornaliere.

### Sprint 3 — Operatività di supporto

- volontari base;
- materiali essenziali;
- pasti e bisogni speciali;
- diario eventi.

### Sprint 4 — Uscita MVP

- report PDF/CSV;
- hardening permessi;
- test end-to-end;
- dati demo e collaudo con scenario realistico.

## Stack tecnico suggerito

Il dominio non impone una tecnologia unica; la ricerca mostra l'uso di stack web consolidati e database relazionali nelle soluzioni esistenti.[cite:5][cite:58] Per una prima fase pragmatica, le opzioni sensate sono:

### Opzione A — Stack pragmatico classico

- frontend server-rendered o SPA leggera;
- backend PHP o framework equivalente;
- PostgreSQL;
- Docker;
- generazione PDF server-side.

Questa opzione è coerente con quanto emerge da ProtezioNET e da soluzioni pubblicate su Developers Italia.[cite:5][cite:58]

### Opzione B — Stack moderno web app

- frontend React/Vue o equivalente;
- backend Node.js, PHP o Python;
- PostgreSQL;
- object storage per allegati futuri;
- codebase pensata per evolvere verso PWA.

Questa opzione è preferibile se l'obiettivo è una UX più moderna e una possibile estensione futura verso offline leggero e mobile web.

## Deliverable della prima fase

Alla chiusura della prima fase il progetto dovrebbe consegnare:

- web app deployabile;
- gestione utenti e ruoli base;
- anagrafiche ospiti e nuclei;
- check-in/check-out/trasferimenti;
- gestione posti e presenze;
- volontari base;
- materiali essenziali;
- pasti e bisogni speciali;
- diario eventi;
- report PDF e CSV;
- audit log minimo;
- documentazione installativa e manuale operativo essenziale.

## Valutazione finale

La prima fase deve essere trattata come costruzione di una **segreteria campo digitale**, non come piattaforma completa di Protezione Civile.[cite:5][cite:19][cite:58] La ricerca suggerisce che lo spazio più interessante non è replicare i sistemi già forti su GIS, sala operativa e gestione emergenze su larga scala, ma presidiare bene il blocco ospiti-alloggi-presenze-volontari-materiali-report, oggi molto meno standardizzato e apparentemente meno coperto da soluzioni moderne dedicate.[cite:5][cite:19][cite:58]

Per questo motivo, la qualità tecnica della prima fase dipende soprattutto da tre fattori: rigore del modello dati, semplicità dei flussi operativi e affidabilità della reportistica. Se questi tre elementi funzionano, il prodotto può essere validato rapidamente sul mercato; se invece la prima fase si disperde in moduli laterali, il rischio è aumentare tempi e complessità senza migliorare il valore percepito dal target.[cite:42]
