<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Dati utili per i volontari: specializzazione e patenti possedute.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('persone', function (Blueprint $table) {
            $table->string('specializzazione')->nullable()->after('ente_appartenenza');
            $table->string('patente')->nullable()->after('specializzazione');
        });
    }

    public function down(): void
    {
        Schema::table('persone', function (Blueprint $table) {
            $table->dropColumn(['specializzazione', 'patente']);
        });
    }
};
