<?php

namespace Tests\Feature;

use App\Actions\RegistraEntrata;
use App\Models\Accesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessiWebTest extends TestCase
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

    public function test_modifica_accesso(): void
    {
        $a = (new RegistraEntrata)($this->campoId, ['cognome' => 'Bianci', 'nome' => 'Ada']);

        $this->get(route('accessi.edit', $a))->assertOk();
        $this->patch(route('accessi.update', $a), ['cognome' => 'Bianchi', 'nome' => 'Ada', 'ente_appartenenza' => 'VVF'])
            ->assertRedirect(route('varco.index'));

        $this->assertDatabaseHas('accessi', ['id' => $a->id, 'cognome' => 'Bianchi', 'ente_appartenenza' => 'VVF']);
    }

    public function test_elimina_accesso(): void
    {
        $a = (new RegistraEntrata)($this->campoId, ['cognome' => 'Errore']);

        $this->delete(route('accessi.destroy', $a))->assertRedirect();

        $this->assertDatabaseMissing('accessi', ['id' => $a->id]);
    }
}
