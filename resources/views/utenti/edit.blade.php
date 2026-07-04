@extends('layouts.app')

@section('title', 'Modifica utente')
@section('sezione', 'Modifica utente')

@section('content')
<main class="wrap">
  <p><a href="{{ route('utenti.index') }}" class="back">← Utenti</a></p>
  <section class="panel" style="max-width:520px">
    <h2 class="panel-title">Utente</h2>
    <form method="post" action="{{ route('utenti.update', $utente) }}">
      @csrf @method('PATCH')
      <label class="field"><span>Nome</span>
        <input name="name" value="{{ old('name', $utente->name) }}" required></label>
      @error('name')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Email</span>
        <input type="email" name="email" value="{{ old('email', $utente->email) }}" required></label>
      @error('email')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Ruolo</span>
        <select name="ruolo">
          <option value="operatore" @selected(old('ruolo', $utente->ruolo)=='operatore')>Operatore</option>
          <option value="admin" @selected(old('ruolo', $utente->ruolo)=='admin')>Admin</option>
        </select></label>
      <label class="field"><span>Nuova password (lascia vuoto per non cambiarla)</span>
        <input type="password" name="password" autocomplete="new-password"></label>
      @error('password')<p class="err">{{ $message }}</p>@enderror
      <button class="btn btn-block" type="submit">Salva</button>
    </form>
  </section>
</main>
@endsection
