<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ente superiore. Nella fase 0 esiste una sola riga; c'è per non dover
// riscrivere lo schema quando servirà la vista multi-campo. ponytail: una
// tabella + FK ora, la dashboard aggregata quando arriva il secondo campo.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('enti', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo')->nullable(); // comune, associazione, coordinamento
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enti');
    }
};
