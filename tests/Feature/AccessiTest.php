<?php

namespace Tests\Feature;

use App\Actions\ChiudiUscita;
use App\Actions\RegistraEntrata;
use App\Models\Accesso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessiTest extends TestCase
{
    use RefreshDatabase;

    private function campoId(): int
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_registra_entrata_crea_accesso_aperto(): void
    {
        $campoId = $this->campoId();

        $accesso = (new RegistraEntrata)($campoId, [
            'cognome' => 'Rossi', 'nome' => 'Mario',
            'ente_appartenenza' => 'Vigili del Fuoco', 'targa_veicolo' => 'AB123CD',
        ]);

        $this->assertSame('Rossi', $accesso->cognome);
        $this->assertSame('Mario', $accesso->nome);
        $this->assertSame('Vigili del Fuoco', $accesso->ente_appartenenza);
        $this->assertNotNull($accesso->entrata_at);
        $this->assertNull($accesso->uscita_at);
    }

    public function test_chiudi_uscita_imposta_uscita(): void
    {
        $accesso = (new RegistraEntrata)($this->campoId(), ['cognome' => 'Tecnico', 'nome' => 'ENEL']);

        $chiuso = (new ChiudiUscita)($accesso);

        $this->assertNotNull($chiuso->uscita_at);
    }

    public function test_chiudi_uscita_gia_chiusa_fallisce(): void
    {
        $accesso = (new RegistraEntrata)($this->campoId(), ['cognome' => 'Fornitore']);
        (new ChiudiUscita)($accesso);

        $this->expectException(\RuntimeException::class);
        (new ChiudiUscita)($accesso);
    }

    public function test_scope_dentro_conta_solo_gli_aperti(): void
    {
        $campoId = $this->campoId();
        $a = (new RegistraEntrata)($campoId, ['cognome' => 'Uno']);
        (new RegistraEntrata)($campoId, ['cognome' => 'Due']);
        (new ChiudiUscita)($a);

        $this->assertSame(2, Accesso::count());
        $this->assertSame(1, Accesso::dentro()->count());
    }
}
