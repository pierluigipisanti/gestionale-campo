<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 168px 36px 54px 36px; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #152238; }

  .header { position: fixed; top: -148px; left: 0; right: 0; }
  .header .loghi { margin-bottom: 6px; }
  .header .loghi img { max-height: 42px; max-width: 130px; margin-right: 14px; vertical-align: middle; }
  .header .titolo { border-bottom: 2px solid #E4560E; padding-bottom: 6px; }
  .header .eyebrow { font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #E4560E; font-weight: bold; }
  .header h1 { font-size: 16px; margin: 2px 0 0; }
  .header .meta { font-size: 10px; color: #5C6880; margin-top: 2px; }

  .footer { position: fixed; bottom: -36px; left: 0; right: 0; height: 24px; font-size: 9px;
            color: #9AA6B8; border-top: 1px solid #D6DDE6; padding-top: 5px; }

  .tot { font-size: 12px; font-weight: bold; margin: 0 0 10px; }
  .sett { font-weight: bold; font-size: 12px; margin: 14px 0 4px; }
  .vuoto { color: #9AA6B8; }

  table.data { width: 100%; border-collapse: collapse; }
  table.data th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .5px;
                  color: #5C6880; border-bottom: 1.5px solid #152238; padding: 5px 6px; }
  table.data td { padding: 5px 6px; border-bottom: 1px solid #E1E6ED; }
  table.data tr:nth-child(even) td { background: #F5F7FA; }
</style>
</head>
<body>
  <div class="header">
    @if (count($loghi))
      <div class="loghi">
        @foreach ($loghi as $l)<img src="{{ $l['src'] }}" alt="{{ $l['etichetta'] }}">@endforeach
      </div>
    @endif
    <div class="titolo">
      <span class="eyebrow">Protezione Civile · {{ $campo->nome }}</span>
      <h1>@yield('titolo')</h1>
      <div class="meta">Generato il {{ $data->format('d/m/Y H:i') }}</div>
    </div>
  </div>

  <div class="footer">Gestionale campo — {{ $campo->nome }} · documento generato automaticamente</div>

  @yield('content')
</body>
</html>
