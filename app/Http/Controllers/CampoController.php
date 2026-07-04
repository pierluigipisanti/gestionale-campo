<?php

namespace App\Http\Controllers;

use App\Models\Campo;
use Illuminate\Http\Request;

// Dati del campo e dell'ente — solo admin. Single-tenant: un ente, un campo.
class CampoController extends Controller
{
    public function edit()
    {
        return view('campo.edit', ['campo' => Campo::with('ente')->firstOrFail()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ente_nome'    => ['required', 'string', 'max:255'],
            'ente_tipo'    => ['nullable', 'string', 'max:100'],
            'campo_nome'   => ['required', 'string', 'max:255'],
            'campo_comune' => ['nullable', 'string', 'max:255'],
        ]);

        $campo = Campo::with('ente')->firstOrFail();
        $campo->ente->update(['nome' => $data['ente_nome'], 'tipo' => $data['ente_tipo'] ?? null]);
        $campo->update(['nome' => $data['campo_nome'], 'comune' => $data['campo_comune'] ?? null]);

        return redirect()->route('campo.edit')->with('ok', 'Dati del campo aggiornati.');
    }
}
