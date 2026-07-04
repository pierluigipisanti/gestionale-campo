@extends('layouts.app')

@section('title', 'Modifica tenda')
@section('sezione', 'Modifica tenda')

@push('styles')
<style>
  .edit{display:grid; grid-template-columns:320px 1fr; gap:20px; max-width:1120px; margin:20px auto; padding:0 24px}
  @media (max-width:820px){ .edit{grid-template-columns:1fr} }
  .entry{align-self:start; position:sticky; top:84px}
  @media (max-width:820px){ .entry{position:static} }
  .posti-list{display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:10px}
  .posto-row{border:1px solid var(--line); border-radius:10px; padding:10px}
  .posto-row .n{font-weight:800; font-size:15px}
  .posto-row .st{font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:1px 7px; border-radius:20px; margin-left:6px}
  .st.libero{background:#E6F3EC; color:#146c3a}
  .st.occupato{background:#FDECEA; color:#9a241a}
  .st.inagibile{background:#EEF1F5; color:#9AA6B8}
  .posto-row .occ{font-size:12px; color:var(--soft); margin:6px 0 8px; min-height:14px}
  .posto-row .acts{display:flex; gap:6px}
  .mini{padding:5px 9px; font:inherit; font-size:12px; font-weight:700; border-radius:7px; cursor:pointer; border:1.5px solid var(--line); background:#fff; color:var(--ink)}
  .mini:hover{background:#F7F9FB}
  .mini.del{color:#8A1C11; border-color:#F3C7C1}
  .mini.del:hover{background:#FDECEA}
  .addbar{display:flex; gap:8px; align-items:flex-end; margin-bottom:16px}
  .addbar .field{margin:0; width:120px}
</style>
@endpush

@section('content')
<main class="edit">
  <section class="panel entry">
    <p style="margin:0 0 12px"><a href="{{ route('struttura.index') }}" class="back">← Struttura</a></p>
    <h2 class="panel-title">Dati tenda {{ $tenda->codice }}</h2>
    <form method="post" action="{{ route('struttura.update', $tenda) }}">
      @csrf @method('PATCH')
      <div class="two">
        <label class="field"><span>Settore</span>
          <input name="settore" value="{{ old('settore', $tenda->settore) }}" required></label>
        <label class="field"><span>Fila</span>
          <input name="fila" value="{{ old('fila', $tenda->fila) }}"></label>
      </div>
      <label class="field"><span>Codice</span>
        <input name="codice" value="{{ old('codice', $tenda->codice) }}" required></label>
      @error('codice')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Tipo</span>
        <select name="tipo">
          <option value="alloggio" @selected(old('tipo', $tenda->tipo)=='alloggio')>Alloggio</option>
          <option value="servizi" @selected(old('tipo', $tenda->tipo)=='servizi')>Servizi</option>
        </select></label>
      <label class="field"><span>Modello</span>
        <input name="modello" value="{{ old('modello', $tenda->modello) }}"></label>
      <button class="btn btn-block" type="submit">Salva modifiche</button>
    </form>
  </section>

  <section class="panel">
    <h2 class="panel-title">Posti ({{ $tenda->posti->count() }})</h2>

    <form method="post" action="{{ route('struttura.posti.add', $tenda) }}" class="addbar">
      @csrf
      <label class="field"><span>Aggiungi posti</span>
        <input type="number" name="quanti" min="1" max="100" value="1"></label>
      <button class="btn btn-sm" type="submit">Aggiungi</button>
    </form>

    <div class="posti-list">
      @foreach ($tenda->posti as $posto)
        @php $occ = $occupanti[$posto->id] ?? null; @endphp
        <div class="posto-row">
          <span class="n">Posto {{ $posto->numero }}</span>
          <span class="st {{ $posto->stato }}">{{ $posto->stato }}</span>
          <div class="occ">{{ $occ ? $occ->cognome.' '.$occ->nome : '' }}</div>
          <div class="acts">
            @if ($posto->stato !== 'occupato')
              <form method="post" action="{{ route('struttura.posti.inagibile', $posto) }}">
                @csrf @method('PATCH')
                <button class="mini" type="submit">{{ $posto->stato === 'inagibile' ? 'Rendi agibile' : 'Inagibile' }}</button>
              </form>
              @can('admin')
                <form method="post" action="{{ route('struttura.posti.remove', $posto) }}"
                      onsubmit="return confirm('Rimuovere il posto {{ $posto->numero }}?')">
                  @csrf @method('DELETE')
                  <button class="mini del" type="submit">Rimuovi</button>
                </form>
              @endcan
            @else
              <a class="mini" href="{{ route('posti.show', $posto) }}">Gestisci occupante</a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </section>
</main>
@endsection
