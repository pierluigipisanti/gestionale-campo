<?php

namespace App\Actions;

use App\Models\Movimento;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Check-in di una persona (ospite/volontario/sanitario) su un posto.
// Unico punto con logica non banale: movimento + persona + posto devono restare
// coerenti o non cambia niente → tutto in una transazione.
class EseguiCheckIn
{
    /**
     * @param  array  $documento  opzionale, catturato al varco: ['tipo' => 'cie',
     *                            'numero' => '...', 'codice_fiscale' => '...']
     * @throws RuntimeException se il posto non è libero o la persona è già presente
     */
    public function __invoke(
        Persona $persona,
        Posto $posto,
        ?int $operatoreId = null,
        ?string $note = null,
        array $documento = [],
    ): Movimento {
        return DB::transaction(function () use ($persona, $posto, $operatoreId, $note, $documento) {
            // Lock pessimistico sul posto: due operatori non assegnano lo stesso letto.
            // ponytail: lockForUpdate basta a questa scala (un campo, pochi operatori);
            // niente code/optimistic versioning finché il throughput non lo impone.
            $posto = Posto::whereKey($posto->getKey())->lockForUpdate()->firstOrFail();

            if ($posto->stato !== 'libero') {
                throw new RuntimeException("Posto {$posto->id} non disponibile (stato: {$posto->stato}).");
            }
            if ($persona->stato === 'presente') {
                throw new RuntimeException("La persona {$persona->id} risulta già presente.");
            }

            $movimento = Movimento::create([
                'persona_id'    => $persona->getKey(),
                'tipo'          => 'checkin',
                'posto_da_id'   => null,
                'posto_a_id'    => $posto->getKey(),
                'operatore_id'  => $operatoreId,
                'registrato_at' => now(),
                'note'          => $note,
            ]);

            // Documento: aggiorna solo i campi effettivamente passati.
            $persona->fill(array_filter([
                'documento_tipo'   => $documento['tipo'] ?? null,
                'documento_numero' => $documento['numero'] ?? null,
                'codice_fiscale'   => $documento['codice_fiscale'] ?? null,
            ]));

            // Posizione corrente denormalizzata (vedi D5): stessa transazione del movimento.
            $persona->stato = 'presente';
            $persona->posto_id = $posto->getKey();
            $persona->ultimo_movimento_at = now();
            $persona->save();

            $posto->stato = 'occupato';
            $posto->save();

            return $movimento;
        });
    }
}
