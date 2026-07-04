<?php

namespace Tests\Feature;

use App\Actions\RegistraEntrata;
use App\Models\Accesso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VarcoWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create(['ruolo' => 'operatore']));
    }

    private function seedCampo(): int
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        return DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo Test', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_pagina_varco_si_apre(): void
    {
        $this->seedCampo();
        $this->get('/varco')->assertOk()->assertSee('Registra ENTRATA');
    }

    public function test_root_apre_la_dashboard(): void
    {
        $this->seedCampo();
        $this->get('/')->assertOk()->assertSee('Persone alloggiate');
    }

    public function test_registra_entrata_dal_form(): void
    {
        $this->seedCampo();

        $this->post('/varco', ['cognome' => 'Rossi', 'nome' => 'Mario', 'ente_appartenenza' => 'VVF'])
            ->assertRedirect();

        $this->assertDatabaseHas('accessi', ['cognome' => 'Rossi', 'nome' => 'Mario', 'uscita_at' => null]);
    }

    public function test_cognome_obbligatorio(): void
    {
        $this->seedCampo();
        $this->post('/varco', [])->assertSessionHasErrors('cognome');
    }

    public function test_registra_uscita_per_scansione_cf(): void
    {
        $campoId = $this->seedCampo();
        $accesso = (new RegistraEntrata)($campoId, ['cognome' => 'Tecnico', 'nome' => 'ENEL', 'codice_fiscale' => 'TCNENL80A01H501U']);

        $this->post(route('varco.uscita'), ['q' => 'TCNENL80A01H501U'])->assertRedirect(route('varco.index'));

        $this->assertNotNull($accesso->fresh()->uscita_at);
    }

    public function test_uscita_per_cognome(): void
    {
        $campoId = $this->seedCampo();
        $accesso = (new RegistraEntrata)($campoId, ['cognome' => 'Neri', 'nome' => 'Ivo']);

        $this->post(route('varco.uscita'), ['q' => 'Neri'])->assertRedirect(route('varco.index'));

        $this->assertNotNull($accesso->fresh()->uscita_at);
    }
}
