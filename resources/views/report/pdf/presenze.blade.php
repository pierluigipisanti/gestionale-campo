@extends('report.pdf.layout')

@section('titolo', 'Presenze — persone nel campo')

@section('content')
<p class="tot">{{ $persone->count() }} persone presenti</p>

@if ($persone->isEmpty())
  <p class="vuoto">Nessuna persona presente nel campo.</p>
@else
  <table class="data">
    <thead>
      <tr><th>Cognome</th><th>Nome</th><th>Categoria</th><th>Nucleo</th><th>Tenda</th><th>Posto</th><th>Dal</th></tr>
    </thead>
    <tbody>
      @foreach ($persone as $p)
        <tr>
          <td>{{ $p->cognome }}</td>
          <td>{{ $p->nome }}</td>
          <td>{{ $p->categoria?->nome }}</td>
          <td>{{ $p->nucleo?->etichetta }}</td>
          <td>{{ $p->posto?->tenda?->codice }}</td>
          <td>{{ $p->posto?->numero }}</td>
          <td>{{ $p->ultimo_movimento_at?->format('d/m H:i') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif
@endsection
