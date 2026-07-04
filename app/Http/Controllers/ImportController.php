<?php

namespace App\Http\Controllers;

use App\Models\Automezzo;
use App\Models\Campo;
use App\Models\CategoriaPersona;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Import da Excel: pre-carica l'anagrafica volontari e il parco automezzi.
// Così al check-in, dal codice fiscale, si riconosce subito chi è.
class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    // ---- template scaricabili ----

    public function templateVolontari()
    {
        return $this->template('template-volontari.xlsx',
            ['Cognome', 'Nome', 'Codice fiscale', 'Cellulare', 'Categoria', 'Ente appartenenza', 'Specializzazione', 'Patente', 'Allergie dieta'],
            ['Rossi', 'Mario', 'RSSMRA80A01H501U', '3331234567', 'Volontario', 'ANPAS', 'Logistica', 'B, C', 'Vegetariano']);
    }

    public function templateAutomezzi()
    {
        return $this->template('template-automezzi.xlsx',
            ['Tipologia', 'Ente', 'Targa', 'Descrizione'],
            ['Ambulanza', 'Croce Rossa', 'AB123CD', 'Mezzo di soccorso']);
    }

    // ---- import ----

    public function importVolontari(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120']]);
        $campoId = Campo::firstOrFail()->id;

        $n = 0;
        foreach ($this->leggi($request->file('file')->getRealPath()) as $row) {
            $cognome = trim((string) ($row['cognome'] ?? ''));
            if ($cognome === '') {
                continue;
            }
            $cf = mb_strtoupper(trim((string) ($row['codice_fiscale'] ?? '')));

            $persona = ($cf !== '' ? Persona::where('campo_id', $campoId)->where('codice_fiscale', $cf)->first() : null)
                ?? new Persona(['campo_id' => $campoId, 'stato' => 'pre_registrato']);

            $persona->fill([
                'cognome'           => $cognome,
                'nome'              => trim((string) ($row['nome'] ?? '')),
                'codice_fiscale'    => $cf !== '' ? $cf : null,
                'telefono'          => trim((string) ($row['cellulare'] ?? '')) ?: null,
                'categoria_id'      => $this->categoriaId($row['categoria'] ?? null),
                'ente_appartenenza' => trim((string) ($row['ente_appartenenza'] ?? '')) ?: null,
                'specializzazione'  => trim((string) ($row['specializzazione'] ?? '')) ?: null,
                'patente'           => trim((string) ($row['patente'] ?? '')) ?: null,
                'allergie_dieta'    => trim((string) ($row['allergie_dieta'] ?? '')) ?: null,
            ]);
            if (! $persona->exists) {
                $persona->stato = 'pre_registrato';
            }
            $persona->save();
            $n++;
        }

        return redirect()->route('import.index')->with('ok', "{$n} volontari importati/aggiornati.");
    }

    public function importAutomezzi(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120']]);
        $campoId = Campo::firstOrFail()->id;

        $n = 0;
        foreach ($this->leggi($request->file('file')->getRealPath()) as $row) {
            $targa = mb_strtoupper(trim((string) ($row['targa'] ?? '')));
            if ($targa === '') {
                continue;
            }

            $auto = Automezzo::firstOrNew(['campo_id' => $campoId, 'targa' => $targa]);
            $auto->fill([
                'tipo'              => trim((string) ($row['tipologia'] ?? '')) ?: null,
                'ente_appartenenza' => trim((string) ($row['ente'] ?? '')) ?: null,
                'descrizione'       => trim((string) ($row['descrizione'] ?? '')) ?: null,
            ]);
            if (! $auto->exists) {
                $auto->stato = 'fuori';
            }
            $auto->save();
            $n++;
        }

        return redirect()->route('import.index')->with('ok', "{$n} automezzi importati/aggiornati.");
    }

    // ---- helper ----

    private function template(string $filename, array $intestazioni, array $esempio)
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->fromArray([$intestazioni, $esempio], null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $writer = new Xlsx($ss);

        return response()->streamDownload(fn () => $writer->save('php://output'), $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Legge il file (xlsx/csv) come righe associative con chiavi = intestazioni normalizzate. */
    private function leggi(string $path): array
    {
        $data = IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
        if (empty($data)) {
            return [];
        }

        $headers = array_map(fn ($h) => Str::of((string) $h)->lower()->trim()->replaceMatches('/\s+/', '_')->value(), $data[0]);

        $out = [];
        foreach (array_slice($data, 1) as $r) {
            $row = [];
            foreach ($headers as $i => $h) {
                if ($h !== '') {
                    $row[$h] = $r[$i] ?? null;
                }
            }
            if (array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                $out[] = $row;
            }
        }

        return $out;
    }

    private function categoriaId($nome): ?int
    {
        $nome = trim((string) $nome);
        if ($nome === '') {
            return null;
        }

        return (CategoriaPersona::whereRaw('lower(nome) = ?', [mb_strtolower($nome)])->first()
            ?? CategoriaPersona::create(['nome' => $nome, 'attiva' => true]))->id;
    }
}
