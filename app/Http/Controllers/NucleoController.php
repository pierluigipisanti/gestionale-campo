<?php

namespace App\Http\Controllers;

use App\Actions\CheckInNucleo;
use App\Models\Campo;
use App\Models\CategoriaPersona;
use App\Models\Tenda;
use Illuminate\Http\Request;
use RuntimeException;

// Check-in di un nucleo familiare intero, assegnato a posti della stessa tenda.
class NucleoController extends Controller
{
    public function checkinForm()
    {
        $campo = Campo::firstOrFail();

        $tende = Tenda::where('campo_id', $campo->id)->where('tipo', 'alloggio')
            ->withCount(['posti as liberi_count' => fn ($q) => $q->where('stato', 'libero')])
            ->orderBy('settore')->orderBy('codice')->get()
            ->filter(fn ($t) => $t->liberi_count > 0)->values();

        return view('nucleo.checkin', [
            'tende'     => $tende,
            'categorie' => CategoriaPersona::attive()->orderBy('nome')->get(),
        ]);
    }

    public function checkinStore(Request $request, CheckInNucleo $checkin)
    {
        $data = $request->validate([
            'etichetta'        => ['required', 'string', 'max:255'],
            'categoria_id'     => ['nullable', 'integer', 'exists:categorie_persona,id'],
            'tenda_id'         => ['required', 'integer', 'exists:tende,id'],
            'membri'           => ['required', 'array', 'min:1'],
            'membri.*.cognome' => ['nullable', 'string', 'max:255'],
            'membri.*.nome'    => ['nullable', 'string', 'max:255'],
        ]);

        $membri = collect($data['membri'])
            ->filter(fn ($m) => filled($m['cognome'] ?? null) && filled($m['nome'] ?? null))
            ->values()->all();

        if (empty($membri)) {
            return back()->withInput()->with('err', 'Inserisci almeno una persona con cognome e nome.');
        }

        $tenda = Tenda::findOrFail($data['tenda_id']);

        try {
            $checkin(Campo::firstOrFail()->id, $data['etichetta'], $membri, $tenda, $data['categoria_id'] ?? null, auth()->id());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('err', $e->getMessage());
        }

        return redirect()->route('posti.index')
            ->with('ok', "Check-in nucleo {$data['etichetta']}: ".count($membri)." persone nella tenda {$tenda->codice}.");
    }
}
