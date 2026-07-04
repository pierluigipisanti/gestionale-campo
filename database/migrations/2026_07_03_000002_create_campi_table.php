<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Il campo di accoglienza. Tutte le entità operative portano campo_id: i report
// filtrano già per campo anche se ora il valore è sempre lo stesso.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('campi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ente_id')->constrained('enti');
            $table->string('nome');
            $table->string('comune')->nullable();
            $table->boolean('attivo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campi');
    }
};
