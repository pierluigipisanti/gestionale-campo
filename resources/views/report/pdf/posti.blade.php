@extends('report.pdf.layout')

@section('titolo', 'Occupazione posti')

@section('content')
@php
  $tot = 0; $occ = 0;
  foreach ($tende as $t) { $tot += $t->posti->count(); $occ += $t->posti->where('stato', 'occupato')->count(); }
@endphp
<p class="tot">{{ $occ }} / {{ $tot }} posti occupati</p>

@forelse ($tende->groupBy('settore') as $settore => $set)
  <p class="sett">Settore {{ $settore ?: '—' }}</p>
  <table class="data">
    <thead><tr><th>Tenda</th><th>Posto</th><th>Stato</th><th>Occupante</th></tr></thead>
    <tbody>
      @foreach ($set as $t)
        @foreach ($t->posti as $posto)
          @php $p = $occupanti[$posto->id] ?? null; @endphp
          <tr>
            <td>{{ $t->codice }}</td>
            <td>{{ $posto->numero }}</td>
            <td>{{ ucfirst($posto->stato) }}</td>
            <td>{{ $p ? $p->cognome.' '.$p->nome : '' }}</td>
          </tr>
        @endforeach
      @endforeach
    </tbody>
  </table>
@empty
  <p class="vuoto">Nessuna tenda configurata.</p>
@endforelse
@endsection
