<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Unità abitativa (tenda ministeriale, modulo, container...).
// La gerarchia dell'attendamento — settore, fila/strada — è modellata come
// COLONNE, non come tabelle: settore e fila sono etichette di raggruppamento,
// non hanno dati propri. ponytail: diventano tabelle solo il giorno in cui un
// settore deve portare attributi suoi (capienza max, responsabile, ecc.).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tende', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');
            $table->string('settore')->nullable();       // A, B, C — separa alloggi da servizi
            $table->string('fila')->nullable();          // la "strada" dell'attendamento
            $table->string('codice');                    // etichetta operativa, es. B-03
            $table->string('tipo')->default('alloggio'); // alloggio | servizi
            $table->string('modello')->nullable();       // PI88, modulo, ecc.
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['campo_id', 'settore']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tende');
    }
};
