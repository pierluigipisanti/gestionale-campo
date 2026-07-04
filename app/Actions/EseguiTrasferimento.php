<?php

namespace App\Actions;

use App\Models\Movimento;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Trasferimento interno: sposta una persona presente da un posto a un altro.
// Libera il vecchio, occupa il nuovo, la persona resta 'presente'.
class EseguiTrasferimento
{
    /** @throws RuntimeException se la persona non è presente o il nuovo posto non è libero */
    public function __invoke(Persona $persona, Posto $nuovoPosto, ?int $operatoreId = null, ?string $note = null): Movimento
    {
        return DB::transaction(function () use ($persona, $nuovoPosto, $operatoreId, $note) {
            $persona = Persona::whereKey($persona->getKey())->lockForUpdate()->firstOrFail();

            if ($persona->stato !== 'presente') {
                throw new RuntimeException("La persona {$persona->id} non è presente: non si può trasferire.");
            }

            $vecchioPostoId = $persona->posto_id;
            if ($vecchioPostoId === $nuovoPosto->getKey()) {
                throw new RuntimeException("La persona è già nel posto {$nuovoPosto->id}.");
            }

            // lock dei posti coinvolti in ordine di id → niente deadlock tra operatori
            $ids = array_filter([$vecchioPostoId, $nuovoPosto->getKey()]);
            sort($ids);
            Posto::whereIn('id', $ids)->lockForUpdate()->get();

            $nuovo = Posto::whereKey($nuovoPosto->getKey())->firstOrFail();
            if ($nuovo->stato !== 'libero') {
                throw new RuntimeException("Posto {$nuovo->id} non disponibile (stato: {$nuovo->stato}).");
            }

            $movimento = Movimento::create([
                'persona_id'    => $persona->getKey(),
                'tipo'          => 'trasferimento',
                'posto_da_id'   => $vecchioPostoId,
                'posto_a_id'    => $nuovo->getKey(),
                'operatore_id'  => $operatoreId,
                'registrato_at' => now(),
                'note'          => $note,
            ]);

            if ($vecchioPostoId) {
                Posto::whereKey($vecchioPostoId)->update(['stato' => 'libero']);
            }

            $nuovo->stato = 'occupato';
            $nuovo->save();

            $persona->posto_id = $nuovo->getKey();
            $persona->ultimo_movimento_at = now();
            $persona->save();

            return $movimento;
        });
    }
}
