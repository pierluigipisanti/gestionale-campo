<?php

namespace App\Http\Controllers;

use App\Models\Accesso;
use App\Models\CategoriaPersona;
use Illuminate\Http\Request;

// Modifica/elimina di un accesso al varco (correzione di una registrazione).
class AccessiController extends Controller
{
    public function edit(Accesso $accesso)
    {
        return view('accessi.edit', [
            'accesso'   => $accesso,
            'categorie' => CategoriaPersona::attive()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Accesso $accesso)
    {
        $data = $request->validate([
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

        $accesso->update($data);

        return redirect()->route('varco.index')->with('ok', "Accesso aggiornato: {$accesso->cognome}.");
    }

    public function destroy(Accesso $accesso)
    {
        $chi = trim("{$accesso->cognome} {$accesso->nome}");
        $accesso->delete();

        return back()->with('ok', "Accesso di {$chi} eliminato.");
    }
}
