@extends('layouts.app')

@section('title', 'Dashboard')
@section('sezione', 'Dashboard')

@push('styles')
<style>
  .cerca-big{display:flex; gap:10px; max-width:1120px; margin:20px auto 4px; padding:0 24px}
  .cerca-big input{flex:1; padding:14px 16px; font-size:16px; border:1.5px solid var(--line); border-radius:10px; font-family:inherit}
  .cerca-big input:focus{outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.15)}
  .tiles{display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; max-width:1120px; margin:18px auto; padding:0 24px}
  .tile{background:var(--surface); border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow); padding:18px}
  .tile .num{display:block; font-size:34px; font-weight:800; letter-spacing:-.02em; line-height:1; font-variant-numeric:tabular-nums}
  .tile .num small{font-size:16px; color:var(--soft); font-weight:600}
  .tile .lbl{display:block; margin-top:8px; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700}
  .tile.accent .num{color:var(--accent)}
  .breakdown{display:flex; flex-wrap:wrap; gap:8px; align-items:center; max-width:1120px; margin:0 auto 8px; padding:0 24px}
  .bd-title{font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; margin-right:4px}
  .bd-chip{font-size:14px; color:var(--ink); background:var(--surface); border:1px solid var(--line); border-radius:20px; padding:5px 12px}
  .bd-chip strong{color:var(--accent); font-variant-numeric:tabular-nums}
</style>
@endpush

@section('content')
<main>
  <form class="cerca-big" method="get" action="{{ route('cerca') }}">
    <input name="q" placeholder="Cerca una persona per cognome, nome o codice fiscale…" autofocus aria-label="Cerca">
    <button class="btn" type="submit">Cerca</button>
  </form>

  <div class="tiles">
    <div class="tile accent"><span class="num">{{ $presenti }}</span><span class="lbl">Persone alloggiate</span></div>
    <div class="tile"><span class="num">{{ $occupati }}<small>/{{ $occupati + $liberi + $inagibili }}</small></span><span class="lbl">Posti occupati</span></div>
    <div class="tile"><span class="num">{{ $liberi }}</span><span class="lbl">Posti liberi</span></div>
    <div class="tile" title="Chi è entrato dal varco e non è ancora uscito (VVF, fornitori, visitatori… non alloggiati)"><span class="num">{{ $alVarco }}</span><span class="lbl">Transiti al varco</span></div>
    <div class="tile"><span class="num">{{ $automezzi }}</span><span class="lbl">Automezzi nel campo</span></div>
    <div class="tile"><span class="num">{{ $tende }}</span><span class="lbl">Tende</span></div>
    @if ($inagibili)
      <div class="tile"><span class="num">{{ $inagibili }}</span><span class="lbl">Posti inagibili</span></div>
    @endif
  </div>

  @if ($breakdown->isNotEmpty())
    <div class="breakdown">
      <span class="bd-title">Alloggiati per categoria</span>
      @foreach ($breakdown as $b)
        <span class="bd-chip"><strong>{{ $b->n }}</strong> {{ $b->etichetta }}</span>
      @endforeach
    </div>
  @endif
</main>
@endsection
