<?php

namespace App\Http\Controllers;

use App\Models\Accesso;
use App\Models\Campo;
use App\Models\Logo;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Tenda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

// Report stampabili (PDF) ed esportabili (CSV). PDF con i loghi in intestazione.
class ReportController extends Controller
{
    public function index()
    {
        return view('report.index');
    }

    // --- Presenze: persone alloggiate nel campo ora ---

    public function presenzePdf()
    {
        $campo = Campo::firstOrFail();

        return Pdf::loadView('report.pdf.presenze', [
            'campo'   => $campo,
            'persone' => $this->presenti($campo->id),
            'loghi'   => $this->loghi($campo->id),
            'data'    => now(),
        ])->download('presenze-'.now()->format('Y-m-d').'.pdf');
    }

    public function presenzeCsv()
    {
        $campo = Campo::firstOrFail();
        $persone = $this->presenti($campo->id);

        return $this->csv('presenze-'.now()->format('Y-m-d').'.csv',
            ['Cognome', 'Nome', 'Categoria', 'Nucleo', 'Tenda', 'Posto', 'Dal'],
            $persone->map(fn ($p) => [
                $p->cognome, $p->nome, $p->categoria?->nome, $p->nucleo?->etichetta,
                $p->posto?->tenda?->codice, $p->posto?->numero,
                $p->ultimo_movimento_at?->format('d/m/Y H:i'),
            ]));
    }

    // --- Occupazione posti ---

    public function postiPdf()
    {
        $campo = Campo::firstOrFail();

        $tende = Tenda::where('campo_id', $campo->id)
            ->with(['posti' => fn ($q) => $q->orderByRaw('length(numero), numero')])
            ->orderBy('settore')->orderBy('fila')->orderBy('codice')->get();

        $occupanti = Persona::where('campo_id', $campo->id)->where('stato', 'presente')
            ->whereNotNull('posto_id')->get()->keyBy('posto_id');

        return Pdf::loadView('report.pdf.posti', [
            'campo'     => $campo,
            'tende'     => $tende,
            'occupanti' => $occupanti,
            'loghi'     => $this->loghi($campo->id),
            'data'      => now(),
        ])->download('occupazione-posti-'.now()->format('Y-m-d').'.pdf');
    }

    // --- Accessi: chi è nel campo ora (registro varco) ---

    public function accessiPdf()
    {
        $campo = Campo::firstOrFail();

        return Pdf::loadView('report.pdf.accessi', [
            'campo'   => $campo,
            'accessi' => $this->dentro($campo->id),
            'loghi'   => $this->loghi($campo->id),
            'data'    => now(),
        ])->download('accessi-'.now()->format('Y-m-d-Hi').'.pdf');
    }

    public function accessiCsv()
    {
        $campo = Campo::firstOrFail();

        return $this->csv('accessi-'.now()->format('Y-m-d-Hi').'.csv',
            ['Cognome', 'Nome', 'Codice fiscale', 'Cellulare', 'Categoria', 'Ente', 'Documento', 'Targa', 'Entrata'],
            $this->dentro($campo->id)->map(fn ($a) => [
                $a->cognome, $a->nome, $a->codice_fiscale, $a->telefono, $a->categoria?->nome, $a->ente_appartenenza,
                $a->documento, $a->targa_veicolo, $a->entrata_at?->format('d/m/Y H:i'),
            ]));
    }

    // --- cartelli tenda (A4 da affiggere fuori dalla tenda) ---

    public function cartelloTenda(Tenda $tenda)
    {
        $campo = Campo::firstOrFail();
        $tenda->load(['posti' => fn ($q) => $q->orderByRaw('length(numero), numero')]);

        return Pdf::loadView('report.pdf.cartello', [
            'campo'        => $campo,
            'tende'        => collect([$tenda]),
            'occupantiPer' => $this->occupanti($tenda->posti->pluck('id')),
            'loghi'        => $this->loghi($campo->id),
        ])->download('cartello-'.$tenda->codice.'.pdf');
    }

    public function cartelliTende()
    {
        $campo = Campo::firstOrFail();
        $tende = Tenda::where('campo_id', $campo->id)
            ->with(['posti' => fn ($q) => $q->orderByRaw('length(numero), numero')])
            ->orderBy('settore')->orderBy('fila')->orderBy('codice')->get();

        return Pdf::loadView('report.pdf.cartello', [
            'campo'        => $campo,
            'tende'        => $tende,
            'occupantiPer' => $this->occupanti($tende->pluck('posti')->flatten()->pluck('id')),
            'loghi'        => $this->loghi($campo->id),
        ])->download('cartelli-tende.pdf');
    }

    private function occupanti($postoIds)
    {
        return Persona::where('stato', 'presente')->whereIn('posto_id', $postoIds)->get()->keyBy('posto_id');
    }

    // --- helper ---

    private function presenti(int $campoId)
    {
        return Persona::with(['categoria', 'nucleo', 'posto.tenda'])
            ->where('campo_id', $campoId)->where('stato', 'presente')
            ->orderBy('cognome')->orderBy('nome')->get();
    }

    private function dentro(int $campoId)
    {
        return Accesso::with('categoria')->where('campo_id', $campoId)
            ->dentro()->orderByDesc('entrata_at')->get();
    }

    /** @return array<int, array{etichetta:string, src:string}> loghi in base64 per il PDF */
    private function loghi(int $campoId): array
    {
        return Logo::where('campo_id', $campoId)->where('stampe', true)
            ->orderBy('ordine')->orderBy('id')->get()
            ->map(fn ($l) => [
                'etichetta' => $l->etichetta,
                'src'       => 'data:'.$l->mime.';base64,'.base64_encode(Storage::disk('public')->get($l->path)),
                'h'         => ['S' => 34, 'M' => 54, 'L' => 92][$l->dimensione] ?? 54,
            ])->all();
    }

    /** CSV con BOM e separatore ';' per Excel italiano. */
    private function csv(string $filename, array $intestazione, $righe)
    {
        // Anti CSV formula injection: neutralizza le celle che Excel interpreterebbe come formula
        $safe = fn ($cella) => is_string($cella) && preg_match('/^[=+\-@\t\r]/', $cella) ? "'".$cella : $cella;

        return response()->streamDownload(function () use ($intestazione, $righe, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, array_map($safe, $intestazione), ';');
            foreach ($righe as $r) {
                fputcsv($out, array_map($safe, (array) $r), ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
