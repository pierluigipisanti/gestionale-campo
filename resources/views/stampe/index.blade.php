@extends('layouts.app')

@section('title', 'Stampe')
@section('sezione', 'Stampe')

@push('styles')
<style>
  .stampe{display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; max-width:1080px; margin:22px auto; padding:0 28px}
  .st-card{background:var(--surface); border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow); padding:20px; display:flex; flex-direction:column}
  .st-card .ic{font-size:26px; margin-bottom:8px}
  .st-card h3{margin:0 0 4px; font-size:17px}
  .st-card p{margin:0 0 14px; color:var(--soft); font-size:13px; flex:1}
  .soon{align-self:flex-start; font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:#8A5A00; background:#FFF3DB; border:1px solid #F3D89A; padding:3px 9px; border-radius:20px}
</style>
@endpush

@section('content')
<main class="stampe">
  <div class="st-card">
    <span class="ic">⛺</span>
    <h3>Cartello tenda</h3>
    <p>Foglio A4 con nome tenda + occupanti, da affiggere fuori. Si stampa
      <strong>una tenda alla volta</strong> dalla lista: Struttura campo → «Cartello».</p>
    <a class="btn btn-ghost btn-sm" href="{{ route('struttura.index') }}">Vai a Struttura campo</a>
  </div>

  <div class="st-card">
    <span class="ic">🕒</span>
    <h3>Orario campo</h3>
    <p>Foglio A4 con l'orario del campo da affiggere.</p>
    <span class="soon">In arrivo</span>
  </div>
  <div class="st-card">
    <span class="ic">📇</span>
    <h3>Referenti campo</h3>
    <p>Elenco dei referenti con ruolo e cellulare, per la bacheca.</p>
    <span class="soon">In arrivo</span>
  </div>
  <div class="st-card">
    <span class="ic">🗓️</span>
    <h3>Turni</h3>
    <p>Prospetto dei turni da stampare e distribuire.</p>
    <span class="soon">In arrivo</span>
  </div>
</main>
@endsection
