<?php

namespace Tests\Feature;

use App\Models\Automezzo;
use App\Models\TransitoAutomezzo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutomezziTest extends TestCase
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

    private function auto(string $stato = 'fuori'): Automezzo
    {
        return Automezzo::create(['campo_id' => $this->campoId, 'targa' => 'AB123CD', 'tipo' => 'furgone', 'stato' => $stato]);
    }

    public function test_registro_crea_automezzo(): void
    {
        $this->post(route('automezzi.store'), ['targa' => 'ab 123 cd', 'tipo' => 'furgone', 'referente' => 'Rossi', 'telefono' => '333'])
            ->assertRedirect(route('automezzi.index'));

        // targa normalizzata in maiuscolo
        $this->assertDatabaseHas('automezzi', ['targa' => 'AB 123 CD', 'referente' => 'Rossi']);
    }

    public function test_targa_unica_nel_campo(): void
    {
        $this->auto();
        $this->post(route('automezzi.store'), ['targa' => 'AB123CD'])->assertSessionHasErrors('targa');
    }

    public function test_pagine_si_aprono(): void
    {
        $this->auto();
        $this->get(route('varco.automezzi'))->assertOk()->assertSee('Registra ENTRATA');
        $this->get(route('automezzi.index'))->assertOk()->assertSee('Registro automezzi');
    }

    public function test_entrata_e_uscita(): void
    {
        $a = $this->auto();

        $this->post(route('automezzi.entrata'), ['targa' => 'AB123CD'])->assertRedirect(route('varco.automezzi'));
        $this->assertSame('dentro', $a->fresh()->stato);
        $this->assertDatabaseHas('transiti_automezzo', ['automezzo_id' => $a->id, 'uscita_at' => null]);

        $this->post(route('automezzi.uscita'), ['targa' => 'AB123CD'])->assertRedirect(route('varco.automezzi'));
        $this->assertSame('fuori', $a->fresh()->stato);
        $this->assertSame(0, TransitoAutomezzo::where('automezzo_id', $a->id)->whereNull('uscita_at')->count());
    }

    public function test_entrata_crea_automezzo_al_volo(): void
    {
        $this->post(route('automezzi.entrata'), ['targa' => 'nuova99', 'tipo' => 'Auto', 'referente' => 'Bianchi'])
            ->assertRedirect(route('varco.automezzi'));

        // targa normalizzata, creato e già dentro
        $this->assertDatabaseHas('automezzi', ['targa' => 'NUOVA99', 'referente' => 'Bianchi', 'stato' => 'dentro']);
    }

    public function test_non_entra_due_volte(): void
    {
        $this->auto();
        $this->post(route('automezzi.entrata'), ['targa' => 'AB123CD']);
        $this->post(route('automezzi.entrata'), ['targa' => 'AB123CD'])->assertSessionHas('err');

        $this->assertSame(1, TransitoAutomezzo::whereHas('automezzo', fn ($q) => $q->where('targa', 'AB123CD'))->count());
    }

    public function test_non_esce_se_gia_fuori(): void
    {
        $a = $this->auto(); // fuori
        $this->post(route('automezzi.uscita'), ['targa' => 'AB123CD'])->assertSessionHas('err');
        $this->assertSame('fuori', $a->fresh()->stato);
    }

    public function test_non_elimina_se_dentro(): void
    {
        $a = $this->auto('dentro');
        $this->delete(route('automezzi.destroy', $a))->assertSessionHas('err');
        $this->assertDatabaseHas('automezzi', ['id' => $a->id]);
    }

    public function test_elimina_automezzo_fuori(): void
    {
        $a = $this->auto();
        $this->delete(route('automezzi.destroy', $a))->assertRedirect();
        $this->assertDatabaseMissing('automezzi', ['id' => $a->id]);
    }
}
