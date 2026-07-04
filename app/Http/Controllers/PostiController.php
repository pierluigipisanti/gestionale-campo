<?php

namespace App\Http\Controllers;

use App\Actions\EseguiCheckIn;
use App\Actions\EseguiCheckOut;
use App\Actions\EseguiTrasferimento;
use App\Models\Campo;
use App\Models\CategoriaPersona;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Http\Request;
use RuntimeException;

// Griglia posti (occupazione a colpo d'occhio) + check-in / check-out dal posto.
class PostiController extends Controller
{
    public function index()
    {
        $campo = Campo::firstOrFail();

        $tende = Tenda::where('campo_id', $campo->id)
            ->with(['posti' => fn ($q) => $q->orderByRaw('length(numero), numero')])
            ->orderBy('settore')->orderBy('fila')->orderBy('codice')->get();

        $occupanti = Persona::where('campo_id', $campo->id)->where('stato', 'presente')
            ->whereNotNull('posto_id')->get()->keyBy('posto_id');

        $conteggio = Posto::whereHas('tenda', fn ($q) => $q->where('campo_id', $campo->id))
            ->selectRaw('stato, count(*) as n')->groupBy('stato')->pluck('n', 'stato');

        return view('posti.index', compact('campo', 'tende', 'occupanti', 'conteggio'));
    }

    public function checkinForm(Posto $posto)
    {
        if ($posto->stato !== 'libero') {
            return redirect()->route('posti.index')->with('err', 'Posto non disponibile.');
        }

        return view('posti.checkin', [
            'posto'     => $posto->load('tenda'),
            'categorie' => CategoriaPersona::attive()->orderBy('nome')->get(),
        ]);
    }

    public function checkinStore(Posto $posto, Request $request, EseguiCheckIn $checkin)
    {
        $dati = $request->validate([
            'persona_id'         => ['nullable', 'integer'],
            'cognome'            => ['required', 'string', 'max:255'],
            'nome'               => ['required', 'string', 'max:255'],
            'categoria_id'       => ['nullable', 'integer', 'exists:categorie_persona,id'],
            'sesso'              => ['nullable', 'string', 'max:20'],
            'data_nascita'       => ['nullable', 'date'],
            'telefono'           => ['nullable', 'string', 'max:40'],
            'comune_provenienza' => ['nullable', 'string', 'max:255'],
            'note_sanitarie'     => ['nullable', 'string', 'max:1000'],
            'allergie_dieta'     => ['nullable', 'string', 'max:1000'],
            'documento_tipo'     => ['nullable', 'string', 'max:40'],
            'documento_numero'   => ['nullable', 'string', 'max:255'],
            'codice_fiscale'     => ['nullable', 'string', 'max:32'],
        ]);

        $campoId = Campo::firstOrFail()->id;
        $cf = isset($dati['codice_fiscale']) ? mb_strtoupper(trim($dati['codice_fiscale'])) : '';

        // riusa la persona scelta dalla ricerca (persona_id), altrimenti per CF, altrimenti nuova
        $persona = null;
        if (! empty($dati['persona_id'])) {
            $persona = Persona::where('campo_id', $campoId)->where('stato', '!=', 'presente')->find($dati['persona_id']);
        }
        if (! $persona && $cf !== '') {
            $persona = Persona::where('campo_id', $campoId)->where('codice_fiscale', $cf)->where('stato', '!=', 'presente')->first();
        }
        $persona = $persona ?? new Persona(['campo_id' => $campoId, 'stato' => 'pre_registrato']);
        $eraNuovo = ! $persona->exists;

        $persona->fill([
            'categoria_id'       => $dati['categoria_id'] ?? $persona->categoria_id,
            'cognome'            => $dati['cognome'],
            'nome'               => $dati['nome'],
            'sesso'              => $dati['sesso'] ?? $persona->sesso,
            'data_nascita'       => $dati['data_nascita'] ?? $persona->data_nascita,
            'telefono'           => $dati['telefono'] ?? $persona->telefono,
            'comune_provenienza' => $dati['comune_provenienza'] ?? $persona->comune_provenienza,
            'note_sanitarie'     => $dati['note_sanitarie'] ?? $persona->note_sanitarie,
            'allergie_dieta'     => $dati['allergie_dieta'] ?? $persona->allergie_dieta,
        ]);
        $persona->save();

        try {
            $checkin($persona, $posto, auth()->id(), null, [
                'tipo'           => $dati['documento_tipo'] ?? null,
                'numero'         => $dati['documento_numero'] ?? null,
                'codice_fiscale' => $dati['codice_fiscale'] ?? null,
            ]);
        } catch (RuntimeException $e) {
            if ($eraNuovo) {
                $persona->delete(); // annulla solo se creato ora (non cancellare un pre-registrato)
            }

            return redirect()->route('posti.index')->with('err', $e->getMessage());
        }

        return redirect()->route('posti.index')
            ->with('ok', "Check-in: {$persona->cognome} {$persona->nome} → {$posto->tenda->codice}/{$posto->numero}.");
    }

    public function show(Posto $posto)
    {
        $posto->load('tenda');

        $liberi = Posto::with('tenda')
            ->whereHas('tenda', fn ($q) => $q->where('campo_id', $posto->tenda->campo_id))
            ->where('stato', 'libero')->where('id', '!=', $posto->id)
            ->orderBy('tenda_id')->orderByRaw('length(numero), numero')->get();

        return view('posti.show', [
            'posto'     => $posto,
            'occupante' => Persona::with('categoria')->where('posto_id', $posto->id)
                                ->where('stato', 'presente')->first(),
            'liberi'    => $liberi,
        ]);
    }

    public function trasferisci(Posto $posto, Request $request, EseguiTrasferimento $trasferimento)
    {
        $data = $request->validate([
            'nuovo_posto_id' => ['required', 'integer', 'exists:posti,id'],
        ]);

        $occupante = Persona::where('posto_id', $posto->id)->where('stato', 'presente')->first();
        if (! $occupante) {
            return redirect()->route('posti.index')->with('err', 'Nessun occupante da trasferire.');
        }

        $nuovo = Posto::with('tenda')->findOrFail($data['nuovo_posto_id']);

        try {
            $trasferimento($occupante, $nuovo, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }

        return redirect()->route('posti.show', $nuovo)
            ->with('ok', "Trasferito: {$occupante->cognome} {$occupante->nome} → {$nuovo->tenda->codice}/{$nuovo->numero}.");
    }

    public function checkout(Posto $posto, EseguiCheckOut $checkout)
    {
        $occupante = Persona::where('posto_id', $posto->id)->where('stato', 'presente')->first();

        if (! $occupante) {
            return redirect()->route('posti.index')->with('err', 'Nessun occupante da dimettere.');
        }

        $checkout($occupante, auth()->id());

        return redirect()->route('posti.index')
            ->with('ok', "Check-out: {$occupante->cognome} {$occupante->nome}.");
    }
}
