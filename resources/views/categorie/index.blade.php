@extends('layouts.app')

@section('title', 'Categorie')
@section('sezione', 'Categorie di persona')

@push('styles')
<style>
  .cat{display:grid; grid-template-columns:320px 1fr; gap:20px; max-width:1120px; margin:20px auto; padding:0 24px}
  @media (max-width:820px){ .cat{grid-template-columns:1fr} }
  .entry{align-self:start; position:sticky; top:84px}
  @media (max-width:820px){ .entry{position:static} }
  table.c{width:100%; border-collapse:collapse; font-size:15px}
  table.c th{text-align:left; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; padding:8px 10px; border-bottom:1px solid var(--line)}
  table.c td{padding:8px 10px; border-bottom:1px solid var(--line); vertical-align:middle}
  table.c td.num{font-variant-numeric:tabular-nums; color:var(--soft)}
  table.c input{width:100%; padding:8px 10px; font-size:15px; border:1.5px solid var(--line); border-radius:8px; font-family:inherit; background:#fff}
  table.c input:focus{outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.15)}
  .stato{font-size:11px; font-weight:700; letter-spacing:.03em; padding:2px 9px; border-radius:20px; text-transform:uppercase}
  .stato.on{background:#E6F3EC; color:#146c3a; border:1px solid #CDE9D8}
  .stato.off{background:#EEF1F5; color:#9AA6B8; border:1px solid var(--line)}
  .acts{display:flex; gap:6px; justify-content:flex-end}
  .acts form{margin:0}
  .mini{padding:6px 10px; font:inherit; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer; border:1.5px solid var(--line); background:#fff; color:var(--ink); text-decoration:none; display:inline-block}
  .mini:hover{background:#F7F9FB; border-color:var(--soft)}
  .mini.save{color:#fff; background:var(--accent); border-color:var(--accent)}
  .mini.save:hover{background:var(--accent-d)}
  .mini.del{color:#8A1C11; border-color:#F3C7C1}
  .mini.del:hover{background:#FDECEA}
  .muted td{opacity:.7}
</style>
@endpush

@section('content')
<main class="cat">
  <section class="panel entry">
    <h2 class="panel-title">Nuova categoria</h2>
    <form method="post" action="{{ route('categorie.store') }}">
      @csrf
      <label class="field"><span>Nome</span>
        <input name="nome" value="{{ old('nome') }}" required autofocus autocomplete="off" placeholder="es. Guardia di Finanza"></label>
      @error('nome')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Sigla (opzionale)</span>
        <input name="sigla" value="{{ old('sigla') }}" autocomplete="off" placeholder="es. GdF"></label>
      <button class="btn btn-block" type="submit">Aggiungi categoria</button>
    </form>
    <p style="margin:14px 0 0; color:var(--soft); font-size:13px">Modifica nome e sigla direttamente in tabella.
      Le categorie in uso non si eliminano: si disattivano (spariscono dalle scelte future, lo storico resta).</p>
  </section>

  <section class="panel">
    <h2 class="panel-title">Categorie ({{ $categorie->count() }})</h2>
    <table class="c">
      <thead><tr><th style="width:38%">Nome</th><th style="width:18%">Sigla</th><th>Usata da</th><th>Stato</th><th></th></tr></thead>
      <tbody>
        @foreach ($categorie as $c)
          @php $usata = $c->persone_count + $c->accessi_count; @endphp
          <tr @class(['muted' => ! $c->attiva])>
            <td><input name="nome" form="cu{{ $c->id }}" value="{{ $c->nome }}"></td>
            <td><input name="sigla" form="cu{{ $c->id }}" value="{{ $c->sigla }}" placeholder="—"></td>
            <td class="num">{{ $usata }}</td>
            <td><span class="stato {{ $c->attiva ? 'on' : 'off' }}">{{ $c->attiva ? 'attiva' : 'disattivata' }}</span></td>
            <td>
              <div class="acts">
                <form id="cu{{ $c->id }}" method="post" action="{{ route('categorie.update', $c) }}">
                  @csrf @method('PATCH')
                  <button class="mini save" type="submit">Salva</button>
                </form>
                <form method="post" action="{{ route('categorie.toggle', $c) }}">
                  @csrf @method('PATCH')
                  <button class="mini" type="submit">{{ $c->attiva ? 'Disattiva' : 'Riattiva' }}</button>
                </form>
                @if ($usata === 0)
                  <form method="post" action="{{ route('categorie.destroy', $c) }}"
                        onsubmit="return confirm('Eliminare la categoria {{ $c->nome }}?')">
                    @csrf @method('DELETE')
                    <button class="mini del" type="submit">Elimina</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </section>
</main>
@endsection
