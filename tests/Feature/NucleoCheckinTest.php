<?php

namespace Tests\Feature;

use App\Actions\CheckInNucleo;
use App\Models\Nucleo;
use App\Models\Persona;
use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NucleoCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create(['ruolo' => 'operatore']));
    }

    /** @return array{campo:int, ospite:int, tenda:Tenda} */
    private function preparaTenda(int $postiLiberi): array
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $ospiteId = DB::table('categorie_persona')->insertGetId(['nome' => 'Ospite', 'attiva' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tenda = Tenda::create(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio']);
        for ($n = 1; $n <= $postiLiberi; $n++) {
            Posto::create(['tenda_id' => $tenda->id, 'numero' => (string) $n, 'stato' => 'libero']);
        }

        return ['campo' => $campoId, 'ospite' => $ospiteId, 'tenda' => $tenda];
    }

    public function test_nucleo_assegnato_a_posti_della_stessa_tenda(): void
    {
        ['campo' => $campoId, 'ospite' => $ospiteId, 'tenda' => $tenda] = $this->preparaTenda(3);

        $membri = [['cognome' => 'Rossi', 'nome' => 'Mario'], ['cognome' => 'Rossi', 'nome' => 'Lucia']];
        $nucleo = (new CheckInNucleo)($campoId, 'Rossi', $membri, $tenda, $ospiteId);

        $this->assertSame(2, Persona::where('nucleo_id', $nucleo->id)->where('stato', 'presente')->count());
        $this->assertSame(2, Posto::where('tenda_id', $tenda->id)->where('stato', 'occupato')->count());
        $this->assertSame(1, Posto::where('tenda_id', $tenda->id)->where('stato', 'libero')->count());
    }

    public function test_posti_insufficienti_annulla_tutto(): void
    {
        ['campo' => $campoId, 'ospite' => $ospiteId, 'tenda' => $tenda] = $this->preparaTenda(1);

        try {
            (new CheckInNucleo)($campoId, 'Verdi', [['cognome' => 'Verdi', 'nome' => 'A'], ['cognome' => 'Verdi', 'nome' => 'B']], $tenda, $ospiteId);
            $this->fail('doveva lanciare RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertSame(0, Nucleo::count());
            $this->assertSame(0, Persona::count());
        }
    }

    public function test_checkin_nucleo_dal_form(): void
    {
        ['ospite' => $ospiteId, 'tenda' => $tenda] = $this->preparaTenda(4);

        $this->post(route('nucleo.checkin.store'), [
            'etichetta' => 'Bianchi', 'categoria_id' => $ospiteId, 'tenda_id' => $tenda->id,
            'membri' => [
                ['cognome' => 'Bianchi', 'nome' => 'Ada'],
                ['cognome' => 'Bianchi', 'nome' => 'Bruno'],
                ['cognome' => '', 'nome' => ''], // riga vuota, ignorata
            ],
        ])->assertRedirect(route('posti.index'));

        $this->assertSame(1, Nucleo::count());
        $this->assertSame(2, Persona::where('stato', 'presente')->count());
        $this->assertSame(2, Posto::where('tenda_id', $tenda->id)->where('stato', 'occupato')->count());
    }

    public function test_form_richiede_etichetta(): void
    {
        $this->preparaTenda(2);
        $this->post(route('nucleo.checkin.store'), ['membri' => [['cognome' => 'X', 'nome' => 'Y']]])
            ->assertSessionHasErrors(['etichetta', 'tenda_id']);
    }
}
