<?php

namespace App\Http\Controllers;

use App\Actions\ConsolidaPresenze;
use App\Models\Campo;
use App\Models\Persona;
use App\Models\Presenza;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Chiusura presenze giornaliera: consolida chi è presente/assente in una fotografia
// per data (tabella presenze), per report, sicurezza e conteggio pasti.
class PresenzeController extends Controller
{
    public function index(Request $request)
    {
        $campo = Campo::firstOrFail();
        $data = rescue(fn () => $request->date('data'), null) ?? today();

        $persone = Persona::with(['categoria', 'posto.tenda'])
            ->where('campo_id', $campo->id)
            ->whereIn('stato', ['presente', 'assente_temporaneo'])
            ->orderBy('cognome')->orderBy('nome')->get();

        // stato già consolidato per quel giorno (se esiste), per pre-riempire i select
        $esistenti = Presenza::where('campo_id', $campo->id)
            ->whereDate('data', $data)->pluck('stato', 'persona_id');

        return view('presenze.index', compact('campo', 'data', 'persone', 'esistenti'));
    }

    public function store(Request $request, ConsolidaPresenze $consolida)
    {
        $request->validate(['data' => ['required', 'date']]);
        $campo = Campo::firstOrFail();
        $data = Carbon::parse($request->input('data'));

        // stato DERIVATO dallo stato corrente della persona (check-in/out), non scelto a mano
        $stati = Persona::where('campo_id', $campo->id)
            ->whereIn('stato', ['presente', 'assente_temporaneo'])->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->stato === 'assente_temporaneo' ? 'assente_temporaneo' : 'presente'])
            ->all();

        $n = $consolida($campo->id, $data, $stati, auth()->id());

        return redirect()->route('presenze.index', ['data' => $data->toDateString()])
            ->with('ok', "Presenze del {$data->format('d/m/Y')} consolidate ({$n} persone).");
    }
}
