<?php

namespace App\Actions;

use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Support\Facades\DB;

// Crea in blocco N tende con M posti ciascuna. La numerazione del codice continua
// da quelle già presenti nello stesso settore/fila, così l'operatore può aggiungere
// tende a più riprese senza collisioni.
class CreaTende
{
    /** @return Tenda[] le tende create */
    public function __invoke(
        int $campoId,
        string $settore,
        ?string $fila,
        int $numeroTende,
        int $postiPerTenda,
        string $tipo = 'alloggio',
        ?string $modello = null,
    ): array {
        $fila = ($fila === '') ? null : $fila;

        return DB::transaction(function () use ($campoId, $settore, $fila, $numeroTende, $postiPerTenda, $tipo, $modello) {
            $prefix = $settore.($fila !== null ? $fila : '');

            $esistenti = Tenda::where('campo_id', $campoId)->where('settore', $settore)
                ->when($fila !== null, fn ($q) => $q->where('fila', $fila), fn ($q) => $q->whereNull('fila'))
                ->count();

            $create = [];
            for ($k = 1; $k <= $numeroTende; $k++) {
                $tenda = Tenda::create([
                    'campo_id' => $campoId,
                    'settore'  => $settore,
                    'fila'     => $fila,
                    'codice'   => sprintf('%s-%02d', $prefix, $esistenti + $k),
                    'tipo'     => $tipo,
                    'modello'  => $modello,
                ]);

                $rows = [];
                for ($n = 1; $n <= $postiPerTenda; $n++) {
                    $rows[] = ['tenda_id' => $tenda->id, 'numero' => (string) $n, 'stato' => 'libero',
                        'created_at' => now(), 'updated_at' => now()];
                }
                Posto::insert($rows);

                $create[] = $tenda;
            }

            return $create;
        });
    }
}
