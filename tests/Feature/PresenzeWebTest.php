<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Presenza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PresenzeWebTest extends TestCase
{
    use RefreshDatabase;

    private Persona $p1;
    private Persona $p2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
        $a = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $b = Posto::create(['tenda_id' => $tendaId, 'numero' => '2', 'stato' => 'libero']);

        $this->p1 = Persona::create(['campo_id' => $campoId, 'cognome' => 'Rossi', 'nome' => 'Mario', 'stato' => 'pre_registrato']);
        $this->p2 = Persona::create(['campo_id' => $campoId, 'cognome' => 'Bianchi', 'nome' => 'Ada', 'stato' => 'pre_registrato']);
        (new EseguiCheckIn)($this->p1, $a);
        (new EseguiCheckIn)($this->p2, $b);
    }

    public function test_pagina_si_apre_con_le_persone_in_forza(): void
    {
        $this->get('/presenze')->assertOk()->assertSee('Rossi')->assertSee('Bianchi');
    }

    public function test_consolida_la_giornata(): void
    {
        $oggi = today()->toDateString();

        // niente stati a mano: derivati dallo stato corrente (entrambi presenti)
        $this->post('/presenze', ['data' => $oggi])->assertRedirect(route('presenze.index', ['data' => $oggi]));

        $this->assertDatabaseHas('presenze', ['persona_id' => $this->p1->id, 'data' => $oggi, 'stato' => 'presente']);
        $this->assertDatabaseHas('presenze', ['persona_id' => $this->p2->id, 'data' => $oggi, 'stato' => 'presente']);
        $this->assertSame(2, Presenza::count());
    }

    public function test_riconsolidare_non_duplica(): void
    {
        $oggi = today()->toDateString();

        $this->post('/presenze', ['data' => $oggi]);
        $this->post('/presenze', ['data' => $oggi]);

        $this->assertSame(1, Presenza::where('persona_id', $this->p1->id)->whereDate('data', $oggi)->count());
    }

    public function test_richiede_la_data(): void
    {
        $this->post('/presenze', [])->assertSessionHasErrors('data');
    }
}
