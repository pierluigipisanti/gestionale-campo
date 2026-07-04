@extends('layouts.app')

@section('title', 'Posti')
@section('sezione', 'Occupazione posti')

@push('styles')
<style>
  .summary{display:flex; align-items:center; gap:28px; flex-wrap:wrap; margin-bottom:6px}
  .summary .big{font-size:34px; font-weight:800; letter-spacing:-.02em; font-variant-numeric:tabular-nums}
  .summary .big small{font-size:15px; color:var(--soft); font-weight:600}
  .legend{display:flex; gap:16px; flex-wrap:wrap; font-size:13px; color:var(--soft); font-weight:600}
  .legend i{display:inline-block; width:12px; height:12px; border-radius:3px; margin-right:6px; vertical-align:-1px}

  .set-h{font-size:13px; letter-spacing:.1em; text-transform:uppercase; color:var(--soft); font-weight:700; margin:24px 0 12px}
  /* griglia a 6 colonne: tenda da 6 posti = span 2 (3/riga), da 8 = span 3 (2/riga) */
  .tende{display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; align-items:start}
  @media (max-width:1100px){ .tende{grid-template-columns:repeat(4,minmax(0,1fr))} }
  @media (max-width:680px){ .tende{grid-template-columns:repeat(2,minmax(0,1fr))} }
  .tenda{background:var(--surface); border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow); padding:12px}
  .tenda-h{display:flex; justify-content:space-between; align-items:baseline; margin-bottom:10px}
  .tenda-h .cod{font-weight:800; letter-spacing:-.01em}
  .tenda-h .cap{font-size:12px; color:var(--soft); font-variant-numeric:tabular-nums}
  .posti{display:grid; gap:6px}
  .posto{
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    height:52px; border-radius:8px; text-decoration:none; border:1.5px solid transparent;
    padding:2px; overflow:hidden; min-width:0;
  }
  .posto .n{font-size:15px; font-weight:800; line-height:1.1}
  .posto .nm{
    width:100%; box-sizing:border-box; padding:0 3px; text-align:center;
    font-size:9.5px; font-weight:600; line-height:1.1; margin-top:2px;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
  }
  .p-libero{background:var(--inside-bg); color:#146c3a; border-color:#CDE9D8}
  .p-libero:hover{border-color:var(--inside)}
  .p-occupato{background:#FDECEA; color:#9a241a; border-color:#F3C7C1}
  .p-occupato:hover{border-color:#D6493B}
  .p-inagibile{background:#EEF1F5; color:#9AA6B8}
</style>
@endpush

@section('content')
@php
  $occ = (int) ($conteggio['occupato'] ?? 0);
  $lib = (int) ($conteggio['libero'] ?? 0);
  $ina = (int) ($conteggio['inagibile'] ?? 0);
  $tot = $occ + $lib + $ina;
@endphp
<main class="wrap">
  <div class="summary">
    <div class="big">{{ $occ }}<small> / {{ $tot }} occupati</small></div>
    <div class="legend">
      <span><i style="background:#CDE9D8"></i>{{ $lib }} liberi</span>
      <span><i style="background:#F3C7C1"></i>{{ $occ }} occupati</span>
      @if ($ina)<span><i style="background:#DDE2EA"></i>{{ $ina }} inagibili</span>@endif
    </div>
    <a class="btn btn-sm" href="{{ route('nucleo.checkin.form') }}" style="margin-left:auto">Check-in nucleo</a>
  </div>

  @forelse ($tende->groupBy('settore') as $settore => $tendeSet)
    <h2 class="set-h">Settore {{ $settore ?: '—' }}</h2>
    <div class="tende">
      @foreach ($tendeSet as $tenda)
        @php
          $tTot = $tenda->posti->count();
          $tOcc = $tenda->posti->where('stato', 'occupato')->count();
          $span = $tTot > 6 ? 3 : 2;   // 6 posti → 3/riga, 8 posti → 2/riga
          $cols = $tTot > 6 ? 4 : 3;   // posti per riga dentro la tenda
        @endphp
        <div class="tenda" style="grid-column: span {{ $span }}">
          <div class="tenda-h">
            <span class="cod">{{ $tenda->codice }}</span>
            <span class="cap">{{ $tOcc }}/{{ $tTot }}</span>
          </div>
          <div class="posti" style="grid-template-columns:repeat({{ $cols }},1fr)">
            @foreach ($tenda->posti as $posto)
              @php $p = $occupanti[$posto->id] ?? null; @endphp
              @if ($posto->stato === 'inagibile')
                <span class="posto p-inagibile" title="Inagibile"><span class="n">{{ $posto->numero }}</span><span class="nm">n/d</span></span>
              @elseif ($p)
                <a class="posto p-occupato" href="{{ route('posti.show', $posto) }}" title="{{ $p->cognome }} {{ $p->nome }}">
                  <span class="n">{{ $posto->numero }}</span><span class="nm">{{ $p->cognome }}</span></a>
              @else
                <a class="posto p-libero" href="{{ route('posti.checkin.form', $posto) }}" title="Posto libero — check-in">
                  <span class="n">{{ $posto->numero }}</span><span class="nm">libero</span></a>
              @endif
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  @empty
    <div class="panel"><p style="margin:0; color:var(--soft)">Nessuna tenda configurata per questo campo.</p></div>
  @endforelse
</main>
@endsection
