<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Gestione utenti — solo admin (gate 'admin' applicato sulle rotte).
class UtentiController extends Controller
{
    public function index()
    {
        return view('utenti.index', ['utenti' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'ruolo'    => ['required', Rule::in(['admin', 'operatore'])],
        ]);

        User::create($data); // password: cast 'hashed' sul model

        return redirect()->route('utenti.index')->with('ok', "Utente {$data['name']} creato.");
    }

    public function edit(User $utente)
    {
        return view('utenti.edit', ['utente' => $utente]);
    }

    public function update(Request $request, User $utente)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($utente->id)],
            'ruolo'    => ['required', Rule::in(['admin', 'operatore'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        // non lasciare il sistema senza amministratori
        if ($utente->isAdmin() && $data['ruolo'] !== 'admin' && User::where('ruolo', 'admin')->count() <= 1) {
            return back()->with('err', 'Deve restare almeno un amministratore.');
        }

        $utente->fill(['name' => $data['name'], 'email' => $data['email'], 'ruolo' => $data['ruolo']]);
        if (! empty($data['password'])) {
            $utente->password = $data['password'];
        }
        $utente->save();

        return redirect()->route('utenti.index')->with('ok', "Utente {$utente->name} aggiornato.");
    }

    public function destroy(User $utente)
    {
        if ($utente->id === auth()->id()) {
            return back()->with('err', 'Non puoi eliminare te stesso.');
        }
        if ($utente->isAdmin() && User::where('ruolo', 'admin')->count() <= 1) {
            return back()->with('err', 'Deve restare almeno un amministratore.');
        }

        $nome = $utente->name;
        $utente->delete();

        return back()->with('ok', "Utente {$nome} eliminato.");
    }
}
