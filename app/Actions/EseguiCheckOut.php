<?php

namespace App\Actions;

use App\Models\Movimento;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Check-out: chiude la permanenza, libera il posto, mantiene lo storico.
// Fratello del check-in — stesso schema, transizione opposta.
class EseguiCheckOut
{
    /** @throws RuntimeException se la persona non è presente */
    public function __invoke(Persona $persona, ?int $operatoreId = null, ?string $note = null): Movimento
    {
        return DB::transaction(function () use ($persona, $operatoreId, $note) {
            // lock sulla persona: niente doppio check-out concorrente
            $persona = Persona::whereKey($persona->getKey())->lockForUpdate()->firstOrFail();

            if ($persona->stato !== 'presente') {
                throw new RuntimeException("La persona {$persona->id} non è presente (stato: {$persona->stato}).");
            }

            $postoId = $persona->posto_id;

            $movimento = Movimento::create([
                'persona_id'    => $persona->getKey(),
                'tipo'          => 'checkout',
                'posto_da_id'   => $postoId,
                'posto_a_id'    => null,
                'operatore_id'  => $operatoreId,
                'registrato_at' => now(),
                'note'          => $note,
            ]);

            if ($postoId) {
                Posto::whereKey($postoId)->update(['stato' => 'libero']);
            }

            $persona->stato = 'dimesso';
            $persona->posto_id = null;
            $persona->ultimo_movimento_at = now();
            $persona->save();

            return $movimento;
        });
    }
}
