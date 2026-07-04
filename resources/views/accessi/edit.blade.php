@extends('layouts.app')

@section('title', 'Modifica accesso')
@section('sezione', 'Modifica accesso al varco')

@section('content')
<main class="wrap">
  <p><a href="{{ route('varco.index') }}" class="back">← Varco</a></p>
  <section class="panel" style="max-width:560px">
    <h2 class="panel-title">Accesso — {{ $accesso->entrata_at?->format('d/m/Y H:i') }}</h2>

    <form method="post" action="{{ route('accessi.update', $accesso) }}">
      @csrf @method('PATCH')
      <div class="two">
        <label class="field"><span>Cognome</span>
          <input name="cognome" value="{{ old('cognome', $accesso->cognome) }}" required></label>
        <label class="field"><span>Nome</span>
          <input name="nome" value="{{ old('nome', $accesso->nome) }}"></label>
      </div>
      @error('cognome')<p class="err">{{ $message }}</p>@enderror

      <div class="two">
        <label class="field"><span>Codice fiscale</span>
          <input name="codice_fiscale" value="{{ old('codice_fiscale', $accesso->codice_fiscale) }}" style="text-transform:uppercase"></label>
        <label class="field"><span>Categoria</span>
          <select name="categoria_id">
            <option value="">—</option>
            @foreach ($categorie as $c)
              <option value="{{ $c->id }}" @selected(old('categoria_id', $accesso->categoria_id) == $c->id)>{{ $c->nome }}</option>
            @endforeach
          </select></label>
      </div>

      <div class="two">
        <label class="field"><span>Ente / appartenenza</span>
          <input name="ente_appartenenza" value="{{ old('ente_appartenenza', $accesso->ente_appartenenza) }}"></label>
        <label class="field"><span>Cellulare</span>
          <input name="telefono" value="{{ old('telefono', $accesso->telefono) }}" inputmode="tel"></label>
      </div>
      <div class="two">
        <label class="field"><span>Documento</span>
          <input name="documento" value="{{ old('documento', $accesso->documento) }}"></label>
        <label class="field"><span>Targa veicolo</span>
          <input name="targa_veicolo" value="{{ old('targa_veicolo', $accesso->targa_veicolo) }}"></label>
      </div>
      <label class="field"><span>Motivo</span>
        <input name="motivo" value="{{ old('motivo', $accesso->motivo) }}"></label>

      <button class="btn btn-block" type="submit">Salva</button>
    </form>

    <form method="post" action="{{ route('accessi.destroy', $accesso) }}" style="margin-top:10px"
          onsubmit="return confirm('Eliminare questo accesso?')">
      @csrf @method('DELETE')
      <button class="btn btn-ghost btn-block" style="color:#8A1C11; border-color:#F3C7C1" type="submit">Elimina accesso</button>
    </form>
  </section>
</main>
@endsection
