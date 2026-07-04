<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Registro varco / controllo accessi: chi TRANSITA dal campo senza alloggiare
// (VVF di passaggio, ENEL, polizia, carabinieri, autorità, fornitori, stampa).
// Probabilmente la funzione più usata del sistema → deve essere velocissima.
//
// Una riga per visita: `uscita_at` NULL = persona attualmente DENTRO il campo,
// che è la domanda più frequente al varco (sicurezza, evacuazione).
// Campi cognome/nome/CF separati (come al check-in) per ricerche affidabili;
// compilabili col lettore documenti. Niente posto, niente presenze: è transito.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('accessi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');
            $table->foreignId('categoria_id')->nullable()->constrained('categorie_persona')->restrictOnDelete();

            $table->string('cognome');
            $table->string('nome')->nullable();
            $table->string('codice_fiscale')->nullable();
            $table->string('telefono')->nullable();
            $table->string('ente_appartenenza')->nullable();
            $table->string('documento')->nullable();
            $table->string('targa_veicolo')->nullable();
            $table->text('motivo')->nullable();

            $table->timestamp('entrata_at');
            $table->timestamp('uscita_at')->nullable();

            $table->foreignId('operatore_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campo_id', 'uscita_at']); // "chi è dentro ora"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessi');
    }
};
