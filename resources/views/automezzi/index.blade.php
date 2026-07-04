@extends('layouts.app')

@section('title', 'Automezzi')
@section('sezione', 'Registro automezzi')

@push('styles')
<style>
  .am{display:grid; grid-template-columns:340px 1fr; gap:20px; max-width:1120px; margin:20px auto; padding:0 28px}
  @media (max-width:820px){ .am{grid-template-columns:1fr} }
  .entry{align-self:start; position:sticky; top:84px}
  @media (max-width:820px){ .entry{position:static} }
  table.am-t{width:100%; border-collapse:collapse; font-size:15px}
  table.am-t th{text-align:left; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; padding:8px 10px; border-bottom:1px solid var(--line)}
  table.am-t td{padding:10px; border-bottom:1px solid var(--line); vertical-align:middle}
  .targa{display:inline-block; font-weight:800; border:1.5px solid var(--ink); border-radius:6px; padding:1px 7px; font-size:14px}
  .st{font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:2px 8px; border-radius:20px}
  .st.dentro{background:#E6F3EC; color:#146c3a}
  .st.fuori{background:#EEF1F5; color:var(--soft)}
  .row-act{display:flex; gap:6px; justify-content:flex-end}
  .mini{padding:6px 10px; font:inherit; font-size:13px; font-weight:700; border-radius:8px; cursor:pointer; border:1.5px solid var(--line); background:#fff; color:var(--ink); text-decoration:none}
  .mini:hover{background:#F7F9FC}
  .mini.del{color:#8A1C11; border-color:#F3C7C1}
</style>
@endpush

@section('content')
<main class="am">
  <section class="panel entry">
    <p style="margin:0 0 12px"><a href="{{ route('varco.automezzi') }}" class="back">← Varco automezzi</a></p>
    <h2 class="panel-title">Nuovo automezzo</h2>
    <form method="post" action="{{ route('automezzi.store') }}">
      @csrf
      <label class="field"><span>Targa</span>
        <input name="targa" value="{{ old('targa') }}" required autofocus autocomplete="off" style="text-transform:uppercase" placeholder="AB123CD"></label>
      @error('targa')<p class="err">{{ $message }}</p>@enderror
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
      <label class="field"><span>Descrizione</span>
        <input name="descrizione" value="{{ old('descrizione') }}" autocomplete="off"></label>
      <button class="btn btn-block" type="submit">Registra automezzo</button>
    </form>
  </section>

  @if ($automezzi->isNotEmpty())
    <section class="panel">
      <h2 class="panel-title">Automezzi ({{ $automezzi->count() }})</h2>
      <table class="am-t">
        <thead><tr><th>Targa</th><th>Tipo</th><th>Referente</th><th>Cellulare</th><th>Stato</th><th></th></tr></thead>
        <tbody>
          @foreach ($automezzi as $a)
            <tr>
              <td><span class="targa">{{ $a->targa }}</span></td>
              <td>{{ $a->tipo }}</td>
              <td>{{ $a->referente }}</td>
              <td>{{ $a->telefono }}</td>
              <td><span class="st {{ $a->stato }}">{{ $a->stato }}</span></td>
              <td>
                <div class="row-act">
                  <a class="mini" href="{{ route('automezzi.edit', $a) }}">Modifica</a>
                  <form method="post" action="{{ route('automezzi.destroy', $a) }}"
                        onsubmit="return confirm('Eliminare l\'automezzo {{ $a->targa }}?')">
                    @csrf @method('DELETE')
                    <button class="mini del" type="submit">Elimina</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </section>
  @endif
</main>
@endsection
