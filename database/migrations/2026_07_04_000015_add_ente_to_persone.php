<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ente di appartenenza (per volontari/sanitari che vengono da un'associazione).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('persone', function (Blueprint $table) {
            $table->string('ente_appartenenza')->nullable()->after('comune_provenienza');
        });
    }

    public function down(): void
    {
        Schema::table('persone', function (Blueprint $table) {
            $table->dropColumn('ente_appartenenza');
        });
    }
};
