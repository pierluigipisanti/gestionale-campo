<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Automezzi registrati una volta (targa, referente...) che entrano/escono dal campo
// più volte. `stato` = posizione corrente (fuori/dentro), denormalizzata dai transiti.
// In fase B ognuno avrà un QR stampabile (sul cruscotto) per entrata/uscita rapida.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('automezzi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');
            $table->string('targa');
            $table->string('tipo')->nullable();            // furgone, ambulanza, auto...
            $table->string('descrizione')->nullable();
            $table->string('ente_appartenenza')->nullable();
            $table->string('referente')->nullable();       // nome del referente a bordo
            $table->string('telefono')->nullable();        // cellulare referente
            $table->string('stato')->default('fuori');     // fuori | dentro
            $table->timestamp('ultimo_movimento_at')->nullable();
            $table->timestamps();

            $table->unique(['campo_id', 'targa']);
            $table->index(['campo_id', 'stato']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automezzi');
    }
};
