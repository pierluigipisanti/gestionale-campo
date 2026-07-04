<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrasferimentoWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create(['ruolo' => 'operatore']));
    }

    public function test_trasferimento_dal_dettaglio_posto(): void
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
        $postoA = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $postoB = Posto::create(['tenda_id' => $tendaId, 'numero' => '2', 'stato' => 'libero']);

        $persona = Persona::create(['campo_id' => $campoId, 'cognome' => 'Rossi', 'nome' => 'Mario', 'stato' => 'pre_registrato']);
        (new EseguiCheckIn)($persona, $postoA);

        $this->post(route('posti.trasferisci', $postoA), ['nuovo_posto_id' => $postoB->id])
            ->assertRedirect(route('posti.show', $postoB));

        $this->assertSame($postoB->id, $persona->fresh()->posto_id);
        $this->assertSame('libero', $postoA->fresh()->stato);
        $this->assertSame('occupato', $postoB->fresh()->stato);
    }
}
