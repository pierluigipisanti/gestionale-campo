<?php

namespace App\Http\Controllers;

use App\Models\Campo;
use App\Models\Logo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Loghi (ente, comune, Protezione Civile...) per report e tesserini — solo admin.
class LoghiController extends Controller
{
    public function index()
    {
        $campo = Campo::firstOrFail();

        return view('loghi.index', [
            'loghi' => Logo::where('campo_id', $campo->id)->orderBy('etichetta')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'etichetta' => ['required', 'string', 'max:100'],
            'file'      => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $file = $request->file('file');
        $path = $file->store('loghi', 'public');

        Logo::create([
            'campo_id'  => Campo::firstOrFail()->id,
            'etichetta' => $data['etichetta'],
            'path'      => $path,
            'mime'      => $file->getMimeType(),
        ]);

        return redirect()->route('loghi.index')->with('ok', "Logo \"{$data['etichetta']}\" caricato.");
    }

    public function update(Request $request, Logo $logo)
    {
        $data = $request->validate([
            'etichetta'  => ['required', 'string', 'max:100'],
            'ordine'     => ['nullable', 'integer', 'min:0', 'max:99'],
            'dimensione' => ['nullable', 'in:S,M,L'],
        ]);

        $logo->update([
            'etichetta'  => $data['etichetta'],
            'stampe'     => $request->boolean('stampe'),
            'ordine'     => $data['ordine'] ?? 0,
            'dimensione' => $data['dimensione'] ?? 'M',
        ]);

        return back()->with('ok', "Logo \"{$logo->etichetta}\" aggiornato.");
    }

    // Serve il file dal disco 'public' senza dipendere da storage:link.
    public function file(Logo $logo)
    {
        abort_unless(Storage::disk('public')->exists($logo->path), 404);

        return response()->file(Storage::disk('public')->path($logo->path));
    }

    public function destroy(Logo $logo)
    {
        Storage::disk('public')->delete($logo->path);
        $etichetta = $logo->etichetta;
        $logo->delete();

        return back()->with('ok', "Logo \"{$etichetta}\" eliminato.");
    }
}
