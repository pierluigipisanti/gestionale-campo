<?php

namespace App\Http\Controllers;

use App\Actions\ChiudiUscita;
use App\Actions\RegistraEntrata;
use App\Models\Accesso;
use App\Models\Campo;
use App\Models\CategoriaPersona;
use Illuminate\Http\Request;

// Varco: la funzione più usata del campo. Registra entrata e mostra chi è dentro ora.
class VarcoController extends Controller
{
    public function index()
    {
        $campo = Campo::firstOrFail();

        return view('varco', [
            'campo'     => $campo,
            'categorie' => CategoriaPersona::attive()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request, RegistraEntrata $registra)
    {
        $dati = $request->validate([
            'cognome'           => ['required', 'string', 'max:255'],
            'nome'              => ['nullable', 'string', 'max:255'],
            'codice_fiscale'    => ['nullable', 'string', 'max:32'],
            'telefono'          => ['nullable', 'string', 'max:40'],
            'categoria_id'      => ['nullable', 'integer', 'exists:categorie_persona,id'],
            'ente_appartenenza' => ['nullable', 'string', 'max:255'],
            'documento'         => ['nullable', 'string', 'max:255'],
            'targa_veicolo'     => ['nullable', 'string', 'max:32'],
            'motivo'            => ['nullable', 'string', 'max:500'],
        ]);

        $registra(Campo::firstOrFail()->id, $dati, auth()->id());

        return back()->with('ok', trim("Entrata registrata: {$dati['cognome']} ".($dati['nome'] ?? '')).'.');
    }

    public function uscita(Request $request, ChiudiUscita $chiudi)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return back()->with('err', 'Scansiona il documento o scrivi cognome / codice fiscale.');
        }

        $ql = mb_strtolower($q);
        $aperti = Accesso::dentro()->where('campo_id', Campo::firstOrFail()->id)
            ->where(fn ($w) => $w
                ->whereRaw('lower(codice_fiscale) = ?', [$ql])
                ->orWhereRaw('lower(cognome) = ?', [$ql])
                ->orWhereRaw("lower(trim(cognome || ' ' || coalesce(nome, ''))) = ?", [$ql]))
            ->get();

        if ($aperti->isEmpty()) {
            return back()->with('err', "Nessuna persona al varco corrisponde a «{$q}».");
        }
        if ($aperti->count() > 1) {
            return back()->with('err', "Più persone corrispondono a «{$q}»: usa il codice fiscale.");
        }

        $accesso = $aperti->first();
        try {
            $chiudi($accesso);
        } catch (\RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }

        return redirect()->route('varco.index')->with('ok', trim("Uscita registrata: {$accesso->cognome} {$accesso->nome}").'.');
    }
}
