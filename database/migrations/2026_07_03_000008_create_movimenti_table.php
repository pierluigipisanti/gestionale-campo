<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cuore del sistema: il registro storico dei movimenti di una persona alloggiata
// (ospite, volontario o sanitario). Immutabile — non si aggiorna, si aggiunge una
// riga. È l'audit trail del check-in/out.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimenti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('persone')->cascadeOnDelete();

            $table->string('tipo'); // checkin | checkout | trasferimento

            $table->foreignId('posto_da_id')->nullable()->constrained('posti')->nullOnDelete();
            $table->foreignId('posto_a_id')->nullable()->constrained('posti')->nullOnDelete();

            $table->foreignId('operatore_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrato_at');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['persona_id', 'registrato_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimenti');
    }
};
