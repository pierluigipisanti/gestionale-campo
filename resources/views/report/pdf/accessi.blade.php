@extends('report.pdf.layout')

@section('titolo', 'Accessi — chi è nel campo ora')

@section('content')
<p class="tot">{{ $accessi->count() }} persone presenti al varco</p>

@if ($accessi->isEmpty())
  <p class="vuoto">Nessun accesso aperto.</p>
@else
  <table class="data">
    <thead>
      <tr><th>Cognome</th><th>Nome</th><th>Categoria</th><th>Ente</th><th>Documento</th><th>Targa</th><th>Entrata</th></tr>
    </thead>
    <tbody>
      @foreach ($accessi as $a)
        <tr>
          <td>{{ $a->cognome }}</td>
          <td>{{ $a->nome }}</td>
          <td>{{ $a->categoria?->nome }}</td>
          <td>{{ $a->ente_appartenenza }}</td>
          <td>{{ $a->documento }}</td>
          <td>{{ $a->targa_veicolo }}</td>
          <td>{{ $a->entrata_at?->format('d/m H:i') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif
@endsection
