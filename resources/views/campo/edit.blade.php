@extends('layouts.app')

@section('title', 'Campo')
@section('sezione', 'Dati del campo')

@section('content')
<main class="wrap">
  <section class="panel" style="max-width:560px">
    <h2 class="panel-title">Ente e campo</h2>
    <form method="post" action="{{ route('campo.update') }}">
      @csrf @method('PATCH')

      <p style="margin:0 0 8px; font-weight:700; color:var(--soft); font-size:13px; letter-spacing:.06em; text-transform:uppercase">Ente</p>
      <div class="two">
        <label class="field"><span>Nome ente</span>
          <input name="ente_nome" value="{{ old('ente_nome', $campo->ente->nome) }}" required></label>
        <label class="field"><span>Tipo</span>
          <input name="ente_tipo" value="{{ old('ente_tipo', $campo->ente->tipo) }}" placeholder="comune, associazione…"></label>
      </div>
      @error('ente_nome')<p class="err">{{ $message }}</p>@enderror

      <p style="margin:14px 0 8px; font-weight:700; color:var(--soft); font-size:13px; letter-spacing:.06em; text-transform:uppercase">Campo</p>
      <div class="two">
        <label class="field"><span>Nome campo</span>
          <input name="campo_nome" value="{{ old('campo_nome', $campo->nome) }}" required></label>
        <label class="field"><span>Comune</span>
          <input name="campo_comune" value="{{ old('campo_comune', $campo->comune) }}"></label>
      </div>
      @error('campo_nome')<p class="err">{{ $message }}</p>@enderror

      <button class="btn btn-block" type="submit">Salva</button>
    </form>
  </section>
</main>
@endsection
