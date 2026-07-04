<?php

namespace App\Actions;

use App\Models\Presenza;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Consolida le presenze di una giornata: una riga per persona per data.
// Idempotente: rilanciarla per lo stesso giorno aggiorna, non duplica
// (unique persona_id + data).
class ConsolidaPresenze
{
    /**
     * @param  array<int|string, string>  $stati  persona_id => stato del giorno
     * @return int  numero di persone consolidate
     */
    public function __invoke(int $campoId, Carbon $data, array $stati, ?int $userId = null): int
    {
        $giorno = $data->toDateString();

        return DB::transaction(function () use ($campoId, $giorno, $stati, $userId) {
            $n = 0;
            foreach ($stati as $personaId => $stato) {
                Presenza::updateOrCreate(
                    ['persona_id' => (int) $personaId, 'data' => $giorno],
                    ['campo_id' => $campoId, 'stato' => $stato, 'confermato_da_id' => $userId],
                );
                $n++;
            }

            return $n;
        });
    }
}
