@extends('layouts.app')

@section('title', 'Ricerca')
@section('sezione', 'Ricerca')

@push('styles')
<style>
  .cerca-big{display:flex; gap:10px; max-width:1120px; margin:20px auto 16px; padding:0 24px}
  .cerca-big input{flex:1; padding:14px 16px; font-size:16px; border:1.5px solid var(--line); border-radius:10px; font-family:inherit}
  .cerca-big input:focus{outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.15)}
  table.res{width:100%; border-collapse:collapse; font-size:15px}
  table.res th{text-align:left; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; padding:8px 10px; border-bottom:1px solid var(--line)}
  table.res td{padding:9px 10px; border-bottom:1px solid var(--line)}
  .vuoto{color:var(--soft); margin:0}
  .hint{color:var(--soft); max-width:1120px; margin:0 auto; padding:0 24px}
  .stato-chip{font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:2px 8px; border-radius:20px; background:#EEF1F5}
  .stato-chip.presente,.stato-chip.dentro{background:#E6F3EC; color:#146c3a}
</style>
@endpush

@section('content')
<main>
  <form class="cerca-big" method="get" action="{{ route('cerca') }}">
    <input name="q" value="{{ $q }}" placeholder="Cognome, nome o codice fiscale…" autofocus aria-label="Cerca">
    <button class="btn" type="submit">Cerca</button>
  </form>

  @if (mb_strlen($q) < 2)
    <p class="hint">Scrivi almeno 2 caratteri per cercare.</p>
  @else
    <div class="wrap">
      <section class="panel" style="margin-bottom:16px">
        <h2 class="panel-title">Alloggiati ({{ $persone->count() }})</h2>
        @if ($persone->isEmpty())
          <p class="vuoto">Nessun alloggiato trovato per "{{ $q }}".</p>
        @else
          <table class="res">
            <thead><tr><th>Cognome</th><th>Nome</th><th>Categoria</th><th>Stato</th><th>Posto</th><th>Codice fiscale</th></tr></thead>
            <tbody>
              @foreach ($persone as $p)
                <tr>
                  <td><a href="{{ route('persone.edit', $p) }}"><strong>{{ $p->cognome }}</strong></a></td>
                  <td>{{ $p->nome }}</td>
                  <td>{{ $p->categoria?->nome }}</td>
                  <td><span class="stato-chip {{ $p->stato }}">{{ str_replace('_', ' ', $p->stato) }}</span></td>
                  <td>@if ($p->posto)<a href="{{ route('posti.show', $p->posto) }}">{{ $p->posto->tenda?->codice }}/{{ $p->posto->numero }}</a>@endif</td>
                  <td>{{ $p->codice_fiscale }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </section>

      <section class="panel">
        <h2 class="panel-title">Al varco / transiti ({{ $accessi->count() }})</h2>
        @if ($accessi->isEmpty())
          <p class="vuoto">Nessun accesso trovato per "{{ $q }}".</p>
        @else
          <table class="res">
            <thead><tr><th>Cognome</th><th>Nome</th><th>Categoria</th><th>Ente</th><th>Stato</th><th>Entrata</th></tr></thead>
            <tbody>
              @foreach ($accessi as $a)
                <tr>
                  <td><a href="{{ route('accessi.edit', $a) }}"><strong>{{ $a->cognome }}</strong></a></td>
                  <td>{{ $a->nome }}</td>
                  <td>{{ $a->categoria?->nome }}</td>
                  <td>{{ $a->ente_appartenenza }}</td>
                  <td><span class="stato-chip {{ $a->uscita_at ? '' : 'dentro' }}">{{ $a->uscita_at ? 'uscito' : 'dentro' }}</span></td>
                  <td>{{ $a->entrata_at?->format('d/m H:i') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </section>
    </div>
  @endif
</main>
@endsection
