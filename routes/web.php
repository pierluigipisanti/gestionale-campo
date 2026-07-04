<?php

use App\Http\Controllers\AccessiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AutomezziController;
use App\Http\Controllers\CampoController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PersoneController;
use App\Http\Controllers\LoghiController;
use App\Http\Controllers\NucleoController;
use App\Http\Controllers\PostiController;
use App\Http\Controllers\PresenzeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StrutturaController;
use App\Http\Controllers\UtentiController;
use App\Http\Controllers\VarcoController;
use Illuminate\Support\Facades\Route;

// Autenticazione
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// App — tutto dietro autenticazione
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/cerca', [DashboardController::class, 'cerca'])->name('cerca');

    // Varco / controllo accessi — persone
    Route::get('/varco', [VarcoController::class, 'index'])->name('varco.index');
    Route::post('/varco', [VarcoController::class, 'store'])->name('varco.store');
    Route::post('/varco/uscita', [VarcoController::class, 'uscita'])->name('varco.uscita');

    // Varco — automezzi
    Route::get('/varco/automezzi', [AutomezziController::class, 'varco'])->name('varco.automezzi');
    Route::post('/varco/automezzi/entrata', [AutomezziController::class, 'entrata'])->name('automezzi.entrata');
    Route::post('/varco/automezzi/uscita', [AutomezziController::class, 'uscita'])->name('automezzi.uscita');

    // Automezzi — registro (CRUD)
    Route::get('/automezzi', [AutomezziController::class, 'index'])->name('automezzi.index');
    Route::post('/automezzi', [AutomezziController::class, 'store'])->name('automezzi.store');
    Route::get('/automezzi/{automezzo}/edit', [AutomezziController::class, 'edit'])->name('automezzi.edit');
    Route::patch('/automezzi/{automezzo}', [AutomezziController::class, 'update'])->name('automezzi.update');
    Route::delete('/automezzi/{automezzo}', [AutomezziController::class, 'destroy'])->name('automezzi.destroy');

    // Check-in nucleo (prima di /posti/{posto} per non collidere)
    Route::get('/posti/nucleo', [NucleoController::class, 'checkinForm'])->name('nucleo.checkin.form');
    Route::post('/posti/nucleo', [NucleoController::class, 'checkinStore'])->name('nucleo.checkin.store');

    // Posti / check-in
    Route::get('/posti', [PostiController::class, 'index'])->name('posti.index');
    Route::get('/posti/{posto}/checkin', [PostiController::class, 'checkinForm'])->name('posti.checkin.form');
    Route::post('/posti/{posto}/checkin', [PostiController::class, 'checkinStore'])->name('posti.checkin.store');
    Route::get('/posti/{posto}', [PostiController::class, 'show'])->name('posti.show');
    Route::post('/posti/{posto}/checkout', [PostiController::class, 'checkout'])->name('posti.checkout');
    Route::post('/posti/{posto}/trasferisci', [PostiController::class, 'trasferisci'])->name('posti.trasferisci');

    // Chiusura presenze giornaliera
    Route::get('/presenze', [PresenzeController::class, 'index'])->name('presenze.index');
    Route::post('/presenze', [PresenzeController::class, 'store'])->name('presenze.store');

    // Struttura campo — creare/modificare tende: admin o operatore
    Route::get('/struttura', [StrutturaController::class, 'index'])->name('struttura.index');
    Route::post('/struttura', [StrutturaController::class, 'store'])->name('struttura.store');
    Route::get('/struttura/{tenda}/edit', [StrutturaController::class, 'edit'])->name('struttura.edit');
    Route::patch('/struttura/{tenda}', [StrutturaController::class, 'update'])->name('struttura.update');
    Route::post('/struttura/{tenda}/posti', [StrutturaController::class, 'addPosti'])->name('struttura.posti.add');
    Route::patch('/struttura/posti/{posto}/inagibile', [StrutturaController::class, 'toggleInagibile'])->name('struttura.posti.inagibile');

    // Anagrafica: lookup per CF + ricerca per cognome (assegnazione tenda)
    Route::get('/anagrafica', [PersoneController::class, 'lookup'])->name('anagrafica.lookup');
    Route::get('/anagrafica/cerca', [PersoneController::class, 'cerca'])->name('anagrafica.cerca');

    // Import da Excel (Utility)
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::get('/import/template/volontari', [ImportController::class, 'templateVolontari'])->name('import.template.volontari');
    Route::get('/import/template/automezzi', [ImportController::class, 'templateAutomezzi'])->name('import.template.automezzi');
    Route::post('/import/volontari', [ImportController::class, 'importVolontari'])->name('import.volontari');
    Route::post('/import/automezzi', [ImportController::class, 'importAutomezzi'])->name('import.automezzi');

    // Persone (scheda modificabile)
    Route::get('/persone/{persona}/edit', [PersoneController::class, 'edit'])->name('persone.edit');
    Route::patch('/persone/{persona}', [PersoneController::class, 'update'])->name('persone.update');
    Route::delete('/persone/{persona}', [PersoneController::class, 'destroy'])->name('persone.destroy');

    // Accessi (correzione/eliminazione)
    Route::get('/accessi/{accesso}/edit', [AccessiController::class, 'edit'])->name('accessi.edit');
    Route::patch('/accessi/{accesso}', [AccessiController::class, 'update'])->name('accessi.update');
    Route::delete('/accessi/{accesso}', [AccessiController::class, 'destroy'])->name('accessi.destroy');

    // Stampe
    Route::view('/stampe', 'stampe.index')->name('stampe.index');
    Route::get('/stampe/cartelli-tende', [ReportController::class, 'cartelliTende'])->name('stampe.cartelli');
    Route::get('/stampe/tenda/{tenda}/cartello', [ReportController::class, 'cartelloTenda'])->name('stampe.tenda');

    // Report (PDF/CSV)
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/presenze.pdf', [ReportController::class, 'presenzePdf'])->name('report.presenze.pdf');
    Route::get('/report/presenze.csv', [ReportController::class, 'presenzeCsv'])->name('report.presenze.csv');
    Route::get('/report/posti.pdf', [ReportController::class, 'postiPdf'])->name('report.posti.pdf');
    Route::get('/report/accessi.pdf', [ReportController::class, 'accessiPdf'])->name('report.accessi.pdf');
    Route::get('/report/accessi.csv', [ReportController::class, 'accessiCsv'])->name('report.accessi.csv');

    // Area admin
    Route::middleware('can:admin')->group(function () {
        Route::get('/campo', [CampoController::class, 'edit'])->name('campo.edit');
        Route::patch('/campo', [CampoController::class, 'update'])->name('campo.update');

        Route::delete('/struttura/posti/{posto}', [StrutturaController::class, 'removePosto'])->name('struttura.posti.remove');
        Route::delete('/struttura/{tenda}', [StrutturaController::class, 'destroy'])->name('struttura.destroy');

        Route::get('/utenti', [UtentiController::class, 'index'])->name('utenti.index');
        Route::post('/utenti', [UtentiController::class, 'store'])->name('utenti.store');
        Route::get('/utenti/{utente}/edit', [UtentiController::class, 'edit'])->name('utenti.edit');
        Route::patch('/utenti/{utente}', [UtentiController::class, 'update'])->name('utenti.update');
        Route::delete('/utenti/{utente}', [UtentiController::class, 'destroy'])->name('utenti.destroy');

        Route::get('/categorie', [CategorieController::class, 'index'])->name('categorie.index');
        Route::post('/categorie', [CategorieController::class, 'store'])->name('categorie.store');
        Route::patch('/categorie/{categoria}', [CategorieController::class, 'update'])->name('categorie.update');
        Route::delete('/categorie/{categoria}', [CategorieController::class, 'destroy'])->name('categorie.destroy');
        Route::patch('/categorie/{categoria}/toggle', [CategorieController::class, 'toggle'])->name('categorie.toggle');

        Route::get('/loghi', [LoghiController::class, 'index'])->name('loghi.index');
        Route::post('/loghi', [LoghiController::class, 'store'])->name('loghi.store');
        Route::patch('/loghi/{logo}', [LoghiController::class, 'update'])->name('loghi.update');
        Route::get('/loghi/{logo}/file', [LoghiController::class, 'file'])->name('loghi.file');
        Route::delete('/loghi/{logo}', [LoghiController::class, 'destroy'])->name('loghi.destroy');
    });
});
