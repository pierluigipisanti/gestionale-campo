<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Actions\RegistraEntrata;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $ospite = DB::table('categorie_persona')->insertGetId(['nome' => 'Ospite', 'attiva' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);

        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $persona = Persona::create(['campo_id' => $campoId, 'categoria_id' => $ospite, 'cognome' => 'Rossi', 'nome' => 'Mario', 'stato' => 'pre_registrato']);
        (new EseguiCheckIn)($persona, $posto);
        (new RegistraEntrata)($campoId, ['cognome' => 'Tecnico', 'nome' => 'ENEL', 'categoria_id' => $ospite, 'ente_appartenenza' => 'ENEL']);
    }

    public function test_hub_report_si_apre(): void
    {
        $this->get('/report')->assertOk()->assertSee('Presenze');
    }

    public function test_presenze_pdf(): void
    {
        $r = $this->get(route('report.presenze.pdf'));
        $r->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $r->getContent());
    }

    public function test_presenze_csv_contiene_i_dati(): void
    {
        $r = $this->get(route('report.presenze.csv'));
        $r->assertOk();
        $csv = $r->streamedContent();
        $this->assertStringContainsString('Cognome', $csv);
        $this->assertStringContainsString('Rossi', $csv);
    }

    public function test_csv_neutralizza_formula_injection(): void
    {
        $campoId = DB::table('campi')->value('id');
        $ospite = DB::table('categorie_persona')->value('id');
        $tendaId = DB::table('tende')->value('id');
        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '2', 'stato' => 'libero']);
        $persona = Persona::create(['campo_id' => $campoId, 'categoria_id' => $ospite, 'cognome' => '=SUM(1+1)', 'nome' => 'X', 'stato' => 'pre_registrato']);
        (new EseguiCheckIn)($persona, $posto);

        $csv = $this->get(route('report.presenze.csv'))->streamedContent();
        $this->assertStringContainsString("'=SUM(1+1)", $csv); // neutralizzato con apice
        $this->assertStringNotContainsString(';=SUM', $csv);    // mai formula a inizio cella
    }

    public function test_posti_pdf(): void
    {
        $this->assertStringStartsWith('%PDF', $this->get(route('report.posti.pdf'))->getContent());
    }

    public function test_accessi_pdf(): void
    {
        $this->assertStringStartsWith('%PDF', $this->get(route('report.accessi.pdf'))->getContent());
    }

    public function test_accessi_csv_contiene_i_dati(): void
    {
        $csv = $this->get(route('report.accessi.csv'))->streamedContent();
        $this->assertStringContainsString('Tecnico', $csv);
    }

    public function test_cartello_tenda_pdf(): void
    {
        $tenda = \App\Models\Tenda::first();
        $this->assertStringStartsWith('%PDF', $this->get(route('stampe.tenda', $tenda))->getContent());
    }

    public function test_cartelli_tende_pdf(): void
    {
        $this->assertStringStartsWith('%PDF', $this->get(route('stampe.cartelli'))->getContent());
    }
}
