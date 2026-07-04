<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seed ESSENZIALE per una installazione reale: un campo (rinominabile), le
// categorie di partenza e gli utenti iniziali. Nessun dato demo.
// I dati di prova (tende, persone, automezzi) stanno in DemoSeeder.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $enteId = DB::table('enti')->insertGetId([
            'nome' => 'Comune di Esempio',
            'tipo' => 'comune',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('campi')->insert([
            'ente_id' => $enteId,
            'nome' => 'Campo Accoglienza',
            'comune' => 'Esempio',
            'attivo' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Utenti iniziali (default demo). CAMBIA la password al primo accesso,
        // oppure crea il tuo admin con:  php artisan crea:admin
        User::create(['name' => 'Amministratore', 'email' => 'admin@campo.local', 'password' => 'password', 'ruolo' => 'admin']);
        User::create(['name' => 'Operatore', 'email' => 'operatore@campo.local', 'password' => 'password', 'ruolo' => 'operatore']);

        // Categorie di partenza. L'admin le gestisce poi dall'interfaccia.
        foreach (['Ospite', 'Volontario', 'Sanitario', 'Vigili del Fuoco',
                  'Polizia', 'Carabinieri', 'Protezione Civile', 'Croce Rossa',
                  'ENEL', 'Fornitore'] as $nome) {
            DB::table('categorie_persona')->insert([
                'nome' => $nome, 'attiva' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
