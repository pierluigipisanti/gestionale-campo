<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Loghi caricati dall'admin (ente, comune, Protezione Civile...) da usare nei report
// e nei tesserini. Solo metadati qui; il file sta sul disco 'public'.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('loghi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->constrained('campi');
            $table->string('etichetta');
            $table->string('path');   // percorso sul disco 'public'
            $table->string('mime');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loghi');
    }
};
