<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Controllo dei loghi sulle stampe: quali loghi finiscono su cartelli/report e in che ordine.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('loghi', function (Blueprint $table) {
            $table->boolean('stampe')->default(true)->after('mime');
            $table->integer('ordine')->default(0)->after('stampe');
        });
    }

    public function down(): void
    {
        Schema::table('loghi', function (Blueprint $table) {
            $table->dropColumn(['stampe', 'ordine']);
        });
    }
};
