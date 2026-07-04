<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Posto letto. È l'unico livello con vera granularità: il check-in assegna QUI,
// lo stato "inagibile" è per-posto, l'occupazione si conta da qui.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('posti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenda_id')->constrained('tende')->cascadeOnDelete();
            $table->string('numero');                 // 1..N dentro la tenda
            $table->string('stato')->default('libero'); // libero | occupato | inagibile
            $table->timestamps();

            $table->unique(['tenda_id', 'numero']);
            $table->index('stato');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posti');
    }
};
