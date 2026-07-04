<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPersona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Gestione categorie di persona (D7) — solo admin. Il vocabolario lo cura chi usa
// il campo: aggiunge righe, disattiva quelle non più utili. Non si cancella: si
// disattiva, così persone e accessi storici restano coerenti.
class CategorieController extends Controller
{
    public function index()
    {
        return view('categorie.index', [
            'categorie' => CategoriaPersona::withCount(['persone', 'accessi'])
                ->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'  => ['required', 'string', 'max:100', 'unique:categorie_persona,nome'],
            'sigla' => ['nullable', 'string', 'max:20'],
        ]);

        CategoriaPersona::create($data + ['attiva' => true]);

        return redirect()->route('categorie.index')->with('ok', "Categoria \"{$data['nome']}\" aggiunta.");
    }

    public function update(Request $request, CategoriaPersona $categoria)
    {
        $data = $request->validate([
            'nome'  => ['required', 'string', 'max:100', Rule::unique('categorie_persona', 'nome')->ignore($categoria->id)],
            'sigla' => ['nullable', 'string', 'max:20'],
        ]);

        $categoria->update($data);

        return redirect()->route('categorie.index')->with('ok', "Categoria aggiornata: \"{$categoria->nome}\".");
    }

    public function destroy(CategoriaPersona $categoria)
    {
        if ($categoria->persone()->exists() || $categoria->accessi()->exists()) {
            return back()->with('err', "\"{$categoria->nome}\" è in uso: disattivala invece di eliminarla.");
        }

        $nome = $categoria->nome;
        $categoria->delete();

        return back()->with('ok', "Categoria \"{$nome}\" eliminata.");
    }

    public function toggle(CategoriaPersona $categoria)
    {
        $categoria->update(['attiva' => ! $categoria->attiva]);

        return back()->with('ok', $categoria->attiva
            ? "\"{$categoria->nome}\" riattivata."
            : "\"{$categoria->nome}\" disattivata (non compare più nelle scelte, lo storico resta).");
    }
}
