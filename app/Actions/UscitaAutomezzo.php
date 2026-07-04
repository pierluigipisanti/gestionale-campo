<?php

namespace App\Actions;

use App\Models\Automezzo;
use App\Models\TransitoAutomezzo;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Registra l'uscita di un automezzo: chiude il transito aperto e lo mette 'fuori'.
class UscitaAutomezzo
{
    /** @throws RuntimeException se non è dentro */
    public function __invoke(Automezzo $automezzo, ?int $operatoreId = null): void
    {
        DB::transaction(function () use ($automezzo, $operatoreId) {
            $automezzo = Automezzo::whereKey($automezzo->getKey())->lockForUpdate()->firstOrFail();

            if ($automezzo->stato !== 'dentro') {
                throw new RuntimeException("L'automezzo {$automezzo->targa} non risulta dentro.");
            }

            TransitoAutomezzo::where('automezzo_id', $automezzo->getKey())
                ->whereNull('uscita_at')->update(['uscita_at' => now()]);

            $automezzo->update(['stato' => 'fuori', 'ultimo_movimento_at' => now()]);
        });
    }
}
