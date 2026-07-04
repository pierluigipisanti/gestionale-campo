<?php

namespace Tests\Feature;

use App\Actions\CreaTende;
use App\Models\Posto;
use App\Models\Tenda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreaTendeTest extends TestCase
{
    use RefreshDatabase;

    private function campoId(): int
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        return DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_crea_tende_con_i_posti(): void
    {
        $campoId = $this->campoId();

        $tende = (new CreaTende)($campoId, 'A', null, 2, 4);

        $this->assertCount(2, $tende);
        $this->assertSame(2, Tenda::count());
        $this->assertSame(8, Posto::count());
        $this->assertEqualsCanonicalizing(['A-01', 'A-02'], Tenda::pluck('codice')->all());
        $this->assertSame(4, Posto::where('tenda_id', $tende[0]->id)->count());
    }

    public function test_numerazione_continua_tra_chiamate(): void
    {
        $campoId = $this->campoId();

        (new CreaTende)($campoId, 'A', '1', 2, 6);
        (new CreaTende)($campoId, 'A', '1', 1, 6);

        // stesso settore+fila → codici continui, nessun duplicato
        $this->assertEqualsCanonicalizing(['A1-01', 'A1-02', 'A1-03'], Tenda::pluck('codice')->all());
    }
}
