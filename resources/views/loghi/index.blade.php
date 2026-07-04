@extends('layouts.app')

@section('title', 'Loghi')
@section('sezione', 'Loghi per report e tesserini')

@push('styles')
<style>
  .loghi{display:grid; grid-template-columns:320px 1fr; gap:20px; max-width:1120px; margin:20px auto; padding:0 24px}
  @media (max-width:820px){ .loghi{grid-template-columns:1fr} }
  .entry{align-self:start; position:sticky; top:84px}
  @media (max-width:820px){ .entry{position:static} }
  .griglia{display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:14px}
  .logo-card{border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow); padding:12px; text-align:center; background:#fff}
  .logo-thumb{height:96px; display:flex; align-items:center; justify-content:center; background:#F5F7FA; border-radius:8px; margin-bottom:10px}
  .logo-thumb img{max-height:84px; max-width:100%}
  .logo-et{font-weight:700; font-size:14px; margin-bottom:8px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap}
  .btn-del{padding:6px 11px; font:inherit; font-size:13px; font-weight:700; color:#8A1C11; background:#fff; border:1.5px solid #F3C7C1; border-radius:8px; cursor:pointer}
  .btn-del:hover{background:#FDECEA}
  .vuoto{color:var(--soft); margin:0}
</style>
@endpush

@section('content')
<main class="loghi">
  <section class="panel entry">
    <h2 class="panel-title">Carica logo</h2>
    <form method="post" action="{{ route('loghi.store') }}" enctype="multipart/form-data">
      @csrf
      <label class="field"><span>Etichetta</span>
        <input name="etichetta" value="{{ old('etichetta') }}" required autocomplete="off" placeholder="es. Comune di Esempio"></label>
      @error('etichetta')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>File (PNG, JPG, WEBP, SVG — max 2 MB)</span>
        <input type="file" name="file" accept=".png,.jpg,.jpeg,.webp,.svg" required></label>
      @error('file')<p class="err">{{ $message }}</p>@enderror
      <button class="btn btn-block" type="submit">Carica</button>
    </form>
    <p style="margin:14px 0 0; color:var(--soft); font-size:13px">Questi loghi saranno
      disponibili nella generazione di report e tesserini.</p>
  </section>

  <section class="panel">
    <h2 class="panel-title">Loghi ({{ $loghi->count() }})</h2>
    @if ($loghi->isEmpty())
      <p class="vuoto">Nessun logo caricato.</p>
    @else
      <div class="griglia">
        @foreach ($loghi as $logo)
          <div class="logo-card">
            <div class="logo-thumb"><img src="{{ route('loghi.file', $logo) }}" alt="{{ $logo->etichetta }}"></div>
            <form method="post" action="{{ route('loghi.update', $logo) }}" style="margin-bottom:8px">
              @csrf @method('PATCH')
              <input name="etichetta" value="{{ $logo->etichetta }}"
                     style="width:100%; padding:7px 9px; font-size:13px; border:1.5px solid var(--line); border-radius:7px; font-family:inherit; text-align:center">
              <div style="display:flex; align-items:center; justify-content:center; gap:12px; margin-top:8px; font-size:13px; color:var(--soft); flex-wrap:wrap">
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer">
                  <input type="checkbox" name="stampe" value="1" @checked($logo->stampe)> sulle stampe</label>
                <label style="display:flex; align-items:center; gap:5px">grandezza
                  <select name="dimensione" style="padding:4px 6px; border:1.5px solid var(--line); border-radius:6px; font-family:inherit">
                    <option value="S" @selected($logo->dimensione=='S')>Piccolo</option>
                    <option value="M" @selected($logo->dimensione=='M')>Medio</option>
                    <option value="L" @selected($logo->dimensione=='L')>Grande</option>
                  </select></label>
                <label style="display:flex; align-items:center; gap:5px">ordine
                  <input type="number" name="ordine" value="{{ $logo->ordine }}" min="0" max="99" style="width:48px; padding:4px 6px; border:1.5px solid var(--line); border-radius:6px; font-family:inherit"></label>
              </div>
              <button type="submit" style="margin-top:8px; padding:5px 11px; font:inherit; font-size:12px; font-weight:700; border:1.5px solid var(--line); background:#fff; border-radius:7px; cursor:pointer">Salva</button>
            </form>
            <form method="post" action="{{ route('loghi.destroy', $logo) }}"
                  onsubmit="return confirm('Eliminare il logo {{ $logo->etichetta }}?')">
              @csrf @method('DELETE')
              <button class="btn-del" type="submit">Elimina</button>
            </form>
          </div>
        @endforeach
      </div>
    @endif
  </section>
</main>
@endsection
