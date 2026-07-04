<?php

namespace Tests\Feature;

use App\Models\Automezzo;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('campi')->insert(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function xlsx(array $righe): UploadedFile
    {
        $ss = new Spreadsheet();
        $ss->getActiveSheet()->fromArray($righe, null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($ss))->save($path);

        return new UploadedFile($path, 'import.xlsx', null, null, true);
    }

    public function test_pagina_e_template(): void
    {
        $this->get('/import')->assertOk()->assertSee('Volontari');
        $this->get(route('import.template.volontari'))->assertOk();
        $this->get(route('import.template.automezzi'))->assertOk();
    }

    public function test_import_volontari(): void
    {
        $file = $this->xlsx([
            ['Cognome', 'Nome', 'Codice fiscale', 'Cellulare', 'Categoria', 'Ente appartenenza', 'Specializzazione', 'Patente'],
            ['Rossi', 'Mario', 'RSSMRA80A01H501U', '3331234567', 'Volontario', 'ANPAS', 'Logistica', 'B, C'],
        ]);

        $this->post(route('import.volontari'), ['file' => $file])->assertRedirect(route('import.index'));

        $this->assertDatabaseHas('persone', [
            'codice_fiscale' => 'RSSMRA80A01H501U', 'stato' => 'pre_registrato', 'ente_appartenenza' => 'ANPAS',
            'specializzazione' => 'Logistica', 'patente' => 'B, C',
        ]);
        // categoria creata al volo
        $this->assertDatabaseHas('categorie_persona', ['nome' => 'Volontario']);
    }

    public function test_reimport_aggiorna_per_cf(): void
    {
        $cf = 'RSSMRA80A01H501U';
        $this->post(route('import.volontari'), ['file' => $this->xlsx([['Cognome', 'Codice fiscale'], ['Rossi', $cf]])]);
        $this->post(route('import.volontari'), ['file' => $this->xlsx([['Cognome', 'Codice fiscale', 'Cellulare'], ['Rossini', $cf, '999']])]);

        $this->assertSame(1, Persona::where('codice_fiscale', $cf)->count());
        $this->assertSame('Rossini', Persona::where('codice_fiscale', $cf)->value('cognome'));
    }

    public function test_import_automezzi(): void
    {
        $file = $this->xlsx([
            ['Tipologia', 'Ente', 'Targa', 'Descrizione'],
            ['Ambulanza', 'Croce Rossa', 'ab 123 cd', 'Soccorso'],
        ]);

        $this->post(route('import.automezzi'), ['file' => $file])->assertRedirect(route('import.index'));

        $this->assertDatabaseHas('automezzi', ['targa' => 'AB 123 CD', 'tipo' => 'Ambulanza', 'stato' => 'fuori']);
    }
}
