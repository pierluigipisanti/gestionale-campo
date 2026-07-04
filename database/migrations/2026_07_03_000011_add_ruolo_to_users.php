<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ruolo utente: admin | operatore. Default operatore. I permessi fini si esprimono
// con un Gate 'admin', non con un sistema RBAC (D-tech: 2 ruoli, semplice).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ruolo')->default('operatore')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ruolo');
        });
    }
};
