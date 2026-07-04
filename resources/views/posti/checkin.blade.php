@extends('layouts.app')

@section('title', 'Check-in')
@section('sezione', 'Check-in')

@push('styles')
<style>
  .cerca-box{position:relative; margin-bottom:16px}
  .cerca-box input{width:100%; padding:12px 13px; font-size:16px; border:1.5px solid var(--line); border-radius:10px; font-family:inherit}
  .cerca-box input:focus{outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.14)}
  .cerca-box .hint{margin:6px 0 0; font-size:13px; color:var(--soft)}
  .risultati{position:absolute; left:0; right:0; top:100%; z-index:10; background:#fff; border:1px solid var(--line); border-radius:10px; box-shadow:var(--shadow); margin-top:4px; max-height:280px; overflow:auto}
  .risultati:empty{display:none}
  .ris{padding:10px 13px; cursor:pointer; font-size:15px; border-bottom:1px solid var(--line)}
  .ris:last-child{border-bottom:none}
  .ris:hover{background:#F1F4F8}
  .ris small{color:var(--soft)}
  .ris-vuoto{padding:10px 13px; color:var(--soft); font-size:14px}
</style>
@endpush

@section('content')
@php $ospiteDefault = optional($categorie->firstWhere('nome', 'Ospite'))->id; @endphp
<main class="wrap">
  <p><a href="{{ route('posti.index') }}" class="back">← Posti</a></p>
  <section class="panel" style="max-width:560px">
    <h2 class="panel-title">Check-in — {{ $posto->tenda->codice }} · posto {{ $posto->numero }}</h2>

    <div class="cerca-box">
      <input id="cerca" autocomplete="off" placeholder="Cerca la persona per cognome o codice fiscale…" autofocus>
      <p class="hint">La persona è già nel campo: cercala e assegnala. Se non c'è, compila i campi sotto.</p>
      <div class="risultati" id="risultati"></div>
    </div>

    <form method="post" action="{{ route('posti.checkin.store', $posto) }}">
      @csrf
      <input type="hidden" name="persona_id" value="{{ old('persona_id') }}">
      <div class="two">
        <label class="field"><span>Cognome</span>
          <input name="cognome" value="{{ old('cognome') }}" required autocomplete="off"></label>
        <label class="field"><span>Nome</span>
          <input name="nome" value="{{ old('nome') }}" required autocomplete="off"></label>
      </div>
      @error('cognome')<p class="err">{{ $message }}</p>@enderror
      @error('nome')<p class="err">{{ $message }}</p>@enderror

      <div class="two">
        <label class="field"><span>Codice fiscale</span>
          <input name="codice_fiscale" value="{{ old('codice_fiscale') }}" autocomplete="off" style="text-transform:uppercase"></label>
        <label class="field"><span>Cellulare</span>
          <input name="telefono" value="{{ old('telefono') }}" autocomplete="off" inputmode="tel"></label>
      </div>

      <label class="field">
        <span>Categoria</span>
        <select name="categoria_id">
          @foreach ($categorie as $c)
            <option value="{{ $c->id }}" @selected(old('categoria_id', $ospiteDefault) == $c->id)>{{ $c->nome }}</option>
          @endforeach
        </select>
      </label>

      <details class="more" @if($errors->hasAny(['data_nascita','codice_fiscale'])) open @endif>
        <summary>Anagrafica e documento</summary>
        <div class="two">
          <label class="field"><span>Sesso</span>
            <select name="sesso">
              <option value="">—</option>
              <option value="M" @selected(old('sesso')=='M')>M</option>
              <option value="F" @selected(old('sesso')=='F')>F</option>
            </select></label>
          <label class="field"><span>Data di nascita</span>
            <input type="date" name="data_nascita" value="{{ old('data_nascita') }}"></label>
        </div>
        <label class="field"><span>Comune di provenienza</span>
          <input name="comune_provenienza" value="{{ old('comune_provenienza') }}" autocomplete="off"></label>
        <label class="field"><span>Allergie / dieta</span>
          <input name="allergie_dieta" value="{{ old('allergie_dieta') }}" autocomplete="off"></label>
        <label class="field"><span>Note sanitarie / fragilità</span>
          <input name="note_sanitarie" value="{{ old('note_sanitarie') }}" autocomplete="off"></label>
        <div class="two">
          <label class="field"><span>Tipo documento</span>
            <input name="documento_tipo" value="{{ old('documento_tipo') }}" autocomplete="off"></label>
          <label class="field"><span>Numero documento</span>
            <input name="documento_numero" value="{{ old('documento_numero') }}" autocomplete="off"></label>
        </div>
      </details>

      <button class="btn btn-block" type="submit">Conferma check-in</button>
    </form>
  </section>
</main>

<script>
  (function () {
    const cerca = document.getElementById('cerca');
    const box = document.getElementById('risultati');
    const pid = document.getElementsByName('persona_id')[0];
    const set = (n, v) => { const el = document.getElementsByName(n)[0]; if (el) el.value = v || ''; };
    let timer;

    cerca.addEventListener('input', function () {
      clearTimeout(timer);
      pid.value = '';
      const q = cerca.value.trim();
      if (q.length < 2) { box.innerHTML = ''; return; }
      timer = setTimeout(() => {
        fetch('{{ route('anagrafica.cerca') }}?q=' + encodeURIComponent(q))
          .then(r => r.json()).then(list => {
            box.innerHTML = '';
            if (!list.length) { box.innerHTML = '<div class="ris-vuoto">Nessuno trovato — compila i campi per una nuova persona.</div>'; return; }
            list.forEach(p => {
              const d = document.createElement('div');
              d.className = 'ris';
              // textContent (non innerHTML) per evitare XSS da nomi/CF
              d.appendChild(Object.assign(document.createElement('strong'), { textContent: p.cognome }));
              d.appendChild(document.createTextNode(' ' + (p.nome || '')));
              [p.cf, p.categoria].filter(Boolean).forEach(x =>
                d.appendChild(Object.assign(document.createElement('small'), { textContent: ' · ' + x })));
              d.onclick = () => {
                pid.value = p.id;
                set('cognome', p.cognome); set('nome', p.nome); set('codice_fiscale', p.cf); set('telefono', p.telefono);
                if (p.categoria_id) { const s = document.getElementsByName('categoria_id')[0]; if (s) s.value = p.categoria_id; }
                box.innerHTML = ''; cerca.value = p.cognome + ' ' + (p.nome || '');
              };
              box.appendChild(d);
            });
          }).catch(() => {});
      }, 200);
    });
  })();
</script>
@endsection
