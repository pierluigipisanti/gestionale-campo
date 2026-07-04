@extends('layouts.app')

@section('title', 'Check-in nucleo')
@section('sezione', 'Check-in nucleo familiare')

@push('styles')
<style>
  .membro{align-items:end}
  .membro .field{margin-bottom:8px}
  #add-membro{margin:2px 0 16px}
  .hint{color:var(--soft); font-size:13px; margin:0 0 14px}
</style>
@endpush

@section('content')
<main class="wrap">
  <p><a href="{{ route('posti.index') }}" class="back">← Posti</a></p>
  <section class="panel" style="max-width:620px">
    <h2 class="panel-title">Check-in nucleo familiare</h2>

    @if ($tende->isEmpty())
      <p class="hint">Nessuna tenda con posti liberi. Aggiungine dalla
        <a href="{{ route('struttura.index') }}">Struttura</a> o libera dei posti.</p>
    @else
      <p class="hint">La famiglia viene assegnata a posti liberi della stessa tenda, così resta insieme.</p>
      <form method="post" action="{{ route('nucleo.checkin.store') }}">
        @csrf
        <div class="two">
          <label class="field"><span>Nucleo (cognome capofamiglia)</span>
            <input name="etichetta" value="{{ old('etichetta') }}" required autofocus autocomplete="off"></label>
          <label class="field"><span>Categoria</span>
            <select name="categoria_id">
              @php $ospiteDefault = optional($categorie->firstWhere('nome', 'Ospite'))->id; @endphp
              @foreach ($categorie as $c)
                <option value="{{ $c->id }}" @selected(old('categoria_id', $ospiteDefault) == $c->id)>{{ $c->nome }}</option>
              @endforeach
            </select></label>
        </div>
        @error('etichetta')<p class="err">{{ $message }}</p>@enderror

        <label class="field"><span>Tenda</span>
          <select name="tenda_id" required>
            @foreach ($tende as $t)
              <option value="{{ $t->id }}" @selected(old('tenda_id') == $t->id)>{{ $t->codice }} — {{ $t->liberi_count }} liberi</option>
            @endforeach
          </select></label>
        @error('tenda_id')<p class="err">{{ $message }}</p>@enderror

        <p class="panel-title" style="margin:18px 0 8px">Membri del nucleo</p>
        <div id="membri">
          @for ($i = 0; $i < 4; $i++)
            <div class="membro two">
              <label class="field"><span>Cognome</span><input name="membri[{{ $i }}][cognome]" autocomplete="off"></label>
              <label class="field"><span>Nome</span><input name="membri[{{ $i }}][nome]" autocomplete="off"></label>
            </div>
          @endfor
        </div>
        <button type="button" class="btn btn-ghost btn-sm" id="add-membro">+ Aggiungi persona</button>

        <button class="btn btn-block" type="submit" style="margin-top:8px">Conferma check-in nucleo</button>
      </form>

      <script>
        (function () {
          var box = document.getElementById('membri');
          var i = box.children.length;
          document.getElementById('add-membro').addEventListener('click', function () {
            var row = box.children[0].cloneNode(true);
            row.querySelectorAll('input').forEach(function (inp) {
              inp.value = '';
              inp.name = inp.name.replace(/\[\d+\]/, '[' + i + ']');
            });
            box.appendChild(row);
            i++;
          });
        })();
      </script>
    @endif
  </section>
</main>
@endsection
