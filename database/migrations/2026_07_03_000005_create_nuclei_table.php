<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nucleo familiare: entità autonoma per assegnare insieme più persone,
// tenerle coerenti nei trasferimenti e produrre report per famiglia.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('nuclei', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');
            $table->string('etichetta');    // es. cognome capofamiglia
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuclei');
    }
};
