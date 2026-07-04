@extends('layouts.app')

@section('title', 'Posto')
@section('sezione', 'Dettaglio posto')

@push('styles')
<style>
  .occ-nome{font-size:22px; font-weight:800; letter-spacing:-.01em}
  .occ-meta{margin:6px 0 2px; color:var(--soft); font-size:14px}
  .occ-meta .chip{margin-right:8px}
  .doc{margin:4px 0 0; color:var(--soft); font-size:14px}
  .hint{margin:14px 0 0; color:var(--soft); font-size:13px}
</style>
@endpush

@section('content')
<main class="wrap">
  <p><a href="{{ route('posti.index') }}" class="back">← Posti</a></p>
  <section class="panel" style="max-width:560px">
    <h2 class="panel-title">{{ $posto->tenda->codice }} · posto {{ $posto->numero }}</h2>

    @if ($occupante)
      <div class="occ-nome">{{ $occupante->cognome }} {{ $occupante->nome }}</div>
      <div class="occ-meta">
        @if ($occupante->categoria)<span class="chip">{{ $occupante->categoria->nome }}</span>@endif
        @if ($occupante->ultimo_movimento_at)dal {{ $occupante->ultimo_movimento_at->format('d/m/Y H:i') }}@endif
        · <a href="{{ route('persone.edit', $occupante) }}">modifica scheda</a>
      </div>
      @if ($occupante->documento_tipo)
        <p class="doc">Documento: {{ $occupante->documento_tipo }} {{ $occupante->documento_numero }}</p>
      @endif

      <form method="post" action="{{ route('posti.checkout', $posto) }}" style="margin-top:18px"
            onsubmit="return confirm('Confermi il check-out di {{ $occupante->cognome }} {{ $occupante->nome }}?')">
        @csrf
        <button class="btn btn-block" type="submit">Registra uscita (check-out)</button>
      </form>

      @if ($liberi->isNotEmpty())
        <form method="post" action="{{ route('posti.trasferisci', $posto) }}" style="margin-top:10px">
          @csrf
          <label class="field" style="margin-bottom:8px">
            <span>Trasferisci in un altro posto</span>
            <select name="nuovo_posto_id" required>
              <option value="">Scegli posto libero…</option>
              @foreach ($liberi->groupBy(fn ($p) => $p->tenda->codice) as $cod => $set)
                <optgroup label="Tenda {{ $cod }}">
                  @foreach ($set as $p)
                    <option value="{{ $p->id }}">{{ $cod }} · posto {{ $p->numero }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </label>
          <button class="btn btn-ghost btn-block" type="submit">Trasferisci</button>
        </form>
      @endif
    @else
      <p style="margin:0">Questo posto è libero.
        <a href="{{ route('posti.checkin.form', $posto) }}">Fai il check-in →</a></p>
    @endif
  </section>
</main>
@endsection
