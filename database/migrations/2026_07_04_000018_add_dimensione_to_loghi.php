<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Dimensione del logo sulle stampe: S (piccolo) / M (medio) / L (grande).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('loghi', function (Blueprint $table) {
            $table->string('dimensione', 1)->default('M')->after('ordine');
        });
    }

    public function down(): void
    {
        Schema::table('loghi', function (Blueprint $table) {
            $table->dropColumn('dimensione');
        });
    }
};
