<?php

namespace App\Http\Controllers;

use App\Models\Accesso;
use App\Models\Automezzo;
use App\Models\Campo;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Http\Request;

// Dashboard: quello che serve sapere a colpo d'occhio + ricerca nominativi.
class DashboardController extends Controller
{
    public function index()
    {
        $campo = Campo::firstOrFail();

        $posti = Posto::whereHas('tenda', fn ($q) => $q->where('campo_id', $campo->id))
            ->selectRaw('stato, count(*) as n')->groupBy('stato')->pluck('n', 'stato');

        return view('dashboard', [
            'campo'     => $campo,
            'presenti'  => Persona::where('campo_id', $campo->id)->where('stato', 'presente')->count(),
            'liberi'    => (int) ($posti['libero'] ?? 0),
            'occupati'  => (int) ($posti['occupato'] ?? 0),
            'inagibili' => (int) ($posti['inagibile'] ?? 0),
            'tende'     => Tenda::where('campo_id', $campo->id)->count(),
            'alVarco'   => Accesso::where('campo_id', $campo->id)->dentro()->count(),
            'automezzi' => Automezzo::where('campo_id', $campo->id)->dentro()->count(),
            'breakdown' => Persona::query()
                ->where('persone.campo_id', $campo->id)->where('persone.stato', 'presente')
                ->leftJoin('categorie_persona', 'persone.categoria_id', '=', 'categorie_persona.id')
                ->selectRaw("coalesce(categorie_persona.nome, 'Senza categoria') as etichetta, count(*) as n")
                ->groupByRaw("coalesce(categorie_persona.nome, 'Senza categoria')")
                ->orderByDesc('n')->get(),
        ]);
    }

    public function cerca(Request $request)
    {
        $campo = Campo::firstOrFail();
        $q = trim((string) $request->query('q', ''));

        $persone = collect();
        $accessi = collect();

        if (mb_strlen($q) >= 2) {
            // case-insensitive portabile (pgsql e sqlite): lower() + like
            $like = '%'.mb_strtolower($q).'%';
            $filtro = fn ($w) => $w->whereRaw('lower(cognome) like ?', [$like])
                ->orWhereRaw('lower(nome) like ?', [$like])
                ->orWhereRaw('lower(codice_fiscale) like ?', [$like]);

            $persone = Persona::with(['posto.tenda', 'categoria'])
                ->where('campo_id', $campo->id)->where($filtro)
                ->orderBy('cognome')->orderBy('nome')->limit(50)->get();

            $accessi = Accesso::with('categoria')
                ->where('campo_id', $campo->id)->where($filtro)
                ->orderByDesc('entrata_at')->limit(50)->get();
        }

        return view('cerca', compact('q', 'persone', 'accessi'));
    }
}
