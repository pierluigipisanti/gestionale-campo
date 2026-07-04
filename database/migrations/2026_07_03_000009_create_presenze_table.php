<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Consolidamento presenze giornaliero: una riga per persona per giorno.
// Serve per report, sicurezza e conteggio pasti. Lo stato del movimento dà
// la posizione; questo dà la fotografia del giorno confermata dalla segreteria.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('presenze', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');
            $table->foreignId('persona_id')->constrained('persone')->cascadeOnDelete();
            $table->date('data');
            $table->string('stato'); // presente | assente_temporaneo | uscito | trasferito
            $table->foreignId('confermato_da_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['persona_id', 'data']);
            $table->index(['campo_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presenze');
    }
};
