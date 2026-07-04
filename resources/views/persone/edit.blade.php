@extends('layouts.app')

@section('title', 'Scheda persona')
@section('sezione', 'Scheda persona')

@section('content')
<main class="wrap">
  <p><a href="{{ url()->previous() }}" class="back">← Indietro</a></p>
  <section class="panel" style="max-width:640px">
    <h2 class="panel-title">
      {{ $persona->cognome }} {{ $persona->nome }}
      @if ($persona->posto) · {{ $persona->posto->tenda?->codice }}/{{ $persona->posto->numero }} @endif
      · <span style="color:var(--soft)">{{ str_replace('_', ' ', $persona->stato) }}</span>
    </h2>

    <form method="post" action="{{ route('persone.update', $persona) }}">
      @csrf @method('PATCH')
      <div class="two">
        <label class="field"><span>Cognome</span>
          <input name="cognome" value="{{ old('cognome', $persona->cognome) }}" required></label>
        <label class="field"><span>Nome</span>
          <input name="nome" value="{{ old('nome', $persona->nome) }}" required></label>
      </div>
      @error('cognome')<p class="err">{{ $message }}</p>@enderror
      @error('nome')<p class="err">{{ $message }}</p>@enderror

      <div class="two">
        <label class="field"><span>Categoria</span>
          <select name="categoria_id">
            <option value="">—</option>
            @foreach ($categorie as $c)
              <option value="{{ $c->id }}" @selected(old('categoria_id', $persona->categoria_id) == $c->id)>{{ $c->nome }}</option>
            @endforeach
          </select></label>
        <label class="field"><span>Codice fiscale</span>
          <input name="codice_fiscale" value="{{ old('codice_fiscale', $persona->codice_fiscale) }}" style="text-transform:uppercase"></label>
      </div>

      <div class="two">
        <label class="field"><span>Sesso</span>
          <select name="sesso">
            <option value="">—</option>
            <option value="M" @selected(old('sesso', $persona->sesso)=='M')>M</option>
            <option value="F" @selected(old('sesso', $persona->sesso)=='F')>F</option>
          </select></label>
        <label class="field"><span>Data di nascita</span>
          <input type="date" name="data_nascita" value="{{ old('data_nascita', optional($persona->data_nascita)->toDateString()) }}"></label>
      </div>

      <div class="two">
        <label class="field"><span>Cellulare</span>
          <input name="telefono" value="{{ old('telefono', $persona->telefono) }}" inputmode="tel"></label>
        <label class="field"><span>Ente appartenenza</span>
          <input name="ente_appartenenza" value="{{ old('ente_appartenenza', $persona->ente_appartenenza) }}"></label>
      </div>
      <div class="two">
        <label class="field"><span>Specializzazione</span>
          <input name="specializzazione" value="{{ old('specializzazione', $persona->specializzazione) }}" placeholder="es. logistica, sanitario"></label>
        <label class="field"><span>Patente</span>
          <input name="patente" value="{{ old('patente', $persona->patente) }}" placeholder="es. B, C"></label>
      </div>
      <label class="field"><span>Comune di provenienza</span>
        <input name="comune_provenienza" value="{{ old('comune_provenienza', $persona->comune_provenienza) }}"></label>

      <div class="two">
        <label class="field"><span>Tipo documento</span>
          <input name="documento_tipo" value="{{ old('documento_tipo', $persona->documento_tipo) }}"></label>
        <label class="field"><span>Numero documento</span>
          <input name="documento_numero" value="{{ old('documento_numero', $persona->documento_numero) }}"></label>
      </div>

      <label class="field"><span>Note sanitarie / fragilità</span>
        <input name="note_sanitarie" value="{{ old('note_sanitarie', $persona->note_sanitarie) }}"></label>
      <label class="field"><span>Allergie / dieta</span>
        <input name="allergie_dieta" value="{{ old('allergie_dieta', $persona->allergie_dieta) }}"></label>

      <button class="btn btn-block" type="submit">Salva scheda</button>
    </form>

    <form method="post" action="{{ route('persone.destroy', $persona) }}" style="margin-top:10px"
          onsubmit="return confirm('Eliminare definitivamente questa persona?')">
      @csrf @method('DELETE')
      <button class="btn btn-ghost btn-block" style="color:#8A1C11; border-color:#F3C7C1" type="submit">Elimina persona</button>
    </form>
    <p style="margin:8px 0 0; color:var(--soft); font-size:12px">L'eliminazione è consentita solo se non ha storico movimenti; altrimenti usa il check-out.</p>
  </section>
</main>
@endsection
