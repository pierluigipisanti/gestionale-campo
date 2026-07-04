<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Gestionale') — {{ $campoCorrente?->nome ?? 'Campo' }}</title>
<style>
  :root{
    --bg:#EEF1F6; --surface:#fff; --ink:#141B2D; --soft:#5B667E;
    --line:#E4E8F0; --accent:#E4560E; --accent-d:#C2470B;
    --inside:#1B8A4B; --inside-bg:#E6F3EC;
    --side:#141B2D; --side-2:#1D273C; --side-soft:#93A0BC; --side-line:#2A3550;
    --shadow:0 1px 2px rgba(20,27,45,.04), 0 8px 24px rgba(20,27,45,.06);
    --radius:14px;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0; background:var(--bg); color:var(--ink);
    font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    font-size:15px; line-height:1.45; -webkit-text-size-adjust:100%;
  }
  a{color:var(--accent)}
  .tabnum{font-variant-numeric:tabular-nums}

  /* ---- shell: sidebar + main ---- */
  .app{display:flex; min-height:100%}
  .sidebar{
    width:236px; flex:0 0 236px; background:var(--side); color:#fff;
    display:flex; flex-direction:column; position:sticky; top:0; height:100vh;
  }
  .brand{display:flex; align-items:center; gap:10px; padding:18px 20px; border-bottom:1px solid var(--side-line)}
  .brand .mark{width:34px; height:34px; border-radius:9px; background:var(--accent); display:flex; align-items:center; justify-content:center; font-size:18px}
  .brand .txt{min-width:0}
  .brand .eyebrow{display:block; font-size:9.5px; letter-spacing:.16em; text-transform:uppercase; color:var(--side-soft); font-weight:800}
  .brand .name{display:block; font-size:15px; font-weight:800; letter-spacing:-.01em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis}

  .side-nav{flex:1; overflow-y:auto; padding:12px 12px 20px}
  .side-sec{margin:14px 8px 6px; font-size:10px; letter-spacing:.12em; text-transform:uppercase; color:var(--side-soft); font-weight:800}
  .side-nav a{
    display:flex; align-items:center; gap:11px; padding:9px 12px; margin-bottom:2px;
    color:#C4CDE0; text-decoration:none; font-weight:600; font-size:14px; border-radius:9px;
  }
  .side-nav a .ic{width:18px; text-align:center; font-size:15px; opacity:.9; flex:0 0 18px}
  .side-nav a:hover{background:var(--side-2); color:#fff}
  .side-nav a.active{background:var(--accent); color:#fff}
  .side-nav a.active .ic{opacity:1}

  .side-user{border-top:1px solid var(--side-line); padding:12px 14px; display:flex; align-items:center; gap:10px}
  .side-user .who{flex:1; min-width:0}
  .side-user .who .n{display:block; font-size:13px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis}
  .side-user .who .r{display:block; font-size:10px; letter-spacing:.06em; text-transform:uppercase; color:var(--side-soft); font-weight:700}
  .side-user .logout{padding:7px 12px; font:inherit; font-size:13px; font-weight:700; color:#C4CDE0; background:var(--side-2); border:none; border-radius:8px; cursor:pointer}
  .side-user .logout:hover{background:#2A3550; color:#fff}

  .main{flex:1; min-width:0; display:flex; flex-direction:column}
  .topbar{
    position:sticky; top:0; z-index:5; display:flex; align-items:center; gap:16px;
    padding:14px 28px; background:rgba(238,241,246,.85); backdrop-filter:blur(8px);
    border-bottom:1px solid var(--line);
  }
  .topbar h1{margin:0; font-size:19px; font-weight:800; letter-spacing:-.01em}
  .topsearch{margin-left:auto}
  .topsearch input{width:260px; max-width:40vw; padding:10px 13px; font-size:14px; border:1.5px solid var(--line); border-radius:10px; font-family:inherit; background:#fff}
  .topsearch input:focus{outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.14)}

  /* ---- flash ---- */
  .flash{margin:18px 28px 0; padding:12px 16px; border-radius:11px; background:var(--inside-bg); color:#0F5C31; border:1px solid #BFE3CD; font-weight:600}
  .flash.bad{background:#FDECEA; color:#8A1C11; border-color:#F3C0BA}

  /* ---- shared components (invariati per nome, rifiniti) ---- */
  .wrap{max-width:1080px; margin:22px auto; padding:0 28px}
  .back{font-size:14px; font-weight:600; text-decoration:none}
  .panel{background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); padding:22px}
  .panel-title{margin:0 0 16px; font-size:12px; letter-spacing:.1em; text-transform:uppercase; color:var(--soft); font-weight:800}

  .field{display:block; margin-bottom:13px}
  .field > span{display:block; font-size:13px; font-weight:600; color:var(--soft); margin-bottom:6px}
  .field input, .field select, .field textarea{
    width:100%; padding:11px 13px; font-size:15px; color:var(--ink);
    background:#fff; border:1.5px solid var(--line); border-radius:10px; font-family:inherit;
  }
  .field input:focus, .field select:focus, .field textarea:focus{
    outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(228,86,14,.14);
  }
  .field input::placeholder{color:#9AA6BC}
  .err{margin:-7px 0 12px; color:#B42318; font-size:13px; font-weight:600}
  .two{display:grid; grid-template-columns:1fr 1fr; gap:13px}

  details.more{margin:6px 0 16px; border-top:1px solid var(--line); padding-top:13px}
  details.more summary{cursor:pointer; font-size:13px; font-weight:700; color:var(--accent); list-style:none; user-select:none}
  details.more summary::-webkit-details-marker{display:none}
  details.more summary::before{content:"+ "; font-weight:800}
  details.more[open] summary::before{content:"− "}
  details.more[open] summary{margin-bottom:13px}

  .btn{display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:11px 17px; font:inherit; font-weight:700; font-size:15px; line-height:1; color:#fff; background:var(--accent); border:none; border-radius:10px; cursor:pointer; text-decoration:none}
  .btn:hover{background:var(--accent-d)}
  .btn:focus-visible{outline:3px solid rgba(228,86,14,.4); outline-offset:2px}
  .btn-block{width:100%; padding:14px}
  .btn-ghost{color:var(--ink); background:#fff; border:1.5px solid var(--line)}
  .btn-ghost:hover{border-color:var(--soft); background:#F7F9FC}
  .btn-sm{padding:9px 14px; font-size:14px}

  .chip{display:inline-block; font-size:11px; font-weight:700; letter-spacing:.03em; color:var(--ink); background:#EEF1F6; border:1px solid var(--line); padding:1px 8px; border-radius:20px}

  .tabs{display:inline-flex; gap:4px; background:#fff; border:1px solid var(--line); border-radius:12px; padding:4px}
  .tabs a{padding:9px 18px; border-radius:9px; text-decoration:none; font-weight:700; font-size:14px; color:var(--soft)}
  .tabs a.active{background:var(--accent); color:#fff}
  .tabs a:hover:not(.active){background:#F1F4F8; color:var(--ink)}
  .tabs-bar{max-width:1120px; margin:22px auto 0; padding:0 28px}

  /* ---- responsive: sidebar in cima su schermi stretti ---- */
  @media (max-width:820px){
    .app{flex-direction:column}
    .sidebar{width:100%; flex:none; height:auto; position:static; flex-direction:column}
    .side-nav{display:flex; flex-wrap:wrap; gap:4px; padding:10px}
    .side-nav a{margin:0}
    .side-sec{width:100%; margin:8px 4px 2px}
    .topsearch input{width:100%; max-width:none}
  }
</style>
@stack('styles')
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <span class="mark">⛑</span>
      <span class="txt">
        <span class="eyebrow">Protezione Civile</span>
        <span class="name">{{ $campoCorrente?->nome ?? 'Campo' }}</span>
      </span>
    </div>

    <nav class="side-nav">
      <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])><span class="ic">🎛</span> Dashboard</a>
      <a href="{{ route('varco.index') }}" @class(['active' => request()->routeIs('varco.*')])><span class="ic">🚪</span> Varco</a>
      <a href="{{ route('automezzi.index') }}" @class(['active' => request()->routeIs('automezzi.*')])><span class="ic">🚐</span> Automezzi</a>
      <a href="{{ route('posti.index') }}" @class(['active' => request()->routeIs('posti.*')])><span class="ic">⛺</span> Tende</a>
      <a href="{{ route('presenze.index') }}" @class(['active' => request()->routeIs('presenze.*')])><span class="ic">✅</span> Presenze</a>
      <a href="{{ route('struttura.index') }}" @class(['active' => request()->routeIs('struttura.*')])><span class="ic">🧭</span> Struttura campo</a>

      <div class="side-sec">Utility</div>
      <a href="{{ route('report.index') }}" @class(['active' => request()->routeIs('report.*')])><span class="ic">📄</span> Report</a>
      <a href="{{ route('import.index') }}" @class(['active' => request()->routeIs('import.*')])><span class="ic">📥</span> Importa</a>
      <a href="{{ route('stampe.index') }}" @class(['active' => request()->routeIs('stampe.*')])><span class="ic">🖨️</span> Stampe</a>

      @can('admin')
        <div class="side-sec">Configurazione</div>
        <a href="{{ route('campo.edit') }}" @class(['active' => request()->routeIs('campo.*')])><span class="ic">⚙️</span> Campo</a>
        <a href="{{ route('categorie.index') }}" @class(['active' => request()->routeIs('categorie.*')])><span class="ic">🏷️</span> Categorie</a>
        <a href="{{ route('loghi.index') }}" @class(['active' => request()->routeIs('loghi.*')])><span class="ic">🖼️</span> Loghi</a>
        <a href="{{ route('utenti.index') }}" @class(['active' => request()->routeIs('utenti.*')])><span class="ic">👥</span> Utenti</a>
      @endcan
    </nav>

    @auth
      <div class="side-user">
        <div class="who">
          <span class="n">{{ auth()->user()->name }}</span>
          <span class="r">{{ auth()->user()->ruolo }}</span>
        </div>
        <form method="post" action="{{ route('logout') }}">
          @csrf
          <button class="logout" type="submit">Esci</button>
        </form>
      </div>
    @endauth
  </aside>

  <div class="main">
    <header class="topbar">
      <h1>@yield('sezione', 'Gestionale campo')</h1>
      <form class="topsearch" method="get" action="{{ route('cerca') }}">
        <input name="q" value="{{ request('q') }}" placeholder="Cerca nominativo o CF…" aria-label="Cerca">
      </form>
    </header>

    @if (session('ok'))
      <div class="flash" role="status">{{ session('ok') }}</div>
    @endif
    @if (session('err'))
      <div class="flash bad" role="alert">{{ session('err') }}</div>
    @endif

    @yield('content')
  </div>
</div>
</body>
</html>
