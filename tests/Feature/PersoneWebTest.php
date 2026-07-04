<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersoneWebTest extends TestCase
{
    use RefreshDatabase;

    private int $campoId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $this->campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function persona(string $stato = 'pre_registrato'): Persona
    {
        return Persona::create(['campo_id' => $this->campoId, 'cognome' => 'Rossi', 'nome' => 'Mario', 'stato' => $stato]);
    }

    public function test_modifica_scheda(): void
    {
        $p = $this->persona();

        $this->get(route('persone.edit', $p))->assertOk()->assertSee('Scheda persona');
        $this->patch(route('persone.update', $p), ['cognome' => 'Rossini', 'nome' => 'Mario', 'codice_fiscale' => 'RSSMRA80A01H501U'])
            ->assertRedirect(route('persone.edit', $p));

        $this->assertDatabaseHas('persone', ['id' => $p->id, 'cognome' => 'Rossini', 'codice_fiscale' => 'RSSMRA80A01H501U']);
    }

    public function test_elimina_persona_senza_movimenti(): void
    {
        $p = $this->persona();

        $this->delete(route('persone.destroy', $p))->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('persone', ['id' => $p->id]);
    }

    public function test_non_elimina_persona_con_movimenti(): void
    {
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $this->campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $p = $this->persona();
        (new EseguiCheckIn)($p, $posto);

        $this->delete(route('persone.destroy', $p))->assertSessionHas('err');
        $this->assertDatabaseHas('persone', ['id' => $p->id]);
    }
}
