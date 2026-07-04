<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UtentiWebTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['ruolo' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_crea_utente(): void
    {
        $this->post('/utenti', ['name' => 'Op', 'email' => 'op@campo.local', 'password' => 'segretissima', 'ruolo' => 'operatore'])
            ->assertRedirect(route('utenti.index'));
        $this->assertDatabaseHas('users', ['email' => 'op@campo.local', 'ruolo' => 'operatore']);
    }

    public function test_modifica_utente(): void
    {
        $u = User::factory()->create(['ruolo' => 'operatore']);

        $this->get(route('utenti.edit', $u))->assertOk();
        $this->patch(route('utenti.update', $u), ['name' => 'Nuovo Nome', 'email' => $u->email, 'ruolo' => 'admin'])
            ->assertRedirect(route('utenti.index'));

        $this->assertDatabaseHas('users', ['id' => $u->id, 'name' => 'Nuovo Nome', 'ruolo' => 'admin']);
    }

    public function test_cambio_password(): void
    {
        $u = User::factory()->create(['ruolo' => 'operatore']);

        $this->patch(route('utenti.update', $u), ['name' => $u->name, 'email' => $u->email, 'ruolo' => 'operatore', 'password' => 'passwordnuova']);

        $this->assertTrue(Auth::validate(['email' => $u->email, 'password' => 'passwordnuova']));
    }

    public function test_elimina_altro_utente(): void
    {
        $u = User::factory()->create(['ruolo' => 'operatore']);

        $this->delete(route('utenti.destroy', $u))->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $u->id]);
    }

    public function test_non_elimina_se_stesso(): void
    {
        $this->delete(route('utenti.destroy', $this->admin))->assertSessionHas('err');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_non_declassa_ultimo_admin(): void
    {
        // $this->admin è l'unico admin
        $this->patch(route('utenti.update', $this->admin), ['name' => $this->admin->name, 'email' => $this->admin->email, 'ruolo' => 'operatore'])
            ->assertSessionHas('err');

        $this->assertSame('admin', $this->admin->fresh()->ruolo);
    }
}
