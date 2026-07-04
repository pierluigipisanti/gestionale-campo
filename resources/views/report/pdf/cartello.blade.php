<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 1.6cm; }
  body { font-family: "DejaVu Sans", sans-serif; color: #152238; }
  .foglio { page-break-after: always; text-align: center; }
  .foglio:last-child { page-break-after: auto; }
  .loghi { margin-bottom: 8px; }
  .loghi img { margin: 0 8px; vertical-align: middle; }
  .eyebrow { font-size: 12px; letter-spacing: 3px; text-transform: uppercase; color: #E4560E; font-weight: bold; }
  .codice { font-size: 110px; font-weight: bold; letter-spacing: -2px; line-height: 1; margin: 8px 0 0; }
  .sub { font-size: 17px; color: #5C6880; margin: 8px 0 24px; }
  table.occ { width: 100%; border-collapse: collapse; }
  table.occ td { padding: 11px 14px; border-bottom: 1px solid #D6DDE6; text-align: left; font-size: 23px; }
  table.occ td.p { width: 74px; text-align: center; font-weight: bold; color: #5C6880; }
  .vuoto { color: #9AA6B8; font-size: 22px; margin-top: 40px; }
</style>
</head>
<body>
@foreach ($tende as $t)
  <div class="foglio">
    @if (count($loghi))
      <div class="loghi">@foreach ($loghi as $l)<img src="{{ $l['src'] }}" style="max-height:{{ $l['h'] }}px" alt="">@endforeach</div>
    @endif
    <div class="eyebrow">Protezione Civile · {{ $campo->nome }}</div>
    <div class="codice">{{ $t->codice }}</div>
    <div class="sub">
      @if ($t->settore)Settore {{ $t->settore }}@endif
      @if ($t->fila) · Fila {{ $t->fila }}@endif
      · {{ $t->posti->where('stato', 'occupato')->count() }}/{{ $t->posti->count() }} occupati
    </div>

    @php $occupata = $t->posti->contains(fn ($p) => isset($occupantiPer[$p->id])); @endphp
    @if (! $occupata)
      <p class="vuoto">Tenda libera</p>
    @else
      <table class="occ">
        @foreach ($t->posti as $p)
          @php $o = $occupantiPer[$p->id] ?? null; @endphp
          @if ($o)
            <tr><td class="p">{{ $p->numero }}</td><td>{{ $o->cognome }} {{ $o->nome }}</td></tr>
          @endif
        @endforeach
      </table>
    @endif
  </div>
@endforeach
</body>
</html>
