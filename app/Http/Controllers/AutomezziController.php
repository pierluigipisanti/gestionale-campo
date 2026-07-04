<?php

namespace App\Http\Controllers;

use App\Actions\EntrataAutomezzo;
use App\Actions\UscitaAutomezzo;
use App\Models\Automezzo;
use App\Models\Campo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

// Automezzi: registro (CRUD) + varco entrata/uscita.
class AutomezziController extends Controller
{
    // ---- registro ----

    public function index()
    {
        $campo = Campo::firstOrFail();

        return view('automezzi.index', [
            'automezzi' => Automezzo::where('campo_id', $campo->id)->orderBy('targa')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $campoId = Campo::firstOrFail()->id;
        $data = $this->validated($request, $campoId);
        Automezzo::create($data + ['campo_id' => $campoId]);

        return redirect()->route('automezzi.index')->with('ok', "Automezzo {$data['targa']} registrato.");
    }

    public function edit(Automezzo $automezzo)
    {
        return view('automezzi.edit', compact('automezzo'));
    }

    public function update(Request $request, Automezzo $automezzo)
    {
        $automezzo->update($this->validated($request, $automezzo->campo_id, $automezzo->id));

        return redirect()->route('automezzi.index')->with('ok', "Automezzo {$automezzo->targa} aggiornato.");
    }

    public function destroy(Automezzo $automezzo)
    {
        if ($automezzo->stato === 'dentro') {
            return back()->with('err', "L'automezzo {$automezzo->targa} è dentro: registra prima l'uscita.");
        }

        $targa = $automezzo->targa;
        $automezzo->delete();

        return back()->with('ok', "Automezzo {$targa} eliminato.");
    }

    // ---- varco automezzi ----

    public function varco()
    {
        $campo = Campo::firstOrFail();

        return view('varco-automezzi', [
            'fuori'  => Automezzo::where('campo_id', $campo->id)->where('stato', 'fuori')->orderBy('targa')->get(),
            'dentro' => Automezzo::where('campo_id', $campo->id)->dentro()->orderBy('targa')->get(),
        ]);
    }

    public function entrata(Request $request, EntrataAutomezzo $azione)
    {
        $data = $request->validate([
            'targa'             => ['required', 'string', 'max:20'],
            'tipo'              => ['nullable', 'string', 'max:60'],
            'referente'         => ['nullable', 'string', 'max:255'],
            'telefono'          => ['nullable', 'string', 'max:40'],
            'ente_appartenenza' => ['nullable', 'string', 'max:255'],
        ]);

        $campoId = Campo::firstOrFail()->id;
        $targa = mb_strtoupper(trim($data['targa']));

        // crea al volo se non esiste, altrimenti riusa (senza sovrascrivere con vuoti)
        $auto = Automezzo::firstOrNew(['campo_id' => $campoId, 'targa' => $targa]);
        $auto->fill(array_filter([
            'tipo'              => $data['tipo'] ?? null,
            'referente'         => $data['referente'] ?? null,
            'telefono'          => $data['telefono'] ?? null,
            'ente_appartenenza' => $data['ente_appartenenza'] ?? null,
        ]));
        if (! $auto->exists) {
            $auto->stato = 'fuori';
        }
        $auto->save();

        try {
            $azione($auto, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }

        return redirect()->route('varco.automezzi')->with('ok', "Entrata registrata: {$auto->targa}.");
    }

    public function uscita(Request $request, UscitaAutomezzo $azione)
    {
        $targa = mb_strtoupper(trim((string) $request->input('targa', '')));
        if ($targa === '') {
            return back()->with('err', 'Scansiona il QR o scrivi la targa.');
        }

        $auto = Automezzo::where('campo_id', Campo::firstOrFail()->id)->where('targa', $targa)->dentro()->first();
        if (! $auto) {
            return back()->with('err', "Nessun automezzo dentro con targa «{$targa}».");
        }

        try {
            $azione($auto, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }

        return redirect()->route('varco.automezzi')->with('ok', "Uscita registrata: {$auto->targa}.");
    }

    private function validated(Request $request, int $campoId, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'targa'             => ['required', 'string', 'max:20',
                Rule::unique('automezzi', 'targa')->where(fn ($q) => $q->where('campo_id', $campoId))->ignore($ignoreId)],
            'tipo'              => ['nullable', 'string', 'max:60'],
            'descrizione'       => ['nullable', 'string', 'max:255'],
            'ente_appartenenza' => ['nullable', 'string', 'max:255'],
            'referente'         => ['nullable', 'string', 'max:255'],
            'telefono'          => ['nullable', 'string', 'max:40'],
        ]);
        $data['targa'] = mb_strtoupper(trim($data['targa']));

        return $data;
    }
}
