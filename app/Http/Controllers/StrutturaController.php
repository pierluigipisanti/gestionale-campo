<?php

namespace App\Http\Controllers;

use App\Actions\CreaTende;
use App\Models\Campo;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Struttura del campo: l'operatore crea le tende e i loro posti. Niente più seeder.
class StrutturaController extends Controller
{
    public function index()
    {
        $campo = Campo::firstOrFail();

        $tende = Tenda::where('campo_id', $campo->id)
            ->withCount(['posti', 'posti as posti_occupati_count' => fn ($q) => $q->where('stato', 'occupato')])
            ->orderBy('settore')->orderBy('fila')->orderBy('codice')->get();

        return view('struttura.index', [
            'campo'  => $campo,
            'tende'  => $tende,
            'totali' => ['tende' => $tende->count(), 'posti' => $tende->sum('posti_count')],
        ]);
    }

    public function store(Request $request, CreaTende $crea)
    {
        $d = $request->validate([
            'settore'         => ['required', 'string', 'max:20'],
            'fila'            => ['nullable', 'string', 'max:20'],
            'numero_tende'    => ['required', 'integer', 'min:1', 'max:200'],
            'posti_per_tenda' => ['required', 'integer', 'min:1', 'max:100'],
            'tipo'            => ['required', 'in:alloggio,servizi'],
            'modello'         => ['nullable', 'string', 'max:60'],
        ]);

        if ($d['numero_tende'] * $d['posti_per_tenda'] > 5000) {
            return back()->withInput()->with('err', 'Troppi posti in un colpo solo (max 5000). Dividi in più inserimenti.');
        }

        $crea(Campo::firstOrFail()->id, $d['settore'], $d['fila'] ?? null,
            $d['numero_tende'], $d['posti_per_tenda'], $d['tipo'], $d['modello'] ?? null);

        return redirect()->route('struttura.index')
            ->with('ok', "Create {$d['numero_tende']} tende × {$d['posti_per_tenda']} posti nel settore {$d['settore']}.");
    }

    public function edit(Tenda $tenda)
    {
        $tenda->load(['posti' => fn ($q) => $q->orderByRaw('length(numero), numero')]);

        $occupanti = Persona::where('stato', 'presente')
            ->whereIn('posto_id', $tenda->posti->pluck('id'))->get()->keyBy('posto_id');

        return view('struttura.edit', compact('tenda', 'occupanti'));
    }

    public function update(Request $request, Tenda $tenda)
    {
        $data = $request->validate([
            'settore' => ['required', 'string', 'max:20'],
            'fila'    => ['nullable', 'string', 'max:20'],
            'codice'  => ['required', 'string', 'max:50'],
            'tipo'    => ['required', 'in:alloggio,servizi'],
            'modello' => ['nullable', 'string', 'max:60'],
        ]);

        $tenda->update($data);

        return redirect()->route('struttura.edit', $tenda)->with('ok', "Tenda {$tenda->codice} aggiornata.");
    }

    public function addPosti(Request $request, Tenda $tenda)
    {
        $data = $request->validate(['quanti' => ['required', 'integer', 'min:1', 'max:100']]);

        // continua la numerazione dal massimo numero esistente (portabile pgsql/sqlite)
        $start = (int) $tenda->posti()->pluck('numero')->map(fn ($n) => (int) $n)->max();
        for ($i = 1; $i <= $data['quanti']; $i++) {
            Posto::create(['tenda_id' => $tenda->id, 'numero' => (string) ($start + $i), 'stato' => 'libero']);
        }

        return back()->with('ok', "{$data['quanti']} posti aggiunti alla tenda {$tenda->codice}.");
    }

    public function toggleInagibile(Posto $posto)
    {
        if ($posto->stato === 'occupato') {
            return back()->with('err', 'Posto occupato: fai prima il check-out.');
        }

        $posto->update(['stato' => $posto->stato === 'inagibile' ? 'libero' : 'inagibile']);

        return back()->with('ok', $posto->stato === 'inagibile'
            ? "Posto {$posto->numero} segnato inagibile."
            : "Posto {$posto->numero} reso agibile.");
    }

    public function removePosto(Posto $posto)
    {
        if ($posto->stato === 'occupato') {
            return back()->with('err', 'Posto occupato: fai prima il check-out.');
        }

        $numero = $posto->numero;
        $posto->delete();

        return back()->with('ok', "Posto {$numero} rimosso.");
    }

    public function destroy(Tenda $tenda)
    {
        if ($tenda->posti()->where('stato', 'occupato')->exists()) {
            return back()->with('err', "Tenda {$tenda->codice}: ci sono posti occupati, fai prima il check-out.");
        }

        $codice = $tenda->codice;
        $tenda->delete(); // i posti liberi vanno via in cascata (FK)

        return back()->with('ok', "Tenda {$codice} eliminata.");
    }
}
