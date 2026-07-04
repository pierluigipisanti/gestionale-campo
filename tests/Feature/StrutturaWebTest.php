<?php

namespace Tests\Feature;

use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StrutturaWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // admin: la creazione tende è per tutti, l'eliminazione è admin-only
        $this->actingAs(\App\Models\User::factory()->create(['ruolo' => 'admin']));
    }

    private function campoId(): int
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        return DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_pagina_struttura_si_apre(): void
    {
        $this->campoId();
        $this->get('/struttura')->assertOk()->assertSee('Aggiungi tende');
    }

    public function test_crea_tende_in_blocco(): void
    {
        $this->campoId();

        $this->post('/struttura', [
            'settore' => 'B', 'numero_tende' => 3, 'posti_per_tenda' => 6, 'tipo' => 'alloggio',
        ])->assertRedirect(route('struttura.index'));

        $this->assertSame(3, Tenda::where('settore', 'B')->count());
        $this->assertSame(18, Posto::count());
    }

    public function test_validazione_campi_obbligatori(): void
    {
        $this->campoId();
        $this->post('/struttura', ['settore' => 'A'])
            ->assertSessionHasErrors(['numero_tende', 'posti_per_tenda']);
    }

    public function test_elimina_tenda_vuota(): void
    {
        $campoId = $this->campoId();
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
        Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);

        $this->delete(route('struttura.destroy', $tendaId))->assertRedirect();

        $this->assertSame(0, Tenda::count());
        $this->assertSame(0, Posto::count()); // posti in cascata
    }

    public function test_non_elimina_tenda_con_posti_occupati(): void
    {
        $campoId = $this->campoId();
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
        Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'occupato']);

        $this->delete(route('struttura.destroy', $tendaId))->assertSessionHas('err');

        $this->assertSame(1, Tenda::count()); // non eliminata
    }

    private function tenda(): int
    {
        $campoId = $this->campoId();

        return DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_pagina_modifica_si_apre(): void
    {
        $tendaId = $this->tenda();
        Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);

        $this->get(route('struttura.edit', $tendaId))->assertOk()->assertSee('Dati tenda');
    }

    public function test_modifica_tenda(): void
    {
        $tendaId = $this->tenda();

        $this->patch(route('struttura.update', $tendaId), ['settore' => 'B', 'codice' => 'B-99', 'tipo' => 'servizi'])
            ->assertRedirect();

        $this->assertDatabaseHas('tende', ['id' => $tendaId, 'settore' => 'B', 'codice' => 'B-99', 'tipo' => 'servizi']);
    }

    public function test_aggiungi_posti_continua_numerazione(): void
    {
        $tendaId = $this->tenda();
        Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);

        $this->post(route('struttura.posti.add', $tendaId), ['quanti' => 3])->assertRedirect();

        $this->assertSame(4, Posto::where('tenda_id', $tendaId)->count());
        $this->assertNotNull(Posto::where('tenda_id', $tendaId)->where('numero', '4')->first());
    }

    public function test_segna_e_rimuove_inagibile(): void
    {
        $tendaId = $this->tenda();
        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);

        $this->patch(route('struttura.posti.inagibile', $posto));
        $this->assertSame('inagibile', $posto->fresh()->stato);

        $this->patch(route('struttura.posti.inagibile', $posto));
        $this->assertSame('libero', $posto->fresh()->stato);
    }

    public function test_rimuove_posto_libero(): void
    {
        $tendaId = $this->tenda();
        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);

        $this->delete(route('struttura.posti.remove', $posto))->assertRedirect();

        $this->assertDatabaseMissing('posti', ['id' => $posto->id]);
    }

    public function test_non_rimuove_posto_occupato(): void
    {
        $tendaId = $this->tenda();
        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'occupato']);

        $this->delete(route('struttura.posti.remove', $posto))->assertSessionHas('err');

        $this->assertDatabaseHas('posti', ['id' => $posto->id]);
    }
}
