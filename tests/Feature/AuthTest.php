<?php

namespace Tests\Feature;

use App\Models\Tenda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_ospite_reindirizzato_al_login(): void
    {
        $this->get('/varco')->assertRedirect('/login');
    }

    public function test_login_con_credenziali_valide(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('varco.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_con_credenziali_errate(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'sbagliata'])
            ->assertSessionHas('err');

        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_utenti_vietato_a_operatore(): void
    {
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));
        $this->get('/utenti')->assertForbidden();
    }

    public function test_admin_apre_e_crea_utenti(): void
    {
        $this->actingAs(User::factory()->create(['ruolo' => 'admin']));

        $this->get('/utenti')->assertOk();

        $this->post('/utenti', [
            'name' => 'Nuovo Operatore', 'email' => 'nuovo@campo.local',
            'password' => 'segretissima', 'ruolo' => 'operatore',
        ])->assertRedirect(route('utenti.index'));

        $this->assertDatabaseHas('users', ['email' => 'nuovo@campo.local', 'ruolo' => 'operatore']);
    }

    public function test_operatore_non_elimina_tenda(): void
    {
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'E', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'C', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tenda = Tenda::create(['campo_id' => $campoId, 'settore' => 'A', 'codice' => 'A-01', 'tipo' => 'alloggio']);

        $this->delete(route('struttura.destroy', $tenda))->assertForbidden();
        $this->assertSame(1, Tenda::count());
    }
}
