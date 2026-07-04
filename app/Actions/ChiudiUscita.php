<?php

namespace App\Actions;

use App\Models\Accesso;
use RuntimeException;

// Chiude un accesso: registra l'uscita dal varco.
class ChiudiUscita
{
    /** @throws RuntimeException se l'accesso è già chiuso */
    public function __invoke(Accesso $accesso): Accesso
    {
        // update condizionale: atomico e race-safe. Se due operatori chiudono lo
        // stesso accesso, solo il primo tocca una riga; il secondo vede 0 e fallisce.
        // ponytail: niente lock/transazione, whereNull basta.
        $chiusi = Accesso::whereKey($accesso->getKey())
            ->whereNull('uscita_at')
            ->update(['uscita_at' => now()]);

        if ($chiusi === 0) {
            throw new RuntimeException("Accesso {$accesso->id} già chiuso o inesistente.");
        }

        return $accesso->fresh();
    }
}
