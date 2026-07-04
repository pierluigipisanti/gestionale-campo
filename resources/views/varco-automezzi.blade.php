@extends('layouts.app')

@section('title', 'Varco automezzi')
@section('sezione', 'Controllo varco')

@push('styles')
<style>
  .va-wrap{max-width:560px; margin:16px auto 22px; padding:0 28px}
  .sep{border:none; border-top:1px solid var(--line); margin:20px 0}
  .cerca-box{position:relative}
  .risultati{position:absolute; left:0; right:0; top:100%; z-index:10; background:#fff; border:1px solid var(--line); border-radius:10px; box-shadow:var(--shadow); margin-top:4px; max-height:240px; overflow:auto}
  .risultati:empty{display:none}
  .ris{padding:10px 13px; cursor:pointer; font-size:15px; border-bottom:1px solid var(--line)}
  .ris:last-child{border-bottom:none}
  .ris:hover{background:#F1F4F8}
  .ris small{color:var(--soft)}
</style>
@endpush

@section('content')
<div class="tabs-bar">
  <div class="tabs">
    <a href="{{ route('varco.index') }}">Persone</a>
    <a href="{{ route('varco.automezzi') }}" class="active">Automezzi</a>
  </div>
</div>

<main class="va-wrap">
  <section class="panel">
    <h2 class="panel-title">① Entrata</h2>
    <form method="post" action="{{ route('automezzi.entrata') }}">
      @csrf
      <div class="cerca-box">
        <label class="field" style="margin-bottom:0"><span>Targa</span>
          <input name="targa" id="am-targa" value="{{ old('targa') }}" required autofocus autocomplete="off"
                 style="text-transform:uppercase" placeholder="AB123CD — nuova o già nota"></label>
        <div class="risultati" id="am-ris"></div>
      </div>
      @error('targa')<p class="err" style="margin-top:8px">{{ $message }}</p>@enderror
      <div style="height:8px"></div>
      <div class="two">
        <label class="field"><span>Tipo</span>
          <input name="tipo" value="{{ old('tipo') }}" autocomplete="off" placeholder="furgone, ambulanza…"></label>
        <label class="field"><span>Ente</span>
          <input name="ente_appartenenza" value="{{ old('ente_appartenenza') }}" autocomplete="off"></label>
      </div>
      <div class="two">
        <label class="field"><span>Referente</span>
          <input name="referente" value="{{ old('referente') }}" autocomplete="off"></label>
        <label class="field"><span>Cellulare</span>
          <input name="telefono" value="{{ old('telefono') }}" autocomplete="off" inputmode="tel"></label>
      </div>
      <button class="btn btn-block" type="submit">Registra ENTRATA</button>
    </form>

    <hr class="sep">

    <h2 class="panel-title">② Uscita</h2>
    <form method="post" action="{{ route('automezzi.uscita') }}">
      @csrf
      <div class="cerca-box">
        <label class="field" style="margin-bottom:0"><span>Scansiona il QR, o scrivi la targa</span>
          <input name="targa" id="us-targa" autocomplete="off" style="text-transform:uppercase" placeholder="AB123CD"></label>
        <div class="risultati" id="us-ris"></div>
      </div>
      <div style="height:8px"></div>
      <button class="btn btn-ghost btn-block" type="submit">Registra USCITA</button>
    </form>

    <p style="margin:18px 0 0"><a href="{{ route('automezzi.index') }}" class="back">Gestisci automezzi →</a></p>
  </section>
</main>

@php
  $fuoriJson = $fuori->map(fn ($a) => ['targa' => $a->targa, 'tipo' => $a->tipo, 'referente' => $a->referente, 'ente' => $a->ente_appartenenza, 'telefono' => $a->telefono])->values();
  $dentroJson = $dentro->map(fn ($a) => ['targa' => $a->targa, 'tipo' => $a->tipo, 'referente' => $a->referente])->values();
@endphp
<script>
  (function () {
    function attach(inputId, boxId, data, onPick) {
      const inp = document.getElementById(inputId), box = document.getElementById(boxId);
      if (!inp) return;
      inp.addEventListener('input', function () {
        const q = inp.value.trim().toUpperCase();
        box.innerHTML = '';
        if (!q) return;
        data.filter(a => a.targa.toUpperCase().indexOf(q) >= 0).slice(0, 12).forEach(a => {
          const d = document.createElement('div');
          d.className = 'ris';
          // textContent (non innerHTML) per evitare XSS da targa/tipo/referente
          d.appendChild(Object.assign(document.createElement('strong'), { textContent: a.targa }));
          [a.tipo, a.referente].filter(Boolean).forEach(x =>
            d.appendChild(Object.assign(document.createElement('small'), { textContent: ' · ' + x })));
          d.onclick = () => { onPick(a); box.innerHTML = ''; };
          box.appendChild(d);
        });
      });
      document.addEventListener('click', function (e) { if (!inp.closest('.cerca-box').contains(e.target)) box.innerHTML = ''; });
    }

    const setName = (n, v) => { const el = document.getElementsByName(n)[0]; if (el) el.value = v || ''; };

    // Entrata: riempie tutti i dati del mezzo
    attach('am-targa', 'am-ris', @json($fuoriJson), a => {
      setName('targa', a.targa); setName('tipo', a.tipo); setName('referente', a.referente);
      setName('ente_appartenenza', a.ente); setName('telefono', a.telefono);
    });

    // Uscita: solo la targa (il campo è l'input dell'uscita, per id)
    attach('us-targa', 'us-ris', @json($dentroJson), a => {
      document.getElementById('us-targa').value = a.targa;
    });
  })();
</script>
@endsection
