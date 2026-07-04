@extends('layouts.app')

@section('title', 'Report')
@section('sezione', 'Report e stampe')

@push('styles')
<style>
  .reports{display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; max-width:1120px; margin:20px auto; padding:0 24px}
  .rep{background:var(--surface); border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow); padding:18px; display:flex; flex-direction:column}
  .rep h3{margin:0 0 4px; font-size:16px; letter-spacing:-.01em}
  .rep p{margin:0 0 14px; color:var(--soft); font-size:13px; flex:1}
  .rep .actions{display:flex; gap:8px}
</style>
@endpush

@section('content')
<main class="reports">
  <div class="rep">
    <h3>Presenze</h3>
    <p>Elenco delle persone alloggiate nel campo, con categoria, nucleo e posto assegnato.</p>
    <div class="actions">
      <a class="btn btn-sm" href="{{ route('report.presenze.pdf') }}">PDF</a>
      <a class="btn btn-ghost btn-sm" href="{{ route('report.presenze.csv') }}">CSV</a>
    </div>
  </div>

  <div class="rep">
    <h3>Occupazione posti</h3>
    <p>Stato di ogni posto per settore e tenda, con l'occupante corrente.</p>
    <div class="actions">
      <a class="btn btn-sm" href="{{ route('report.posti.pdf') }}">PDF</a>
    </div>
  </div>

  <div class="rep">
    <h3>Accessi al varco</h3>
    <p>Chi è nel campo in questo momento (registro varco aperto), con ente e veicolo.</p>
    <div class="actions">
      <a class="btn btn-sm" href="{{ route('report.accessi.pdf') }}">PDF</a>
      <a class="btn btn-ghost btn-sm" href="{{ route('report.accessi.csv') }}">CSV</a>
    </div>
  </div>
</main>
@endsection
