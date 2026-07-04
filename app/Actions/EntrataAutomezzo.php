<?php

namespace App\Actions;

use App\Models\Automezzo;
use App\Models\TransitoAutomezzo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Registra l'ingresso di un automezzo: apre un transito e lo mette 'dentro'.
class EntrataAutomezzo
{
    /** @throws RuntimeException se è già dentro */
    public function __invoke(Automezzo $automezzo, ?int $operatoreId = null): TransitoAutomezzo
    {
        return DB::transaction(function () use ($automezzo, $operatoreId) {
            $automezzo = Automezzo::whereKey($automezzo->getKey())->lockForUpdate()->firstOrFail();

            if ($automezzo->stato === 'dentro') {
                throw new RuntimeException("L'automezzo {$automezzo->targa} risulta già dentro.");
            }

            $transito = TransitoAutomezzo::create([
                'automezzo_id' => $automezzo->getKey(),
                'entrata_at'   => now(),
                'operatore_id' => $operatoreId,
            ]);

            $automezzo->update(['stato' => 'dentro', 'ultimo_movimento_at' => now()]);

            return $transito;
        });
    }
}
