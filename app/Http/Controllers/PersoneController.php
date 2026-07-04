<?php

namespace App\Http\Controllers;

use App\Models\Campo;
use App\Models\CategoriaPersona;
use App\Models\Persona;
use Illuminate\Http\Request;

// Scheda persona: modifica dei dati anagrafici dopo il check-in.
// Niente cancellazione se ha storico movimenti (si usa il check-out).
class PersoneController extends Controller
{
    // Lookup per codice fiscale: al check-in, dal CF riconosce un pre-registrato.
    public function lookup(Request $request)
    {
        $cf = mb_strtoupper(trim((string) $request->query('cf', '')));
        if (mb_strlen($cf) < 11) {
            return response()->json(['found' => false]);
        }

        $p = Persona::where('campo_id', Campo::firstOrFail()->id)
            ->where('codice_fiscale', $cf)->where('stato', '!=', 'presente')->first();

        if (! $p) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'        => true,
            'cognome'      => $p->cognome,
            'nome'         => $p->nome,
            'categoria_id' => $p->categoria_id,
            'telefono'     => $p->telefono,
        ]);
    }

    // Ricerca persone già nel campo (non presenti) da assegnare a una tenda.
    public function cerca(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $like = '%'.mb_strtolower($q).'%';
        $persone = Persona::with('categoria')
            ->where('campo_id', Campo::firstOrFail()->id)->where('stato', '!=', 'presente')
            ->where(fn ($w) => $w
                ->whereRaw('lower(cognome) like ?', [$like])
                ->orWhereRaw('lower(nome) like ?', [$like])
                ->orWhereRaw('lower(codice_fiscale) like ?', [$like]))
            ->orderBy('cognome')->orderBy('nome')->limit(20)->get();

        return response()->json($persone->map(fn ($p) => [
            'id'           => $p->id,
            'cognome'      => $p->cognome,
            'nome'         => $p->nome,
            'cf'           => $p->codice_fiscale,
            'categoria'    => $p->categoria?->nome,
            'categoria_id' => $p->categoria_id,
            'telefono'     => $p->telefono,
        ]));
    }

    public function edit(Persona $persona)
    {
        return view('persone.edit', [
            'persona'   => $persona->load(['categoria', 'posto.tenda']),
            'categorie' => CategoriaPersona::attive()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Persona $persona)
    {
        $data = $request->validate([
            'cognome'            => ['required', 'string', 'max:255'],
            'nome'               => ['required', 'string', 'max:255'],
            'categoria_id'       => ['nullable', 'integer', 'exists:categorie_persona,id'],
            'sesso'              => ['nullable', 'string', 'max:20'],
            'data_nascita'       => ['nullable', 'date'],
            'telefono'           => ['nullable', 'string', 'max:40'],
            'comune_provenienza' => ['nullable', 'string', 'max:255'],
            'ente_appartenenza'  => ['nullable', 'string', 'max:255'],
            'specializzazione'   => ['nullable', 'string', 'max:255'],
            'patente'            => ['nullable', 'string', 'max:60'],
            'codice_fiscale'     => ['nullable', 'string', 'max:32'],
            'documento_tipo'     => ['nullable', 'string', 'max:40'],
            'documento_numero'   => ['nullable', 'string', 'max:255'],
            'note_sanitarie'     => ['nullable', 'string', 'max:1000'],
            'allergie_dieta'     => ['nullable', 'string', 'max:1000'],
        ]);

        $persona->update($data);

        return redirect()->route('persone.edit', $persona)->with('ok', 'Scheda aggiornata.');
    }

    public function destroy(Persona $persona)
    {
        if ($persona->movimenti()->exists()) {
            return back()->with('err', 'Questa persona ha uno storico movimenti: usa il check-out, non l\'eliminazione.');
        }

        $persona->delete();

        return redirect()->route('dashboard')->with('ok', 'Persona eliminata.');
    }
}
