@extends('layouts.app')

@section('title', 'Importa')
@section('sezione', 'Importa da Excel')

@push('styles')
<style>
  .imp{display:grid; grid-template-columns:1fr 1fr; gap:20px; max-width:1080px; margin:22px auto; padding:0 28px}
  @media (max-width:820px){ .imp{grid-template-columns:1fr} }
  .imp h3{margin:0 0 4px; font-size:17px}
  .imp .cols{margin:0 0 14px; color:var(--soft); font-size:13px}
  .imp .step{display:flex; gap:8px; align-items:center; margin-bottom:12px}
  .imp .n{width:22px; height:22px; flex:0 0 22px; border-radius:50%; background:var(--ink); color:#fff; font-size:12px; font-weight:800; display:flex; align-items:center; justify-content:center}
</style>
@endpush

@section('content')
<main class="imp">
  <section class="panel">
    <h3>Volontari</h3>
    <p class="cols">Colonne: Cognome, Nome, Codice fiscale, Cellulare, Categoria, Ente appartenenza.
      Le persone importate restano <em>pre-registrate</em>: al check-in, dal CF, il sistema le riconosce.</p>
    <div class="step"><span class="n">1</span><a class="btn btn-ghost btn-sm" href="{{ route('import.template.volontari') }}">Scarica template Excel</a></div>
    <form method="post" action="{{ route('import.volontari') }}" enctype="multipart/form-data">
      @csrf
      <div class="step"><span class="n">2</span>
        <input type="file" name="file" accept=".xlsx,.csv" required></div>
      @error('file')<p class="err">{{ $message }}</p>@enderror
      <button class="btn btn-block" type="submit">Importa volontari</button>
    </form>
  </section>

  <section class="panel">
    <h3>Automezzi</h3>
    <p class="cols">Colonne: Tipologia, Ente, Targa, Descrizione.
      I mezzi importati entrano nel registro (stato «fuori»); l'entrata la registri poi dal varco.</p>
    <div class="step"><span class="n">1</span><a class="btn btn-ghost btn-sm" href="{{ route('import.template.automezzi') }}">Scarica template Excel</a></div>
    <form method="post" action="{{ route('import.automezzi') }}" enctype="multipart/form-data">
      @csrf
      <div class="step"><span class="n">2</span>
        <input type="file" name="file" accept=".xlsx,.csv" required></div>
      @error('file')<p class="err">{{ $message }}</p>@enderror
      <button class="btn btn-block" type="submit">Importa automezzi</button>
    </form>
  </section>
</main>
@endsection
