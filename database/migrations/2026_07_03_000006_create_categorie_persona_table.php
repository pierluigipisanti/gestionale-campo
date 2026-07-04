<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Vocabolario delle categorie di persona, GESTITO DALL'ADMIN dall'interfaccia.
// Regola d'oro del progetto: massima flessibilità, zero modifiche al codice.
// Ospite, Volontario, Sanitario, VVF, ENEL, Polizia, Carabinieri... si aggiungono
// come righe, non come costanti nel codice. Vale sia per chi alloggia (persone)
// sia per chi transita (accessi): stesso vocabolario condiviso.
//
// Non si cancella una categoria in uso (restrictOnDelete lato FK): la si DISATTIVA
// con `attiva=false`, così le persone/accessi storici restano coerenti.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorie_persona', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();       // "Vigili del Fuoco", "ENEL", "Ospite"...
            $table->string('sigla')->nullable();    // "VVF" — opzionale, per griglia/report
            $table->boolean('attiva')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorie_persona');
    }
};
