<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Log entrata/uscita di un automezzo. Una riga per visita: uscita_at NULL = dentro ora.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('transiti_automezzo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automezzo_id')->constrained('automezzi')->cascadeOnDelete();
            $table->timestamp('entrata_at');
            $table->timestamp('uscita_at')->nullable();
            $table->foreignId('operatore_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['automezzo_id', 'uscita_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transiti_automezzo');
    }
};
