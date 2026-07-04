<?php

namespace App\Actions;

use App\Models\Nucleo;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Check-in di un nucleo familiare: crea il nucleo e assegna tutti i membri a posti
// liberi della STESSA tenda, così la famiglia resta insieme. Tutto o niente.
class CheckInNucleo
{
    /**
     * @param  array<int, array{cognome:string, nome:string}>  $membri
     * @throws RuntimeException se la tenda non ha abbastanza posti liberi
     */
    public function __invoke(int $campoId, string $etichetta, array $membri, Tenda $tenda, ?int $categoriaId = null, ?int $operatoreId = null): Nucleo
    {
        return DB::transaction(function () use ($campoId, $etichetta, $membri, $tenda, $categoriaId, $operatoreId) {
            $liberi = Posto::where('tenda_id', $tenda->id)->where('stato', 'libero')
                ->orderByRaw('length(numero), numero')->lockForUpdate()->get();

            if ($liberi->count() < count($membri)) {
                throw new RuntimeException("Posti liberi insufficienti nella tenda {$tenda->codice}: servono ".count($membri).", disponibili {$liberi->count()}.");
            }

            $nucleo = Nucleo::create(['campo_id' => $campoId, 'etichetta' => $etichetta]);

            $checkin = new EseguiCheckIn();
            foreach (array_values($membri) as $i => $m) {
                $persona = Persona::create([
                    'campo_id'     => $campoId,
                    'nucleo_id'    => $nucleo->id,
                    'categoria_id' => $categoriaId,
                    'cognome'      => $m['cognome'],
                    'nome'         => $m['nome'],
                    'stato'        => 'pre_registrato',
                ]);
                $checkin($persona, $liberi[$i], $operatoreId);
            }

            return $nucleo;
        });
    }
}
