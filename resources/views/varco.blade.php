@extends('layouts.app')

@section('title', 'Varco')
@section('sezione', 'Controllo varco')

@push('styles')
<style>
  .varco-wrap{max-width:560px; margin:16px auto 22px; padding:0 28px}
  .scan-box{background:#141B2D; border-radius:10px; padding:12px 14px; margin-bottom:14px}
  .scan-box label span{display:block; font-size:12px; font-weight:700; color:#9FB0C8; letter-spacing:.04em; margin-bottom:6px}
  .scan-box textarea{width:100%; padding:11px 12px; font-size:15px; border:none; border-radius:8px; font-family:inherit; resize:vertical}
  .scan-box textarea:focus{outline:3px solid rgba(228,86,14,.5)}
  .sep{border:none; border-top:1px solid var(--line); margin:20px 0}
</style>
@endpush

@section('content')
<div class="tabs-bar">
  <div class="tabs">
    <a href="{{ route('varco.index') }}" class="active">Persone</a>
    <a href="{{ route('varco.automezzi') }}">Automezzi</a>
  </div>
</div>

<main class="varco-wrap">
  <section class="panel">
    <h2 class="panel-title">① Entrata</h2>

    <div class="scan-box">
      <label><span>Leggi il documento (CIE / tessera sanitaria)</span>
        <textarea id="scan" rows="2" autocomplete="off" placeholder="Lettore MRZ / barcode…"></textarea></label>
    </div>

    <form method="post" action="{{ route('varco.store') }}">
      @csrf
      <div class="two">
        <label class="field"><span>Cognome</span>
          <input name="cognome" value="{{ old('cognome') }}" required autocomplete="off"></label>
        <label class="field"><span>Nome</span>
          <input name="nome" value="{{ old('nome') }}" autocomplete="off"></label>
      </div>
      @error('cognome')<p class="err">{{ $message }}</p>@enderror

      <div class="two">
        <label class="field"><span>Codice fiscale</span>
          <input name="codice_fiscale" value="{{ old('codice_fiscale') }}" autocomplete="off" style="text-transform:uppercase"></label>
        <label class="field"><span>Cellulare</span>
          <input name="telefono" value="{{ old('telefono') }}" autocomplete="off" inputmode="tel"></label>
      </div>

      <label class="field"><span>Categoria</span>
        <select name="categoria_id">
          <option value="">—</option>
          @foreach ($categorie as $c)
            <option value="{{ $c->id }}" @selected(old('categoria_id') == $c->id)>{{ $c->nome }}</option>
          @endforeach
        </select></label>

      <label class="field"><span>Ente / appartenenza</span>
        <input name="ente_appartenenza" value="{{ old('ente_appartenenza') }}" autocomplete="off" placeholder="es. Vigili del Fuoco"></label>

      <details class="more" @if(old('documento') || old('targa_veicolo') || old('motivo')) open @endif>
        <summary>Documento, veicolo, motivo</summary>
        <label class="field"><span>Documento</span>
          <input name="documento" value="{{ old('documento') }}" autocomplete="off"></label>
        <label class="field"><span>Targa veicolo</span>
          <input name="targa_veicolo" value="{{ old('targa_veicolo') }}" autocomplete="off" placeholder="AB123CD"></label>
        <label class="field"><span>Motivo</span>
          <input name="motivo" value="{{ old('motivo') }}" autocomplete="off"></label>
      </details>

      <button class="btn btn-block" type="submit">Registra ENTRATA</button>
    </form>

    <hr class="sep">

    <h2 class="panel-title">② Uscita</h2>
    <form id="form-uscita" method="post" action="{{ route('varco.uscita') }}">
      @csrf
      <label class="field"><span>Scansiona il documento, o scrivi cognome / CF</span>
        <input name="q" id="uscita-scan" autofocus autocomplete="off" placeholder="Passa il documento sul lettore, o scrivi…"></label>
      <button class="btn btn-ghost btn-block" type="submit">Registra USCITA</button>
    </form>
  </section>
</main>

<script>
  (function () {
    const cfStrict = /[A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z]/;
    const set = (n, v) => { const el = document.getElementsByName(n)[0]; if (el && v) el.value = v; };
    const cfFrom = (val) => {
      const c = val.replace(/[\r\n ]/g, '').toUpperCase();
      if (c.length === 16 && cfStrict.test(c)) return c;
      if (c.length >= 88 && c.indexOf('<') >= 0) { const m = c.match(cfStrict); return m ? m[0] : null; }
      return null;
    };

    // Entrata: scan documento → compila i campi
    const scan = document.getElementById('scan');
    if (scan) {
      scan.addEventListener('input', function () {
        const clean = scan.value.replace(/[\r\n ]/g, '').toUpperCase();
        if (clean.length === 16 && cfStrict.test(clean)) {
          set('codice_fiscale', clean);
          scan.value = ''; document.getElementsByName('cognome')[0].focus();
        } else if (clean.length >= 88 && clean.indexOf('<') >= 0) {
          const nomi = clean.substr(60, 30).split('<<');
          set('cognome', (nomi[0] || '').replace(/</g, ' ').trim());
          set('nome', (nomi[1] || '').replace(/</g, ' ').trim());
          const cf = clean.match(cfStrict); if (cf) set('codice_fiscale', cf[0]);
          scan.value = '';
        }
      });
    }

    // Uscita: scan documento → invia subito (uscita rapida, niente ricerca)
    const us = document.getElementById('uscita-scan');
    if (us) {
      us.addEventListener('input', function () {
        const cf = cfFrom(us.value);
        if (cf) { us.value = cf; document.getElementById('form-uscita').submit(); }
      });
    }
  })();
</script>
@endsection
