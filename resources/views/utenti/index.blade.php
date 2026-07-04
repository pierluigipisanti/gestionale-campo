@extends('layouts.app')

@section('title', 'Utenti')
@section('sezione', 'Gestione utenti')

@push('styles')
<style>
  .utenti{display:grid; grid-template-columns:320px 1fr; gap:20px; max-width:1120px; margin:20px auto; padding:0 24px}
  @media (max-width:820px){ .utenti{grid-template-columns:1fr} }
  .entry{align-self:start; position:sticky; top:84px}
  @media (max-width:820px){ .entry{position:static} }
  table.u{width:100%; border-collapse:collapse; font-size:15px}
  table.u th{text-align:left; font-size:12px; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); font-weight:700; padding:8px 10px; border-bottom:1px solid var(--line)}
  table.u td{padding:10px; border-bottom:1px solid var(--line)}
  .role{font-size:11px; font-weight:700; letter-spacing:.03em; padding:2px 9px; border-radius:20px; text-transform:uppercase}
  .role.admin{background:#FDECEA; color:#9a241a; border:1px solid #F3C7C1}
  .role.operatore{background:#EEF1F5; color:var(--ink); border:1px solid var(--line)}
</style>
@endpush

@section('content')
<main class="utenti">
  <section class="panel entry">
    <h2 class="panel-title">Nuovo utente</h2>
    <form method="post" action="{{ route('utenti.store') }}">
      @csrf
      <label class="field"><span>Nome</span>
        <input name="name" value="{{ old('name') }}" required autocomplete="off"></label>
      @error('name')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Email</span>
        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="off"></label>
      @error('email')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Password</span>
        <input type="password" name="password" required autocomplete="new-password"></label>
      @error('password')<p class="err">{{ $message }}</p>@enderror
      <label class="field"><span>Ruolo</span>
        <select name="ruolo">
          <option value="operatore" @selected(old('ruolo')=='operatore')>Operatore</option>
          <option value="admin" @selected(old('ruolo')=='admin')>Admin</option>
        </select></label>
      <button class="btn btn-block" type="submit">Crea utente</button>
    </form>
  </section>

  <section class="panel">
    <h2 class="panel-title">Utenti ({{ $utenti->count() }})</h2>
    <table class="u">
      <thead><tr><th>Nome</th><th>Email</th><th>Ruolo</th><th></th></tr></thead>
      <tbody>
        @foreach ($utenti as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td><span class="role {{ $u->ruolo }}">{{ $u->ruolo }}</span></td>
            <td style="text-align:right; white-space:nowrap">
              <a class="btn btn-ghost btn-sm" href="{{ route('utenti.edit', $u) }}">Modifica</a>
              @if ($u->id !== auth()->id())
                <form method="post" action="{{ route('utenti.destroy', $u) }}" style="display:inline"
                      onsubmit="return confirm('Eliminare l\'utente {{ $u->name }}?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-ghost btn-sm" style="color:#8A1C11; border-color:#F3C7C1" type="submit">Elimina</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </section>
</main>
@endsection
