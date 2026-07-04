<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Dati DIMOSTRATIVI per provare l'app: tende (settore A da 6 posti + settore B
// da 8), automezzi e volontari pre-registrati. NON usare in produzione.
//   php artisan db:seed --class=DemoSeeder
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $campoId = DB::table('campi')->value('id');
        if (! $campoId) {
            $this->command->warn('Nessun campo: esegui prima il seed base (php artisan db:seed).');

            return;
        }

        // settore A: 3 file da 4 tende, 6 posti ciascuna
        foreach (['1', '2', '3'] as $fila) {
            foreach (range(1, 4) as $n) {
                $tendaId = DB::table('tende')->insertGetId([
                    'campo_id' => $campoId, 'settore' => 'A', 'fila' => $fila,
                    'codice' => sprintf('A-%s%02d', $fila, $n), 'tipo' => 'alloggio', 'modello' => 'PI88',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach (range(1, 6) as $p) {
                    DB::table('posti')->insert(['tenda_id' => $tendaId, 'numero' => (string) $p, 'stato' => 'libero', 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }

        // settore B: 2 tende da 8 posti (layout misto 6/8)
        foreach (range(1, 2) as $n) {
            $tendaId = DB::table('tende')->insertGetId([
                'campo_id' => $campoId, 'settore' => 'B', 'codice' => sprintf('B-%02d', $n),
                'tipo' => 'alloggio', 'modello' => 'PI88', 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (range(1, 8) as $p) {
                DB::table('posti')->insert(['tenda_id' => $tendaId, 'numero' => (string) $p, 'stato' => 'libero', 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        foreach ([['Ambulanza', 'Croce Rossa', 'AA111BB'], ['Furgone', 'Protezione Civile', 'CC222DD'], ['Fuoristrada', 'Vigili del Fuoco', 'DD333EE']] as [$tipo, $ente, $targa]) {
            DB::table('automezzi')->insert(['campo_id' => $campoId, 'targa' => $targa, 'tipo' => $tipo, 'ente_appartenenza' => $ente, 'stato' => 'fuori', 'created_at' => now(), 'updated_at' => now()]);
        }

        $volCat = DB::table('categorie_persona')->where('nome', 'Volontario')->value('id');
        foreach ([
            ['Ferri', 'Luca', 'FRRLCU85A01H501U', 'Logistica', 'B', 'Vegetariano'],
            ['Neri', 'Sara', 'NRESRA90A41H501U', 'Sanitario', 'B', 'Nessuna'],
            ['Galli', 'Marco', 'GLLMRC78A01H501U', 'Cucina', 'C, D', 'Celiaco'],
            ['Riva', 'Anna', 'RVINNA92A41H501U', 'Radio', 'B', 'Nessuna'],
            ['Costa', 'Paolo', 'CSTPLA80A01H501U', 'Antincendio', 'B, C', 'Nessuna'],
        ] as [$cog, $nom, $cf, $spec, $pat, $all]) {
            DB::table('persone')->insert([
                'campo_id' => $campoId, 'categoria_id' => $volCat, 'cognome' => $cog, 'nome' => $nom,
                'codice_fiscale' => $cf, 'ente_appartenenza' => 'ANPAS', 'specializzazione' => $spec,
                'patente' => $pat, 'allergie_dieta' => $all, 'stato' => 'pre_registrato',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // settore A (12×6=72) + settore B (2×8=16) = 88 posti
        assert(DB::table('posti')->where('stato', 'libero')->count() === 88);
    }
}
