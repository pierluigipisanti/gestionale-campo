@extends('layouts.app')

@section('title', 'Modifica automezzo')
@section('sezione', 'Modifica automezzo')

@section('content')
<main class="wrap">
  <p><a href="{{ route('automezzi.index') }}" class="back">← Automezzi</a></p>
  <section class="panel" style="max-width:560px">
    <h2 class="panel-title">Automezzo {{ $automezzo->targa }} · <span style="color:var(--soft)">{{ $automezzo->stato }}</span></h2>
    <form method="post" action="{{ route('automezzi.update', $automezzo) }}">
      @csrf @method('PATCH')
      <label class="field"><span>Targa</span>
        <input name="targa" value="{{ old('targa', $automezzo->targa) }}" required style="text-transform:uppercase"></label>
      @error('targa')<p class="err">{{ $message }}</p>@enderror
      <div class="two">
        <label class="field"><span>Tipo</span>
          <input name="tipo" value="{{ old('tipo', $automezzo->tipo) }}"></label>
        <label class="field"><span>Ente</span>
          <input name="ente_appartenenza" value="{{ old('ente_appartenenza', $automezzo->ente_appartenenza) }}"></label>
      </div>
      <div class="two">
        <label class="field"><span>Referente</span>
          <input name="referente" value="{{ old('referente', $automezzo->referente) }}"></label>
        <label class="field"><span>Cellulare</span>
          <input name="telefono" value="{{ old('telefono', $automezzo->telefono) }}" inputmode="tel"></label>
      </div>
      <label class="field"><span>Descrizione</span>
        <input name="descrizione" value="{{ old('descrizione', $automezzo->descrizione) }}"></label>
      <button class="btn btn-block" type="submit">Salva</button>
    </form>
  </section>
</main>
@endsection
