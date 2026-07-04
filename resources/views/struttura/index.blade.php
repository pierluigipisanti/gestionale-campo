@extends('layouts.app')

@section('title', 'Struttura')
@section('sezione', 'Struttura del campo')

@push('styles')
<style>
  .struttura{display:grid; grid-template-columns:340px 1fr; gap:20px; max-width:1120px; margin:20px auto; padding:0 24px}
  @media (max-width:820px){ .struttura{grid-template-columns:1fr} }
  .entry{align-self:start; position:sticky; top:84px}
  @media (max-width:820px){ .entry{position:static} }

  .tot{display:flex; gap:24px; margin-bottom:14px}
  .tot .big{font-size:30px; font-weight:800; letter-spacing:-.02em; font-variant-numeric:tabular-nums; line-height:1}
  .tot .big small{display:block; font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:var(--soft); font-weight:700; margin-top:4px}

  table.tende-t{width:100%; border-collapse:collapse; font-size:15px}
  table.tende-t th{text-align:left; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; padding:8px 10px; border-bottom:1px solid var(--line)}
  table.tende-t td{padding:10px; border-bottom:1px solid var(--line); vertical-align:middle}
  table.tende-t td.num{font-variant-numeric:tabular-nums}
  .cod{font-weight:800}
  .set-h{font-size:13px; letter-spacing:.1em; text-transform:uppercase; color:var(--soft); font-weight:700; margin:20px 0 8px}
  .btn-del{padding:6px 11px; font-size:13px; font-weight:700; color:#8A1C11; background:#fff; border:1.5px solid #F3C7C1; border-radius:8px; cursor:pointer}
  .btn-del:hover{background:#FDECEA}
  .occ-warn{color:var(--soft); font-size:12px}
</style>
@endpush

@section('content')
<main class="struttura">
  <section class="panel entry">
    <h2 class="panel-title">Aggiungi tende</h2>
    <form method="post" action="{{ route('struttura.store') }}">
      @csrf
      <div class="two">
        <label class="field"><span>Settore</span>
          <input name="settore" value="{{ old('settore', 'A') }}" required autocomplete="off" placeholder="A"></label>
        <label class="field"><span>Fila / strada</span>
          <input name="fila" value="{{ old('fila') }}" autocomplete="off" placeholder="opzionale"></label>
      </div>
      @error('settore')<p class="err">{{ $message }}</p>@enderror

      <div class="two">
        <label class="field"><span>Numero di tende</span>
          <input type="number" name="numero_tende" value="{{ old('numero_tende', 1) }}" min="1" max="200" required></label>
        <label class="field"><span>Posti per tenda</span>
          <input type="number" name="posti_per_tenda" value="{{ old('posti_per_tenda', 6) }}" min="1" max="100" required></label>
      </div>
      @error('numero_tende')<p class="err">{{ $message }}</p>@enderror
      @error('posti_per_tenda')<p class="err">{{ $message }}</p>@enderror

      <label class="field"><span>Tipo</span>
        <select name="tipo">
          <option value="alloggio" @selected(old('tipo','alloggio')=='alloggio')>Alloggio</option>
          <option value="servizi" @selected(old('tipo')=='servizi')>Servizi</option>
        </select></label>

      <details class="more">
        <summary>Modello tenda</summary>
        <label class="field"><span>Modello</span>
          <input name="modello" value="{{ old('modello') }}" autocomplete="off" placeholder="es. PI88"></label>
      </details>

      <button class="btn btn-block" type="submit">Crea tende</button>
    </form>
  </section>

  <section class="panel">
    <div class="tot">
      <div class="big">{{ $totali['tende'] }}<small>Tende</small></div>
      <div class="big">{{ $totali['posti'] }}<small>Posti</small></div>
    </div>

    @forelse ($tende->groupBy('settore') as $settore => $tendeSet)
      <h3 class="set-h">Settore {{ $settore ?: '—' }}</h3>
      <table class="tende-t">
        <thead>
          <tr><th>Tenda</th><th>Fila</th><th>Tipo</th><th>Posti</th><th></th></tr>
        </thead>
        <tbody>
          @foreach ($tendeSet as $t)
            <tr>
              <td><span class="cod">{{ $t->codice }}</span></td>
              <td>{{ $t->fila ?: '—' }}</td>
              <td>{{ ucfirst($t->tipo) }}</td>
              <td class="num">
                {{ $t->posti_count }}
                @if ($t->posti_occupati_count)<span class="occ-warn">({{ $t->posti_occupati_count }} occ.)</span>@endif
              </td>
              <td style="text-align:right">
                <span style="display:inline-flex; gap:8px; align-items:center; justify-content:flex-end">
                  <a href="{{ route('stampe.tenda', $t) }}" style="padding:6px 11px; font-size:13px; font-weight:700; color:var(--ink); background:#fff; border:1.5px solid var(--line); border-radius:8px; text-decoration:none">Cartello</a>
                  <a href="{{ route('struttura.edit', $t) }}" style="padding:6px 11px; font-size:13px; font-weight:700; color:var(--ink); background:#fff; border:1.5px solid var(--line); border-radius:8px; text-decoration:none">Modifica</a>
                  @can('admin')
                    <form method="post" action="{{ route('struttura.destroy', $t) }}"
                          onsubmit="return confirm('Eliminare la tenda {{ $t->codice }} e i suoi posti?')">
                      @csrf @method('DELETE')
                      <button class="btn-del" type="submit">Elimina</button>
                    </form>
                  @endcan
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @empty
      <p style="margin:0; color:var(--soft)">Nessuna tenda ancora. Usa il modulo a sinistra per crearle.</p>
    @endforelse
  </section>
</main>
@endsection
