<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Accesso — Gestionale campo</title>
<style>
  :root{
    --bg:#EAEEF3; --surface:#fff; --ink:#152238; --soft:#5C6880;
    --line:#D6DDE6; --accent:#E4560E; --accent-d:#C2470B;
    --shadow:0 1px 2px rgba(21,34,56,.06), 0 10px 30px rgba(21,34,56,.10);
  }
  *{box-sizing:border-box}
  body{
    margin:0; min-height:100vh; background:var(--bg); color:var(--ink);
    font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    display:flex; align-items:center; justify-content:center; padding:24px;
  }
  .card{width:100%; max-width:380px; background:var(--surface); border:1px solid var(--line);
        border-radius:14px; box-shadow:var(--shadow); padding:28px}
  .eyebrow{font-size:11px; letter-spacing:.16em; text-transform:uppercase; color:var(--accent); font-weight:800}
  h1{margin:6px 0 20px; font-size:22px; font-weight:800; letter-spacing:-.01em}
  .field{display:block; margin-bottom:14px}
  .field > span{display:block; font-size:13px; font-weight:600; color:var(--soft); margin-bottom:5px}
  .field input{width:100%; padding:12px 13px; font-size:16px; color:var(--ink); background:#fff;
               border:1.5px solid var(--line); border-radius:9px; font-family:inherit}
  .field input:focus{outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.15)}
  .remember{display:flex; align-items:center; gap:8px; font-size:14px; color:var(--soft); margin-bottom:18px}
  .btn{width:100%; padding:14px; font:inherit; font-weight:700; font-size:16px; color:#fff;
       background:var(--accent); border:none; border-radius:9px; cursor:pointer}
  .btn:hover{background:var(--accent-d)}
  .flash-bad{margin:0 0 16px; padding:11px 14px; border-radius:9px; background:#FDECEA; color:#8A1C11;
             border:1px solid #F3C0BA; font-weight:600; font-size:14px}
  .err{margin:-8px 0 12px; color:#B42318; font-size:13px; font-weight:600}
</style>
</head>
<body>
  <form class="card" method="post" action="{{ route('login') }}">
    @csrf
    <span class="eyebrow">Protezione Civile</span>
    <h1>Gestionale campo</h1>

    @if (session('err'))<div class="flash-bad" role="alert">{{ session('err') }}</div>@endif

    <label class="field">
      <span>Email</span>
      <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
    </label>
    @error('email')<p class="err">{{ $message }}</p>@enderror

    <label class="field">
      <span>Password</span>
      <input type="password" name="password" required autocomplete="current-password">
    </label>

    <label class="remember">
      <input type="checkbox" name="remember" value="1"> Ricordami su questo dispositivo
    </label>

    <button class="btn" type="submit">Accedi</button>
  </form>
</body>
</html>
