<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Actions\EseguiCheckOut;
use App\Actions\EseguiTrasferimento;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MovimentiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Persona già presente nel posto A, con un posto B libero a disposizione.
     * @return array{0: Persona, 1: Posto, 2: Posto}
     */
    private function personaPresente(): array
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);

        $postoA = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $postoB = Posto::create(['tenda_id' => $tendaId, 'numero' => '2', 'stato' => 'libero']);
        $persona = Persona::create(['campo_id' => $campoId, 'cognome' => 'Rossi', 'nome' => 'Mario', 'stato' => 'pre_registrato']);

        (new EseguiCheckIn)($persona, $postoA);

        return [$persona->fresh(), $postoA->fresh(), $postoB->fresh()];
    }

    public function test_checkout_libera_posto_e_dimette(): void
    {
        [$persona, $postoA] = $this->personaPresente();

        $mov = (new EseguiCheckOut)($persona);

        $this->assertSame('checkout', $mov->tipo);
        $this->assertSame($postoA->id, $mov->posto_da_id);

        $persona->refresh();
        $this->assertSame('dimesso', $persona->stato);
        $this->assertNull($persona->posto_id);
        $this->assertSame('libero', $postoA->fresh()->stato);
    }

    public function test_checkout_di_non_presente_fallisce(): void
    {
        [$persona] = $this->personaPresente();
        (new EseguiCheckOut)($persona); // ora è dimesso

        $this->expectException(\RuntimeException::class);
        (new EseguiCheckOut)($persona->fresh());
    }

    public function test_trasferimento_sposta_persona_e_aggiorna_posti(): void
    {
        [$persona, $postoA, $postoB] = $this->personaPresente();

        $mov = (new EseguiTrasferimento)($persona, $postoB);

        $this->assertSame('trasferimento', $mov->tipo);
        $this->assertSame($postoA->id, $mov->posto_da_id);
        $this->assertSame($postoB->id, $mov->posto_a_id);

        $persona->refresh();
        $this->assertSame('presente', $persona->stato);
        $this->assertSame($postoB->id, $persona->posto_id);
        $this->assertSame('libero', $postoA->fresh()->stato);
        $this->assertSame('occupato', $postoB->fresh()->stato);
    }

    public function test_trasferimento_su_posto_occupato_fallisce(): void
    {
        [$persona, , $postoB] = $this->personaPresente();
        $postoB->update(['stato' => 'occupato']);

        $this->expectException(\RuntimeException::class);
        (new EseguiTrasferimento)($persona, $postoB);
    }
}
