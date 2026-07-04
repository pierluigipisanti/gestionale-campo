<?php

namespace Tests\Feature;

use App\Models\CategoriaPersona;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategorieWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'admin']));
    }

    public function test_pagina_si_apre(): void
    {
        $this->get('/categorie')->assertOk()->assertSee('Nuova categoria');
    }

    public function test_crea_categoria(): void
    {
        $this->post('/categorie', ['nome' => 'Guardia di Finanza', 'sigla' => 'GdF'])
            ->assertRedirect(route('categorie.index'));

        $this->assertDatabaseHas('categorie_persona', ['nome' => 'Guardia di Finanza', 'attiva' => true]);
    }

    public function test_nome_deve_essere_unico(): void
    {
        CategoriaPersona::create(['nome' => 'Polizia', 'attiva' => true]);

        $this->post('/categorie', ['nome' => 'Polizia'])->assertSessionHasErrors('nome');
    }

    public function test_toggle_disattiva_e_riattiva(): void
    {
        $cat = CategoriaPersona::create(['nome' => 'ENEL', 'attiva' => true]);

        $this->patch(route('categorie.toggle', $cat));
        $this->assertFalse($cat->fresh()->attiva);

        $this->patch(route('categorie.toggle', $cat));
        $this->assertTrue($cat->fresh()->attiva);
    }

    public function test_modifica_categoria(): void
    {
        $cat = CategoriaPersona::create(['nome' => 'Vigli del Fuco', 'attiva' => true]);

        $this->patch(route('categorie.update', $cat), ['nome' => 'Vigili del Fuoco', 'sigla' => 'VVF'])
            ->assertRedirect(route('categorie.index'));

        $this->assertDatabaseHas('categorie_persona', ['id' => $cat->id, 'nome' => 'Vigili del Fuoco', 'sigla' => 'VVF']);
    }

    public function test_elimina_categoria_inutilizzata(): void
    {
        $cat = CategoriaPersona::create(['nome' => 'Temporanea', 'attiva' => true]);

        $this->delete(route('categorie.destroy', $cat))->assertRedirect();

        $this->assertDatabaseMissing('categorie_persona', ['id' => $cat->id]);
    }

    public function test_non_elimina_categoria_in_uso(): void
    {
        $cat = CategoriaPersona::create(['nome' => 'Ospite', 'attiva' => true]);
        $enteId = DB::table('enti')->insertGetId(['nome' => 'E', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'C', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        Persona::create(['campo_id' => $campoId, 'categoria_id' => $cat->id, 'cognome' => 'X', 'nome' => 'Y', 'stato' => 'presente']);

        $this->delete(route('categorie.destroy', $cat))->assertSessionHas('err');
        $this->assertDatabaseHas('categorie_persona', ['id' => $cat->id]);
    }

    public function test_operatore_non_accede(): void
    {
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));
        $this->get('/categorie')->assertForbidden();
    }
}
