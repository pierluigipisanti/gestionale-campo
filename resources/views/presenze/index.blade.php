@extends('layouts.app')

@section('title', 'Presenze')
@section('sezione', 'Chiusura presenze giornaliera')

@push('styles')
<style>
  .barra{display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; max-width:1120px; margin:20px auto 0; padding:0 24px}
  .barra .data-form{display:flex; align-items:flex-end; gap:8px}
  .barra .data-form .field{margin:0}
  .barra .data-form input{padding:9px 11px}
  .barra .conteggio{margin-left:auto; font-size:14px; color:var(--soft); font-weight:600}
  .barra .conteggio strong{color:var(--ink); font-size:16px}
  .wrapp{max-width:1120px; margin:16px auto; padding:0 24px}
  table.p{width:100%; border-collapse:collapse; font-size:15px}
  table.p th{text-align:left; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; padding:8px 10px; border-bottom:1px solid var(--line)}
  table.p td{padding:8px 10px; border-bottom:1px solid var(--line); vertical-align:middle}
  table.p select{padding:8px 10px; font-size:15px; border:1.5px solid var(--line); border-radius:8px; font-family:inherit; background:#fff}
  .azioni{display:flex; justify-content:flex-end; margin-top:16px}
  .già{color:#146c3a; font-weight:600; font-size:13px}
  .stato-chip{font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:2px 9px; border-radius:20px; background:#EEF1F5; color:var(--soft)}
  .stato-chip.presente{background:#E6F3EC; color:#146c3a}
  .stato-chip.assente{background:#FFF3DB; color:#8A5A00}
</style>
@endpush

@section('content')
<div class="barra">
  <form class="data-form" method="get" action="{{ route('presenze.index') }}">
    <label class="field"><span style="display:block;font-size:13px;font-weight:600;color:var(--soft);margin-bottom:5px">Giorno</span>
      <input type="date" name="data" value="{{ $data->toDateString() }}"></label>
    <button class="btn btn-ghost btn-sm" type="submit">Vai</button>
  </form>
  <div class="conteggio"><strong>{{ $persone->count() }}</strong> persone in forza
    @if ($esistenti->isNotEmpty())<br><span class="già">giornata già consolidata — aggiorna e riconferma</span>@endif
  </div>
</div>

<main class="wrapp">
  <div class="panel">
    <form method="post" action="{{ route('presenze.store') }}">
      @csrf
      <input type="hidden" name="data" value="{{ $data->toDateString() }}">

      @if ($persone->isEmpty())
        <p style="margin:0; color:var(--soft)">Nessuna persona in forza nel campo.</p>
      @else
        <table class="p">
          <thead>
            <tr><th>Cognome</th><th>Nome</th><th>Categoria</th><th>Posto</th><th style="width:120px">Stato</th></tr>
          </thead>
          <tbody>
            @foreach ($persone as $p)
              @php
                $lbl = $p->stato === 'assente_temporaneo' ? 'Assente' : 'Presente';
                $cls = $p->stato === 'assente_temporaneo' ? 'assente' : 'presente';
              @endphp
              <tr>
                <td><strong>{{ $p->cognome }}</strong></td>
                <td>{{ $p->nome }}</td>
                <td>{{ $p->categoria?->nome }}</td>
                <td>{{ $p->posto?->tenda?->codice }}{{ $p->posto ? '/'.$p->posto->numero : '' }}</td>
                <td><span class="stato-chip {{ $cls }}">{{ $lbl }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <p style="margin:14px 0 0; color:var(--soft); font-size:13px">Lo stato è preso automaticamente dalle
          entrate/uscite. Consolida per salvare la fotografia del giorno (per report e storico).</p>
        <div class="azioni">
          <button class="btn" type="submit">Consolida presenze del {{ $data->format('d/m/Y') }}</button>
        </div>
      @endif
    </form>
  </div>
</main>
@endsection
