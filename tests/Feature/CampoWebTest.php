<?php

namespace Tests\Feature;

use App\Models\Campo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CampoWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'admin']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'Comune X', 'tipo' => 'comune', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('campi')->insert(['ente_id' => $enteId, 'nome' => 'Campo 1', 'comune' => 'X', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_pagina_si_apre(): void
    {
        $this->get('/campo')->assertOk()->assertSee('Nome campo');
    }

    public function test_modifica_nome_campo_ed_ente(): void
    {
        $this->patch('/campo', [
            'ente_nome' => 'Comune di Rivalta', 'ente_tipo' => 'comune',
            'campo_nome' => 'Campo Palasport', 'campo_comune' => 'Rivalta',
        ])->assertRedirect(route('campo.edit'));

        $this->assertDatabaseHas('campi', ['nome' => 'Campo Palasport', 'comune' => 'Rivalta']);
        $this->assertDatabaseHas('enti', ['nome' => 'Comune di Rivalta']);
    }

    public function test_nome_campo_obbligatorio(): void
    {
        $this->patch('/campo', ['ente_nome' => 'X', 'campo_nome' => ''])
            ->assertSessionHasErrors('campo_nome');
    }

    public function test_operatore_non_accede(): void
    {
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));
        $this->get('/campo')->assertForbidden();
    }
}
