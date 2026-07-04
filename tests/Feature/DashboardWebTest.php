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

class DashboardWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);

        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $persona = Persona::create(['campo_id' => $campoId, 'cognome' => 'Rossi', 'nome' => 'Mario', 'codice_fiscale' => 'RSSMRA80A01H501U', 'stato' => 'pre_registrato']);
        (new EseguiCheckIn)($persona, $posto);

        (new RegistraEntrata)($campoId, ['cognome' => 'Bianchi', 'nome' => 'Ada', 'ente_appartenenza' => 'VVF']);
    }

    public function test_dashboard_si_apre(): void
    {
        $this->get('/')->assertOk()->assertSee('Persone alloggiate')->assertSee('Transiti al varco');
    }

    public function test_ricerca_persona_per_cognome(): void
    {
        $this->get('/cerca?q=Rossi')->assertOk()->assertSee('Rossi')->assertSee('A-01/1');
    }

    public function test_ricerca_persona_per_codice_fiscale(): void
    {
        $this->get('/cerca?q=RSSMRA80A01H501U')->assertOk()->assertSee('Rossi');
    }

    public function test_ricerca_trova_accesso_al_varco(): void
    {
        $this->get('/cerca?q=Bianchi')->assertOk()->assertSee('Bianchi');
    }

    public function test_ricerca_troppo_corta(): void
    {
        $this->get('/cerca?q=R')->assertOk()->assertSee('almeno 2 caratteri');
    }
}
