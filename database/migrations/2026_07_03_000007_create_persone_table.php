<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Persona alloggiata nel campo: ospite, volontario o sanitario. Tutti occupano
// un posto letto e condividono la stessa macchina (movimenti + presenze), quindi
// stanno in un'unica tabella; `categoria_id` punta a una lookup gestita dall'admin
// (categorie_persona), non a un tipo nel codice: nuove figure non richiedono
// migration né uno sviluppatore.
// NB: chi solo transita (VVF di passaggio, autorità, fornitori) NON sta qui —
// quello è la tabella `accessi` (registro varco).
//
// posto_id e stato sono DENORMALIZZATI: posizione corrente aggiornata nella stessa
// transazione del movimento. `movimenti` resta la verità storica/audit.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('persone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');

            // categoria gestita dall'admin via lookup (ospite, volontario, VVF, ENEL...)
            $table->foreignId('categoria_id')->nullable()->constrained('categorie_persona')->restrictOnDelete();

            // nucleo familiare: riguarda gli ospiti; null per volontari/sanitari
            $table->foreignId('nucleo_id')->nullable()->constrained('nuclei')->nullOnDelete();

            $table->string('cognome');
            $table->string('nome');
            $table->date('data_nascita')->nullable();
            $table->string('sesso')->nullable();
            $table->string('telefono')->nullable();
            $table->string('comune_provenienza')->nullable();

            // Documento presentato al check-in. Tipo come stringa: il set è legalmente
            // stabile e "manuale" è l'escape hatch → non una lookup come le categorie.
            $table->string('codice_fiscale')->nullable();
            $table->string('documento_tipo')->nullable();   // passaporto | cie | tessera_volontario | manuale
            $table->string('documento_numero')->nullable();

            $table->text('note_sanitarie')->nullable();  // fragilità essenziali
            $table->text('allergie_dieta')->nullable();
            $table->text('note')->nullable();

            // stato corrente: pre_registrato | presente | assente_temporaneo | trasferito | dimesso
            $table->string('stato')->default('pre_registrato');
            $table->foreignId('posto_id')->nullable()->constrained('posti')->nullOnDelete();
            $table->timestamp('ultimo_movimento_at')->nullable();

            $table->timestamps();

            $table->index(['campo_id', 'categoria_id', 'stato']);
            $table->index(['cognome', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persone');
    }
};
