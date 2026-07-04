<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Persona, 1: Posto} */
    private function campoConPostoLibero(): array
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'codice' => 'A-01', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);

        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);
        $persona = Persona::create(['campo_id' => $campoId, 'cognome' => 'Rossi', 'nome' => 'Mario', 'stato' => 'pre_registrato']);

        return [$persona, $posto];
    }

    public function test_checkin_rende_presente_occupa_posto_e_registra_documento(): void
    {
        [$persona, $posto] = $this->campoConPostoLibero();

        $mov = (new EseguiCheckIn)($persona, $posto, documento: ['tipo' => 'cie', 'numero' => 'CA12345']);

        $this->assertSame('checkin', $mov->tipo);
        $this->assertSame($posto->id, $mov->posto_a_id);

        $persona->refresh();
        $this->assertSame('presente', $persona->stato);
        $this->assertSame($posto->id, $persona->posto_id);
        $this->assertNotNull($persona->ultimo_movimento_at);
        $this->assertSame('cie', $persona->documento_tipo);
        $this->assertSame('CA12345', $persona->documento_numero);

        $this->assertSame('occupato', $posto->fresh()->stato);
    }

    public function test_checkin_su_posto_non_libero_fallisce_e_non_cambia_nulla(): void
    {
        [$persona, $posto] = $this->campoConPostoLibero();
        $posto->update(['stato' => 'occupato']);

        $this->expectException(\RuntimeException::class);
        try {
            (new EseguiCheckIn)($persona, $posto);
        } finally {
            $this->assertSame('pre_registrato', $persona->fresh()->stato);
            $this->assertSame(0, DB::table('movimenti')->count());
        }
    }

    public function test_persona_gia_presente_non_viene_richeckinata(): void
    {
        [$persona, $posto] = $this->campoConPostoLibero();
        $persona->update(['stato' => 'presente']);

        $this->expectException(\RuntimeException::class);
        (new EseguiCheckIn)($persona, $posto);
    }
}
