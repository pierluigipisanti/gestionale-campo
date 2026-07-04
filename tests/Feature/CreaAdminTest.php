<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreaAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_admin(): void
    {
        $this->artisan('crea:admin')
            ->expectsQuestion('Email', 'capo@campo.local')
            ->expectsQuestion('Password (min 6 caratteri)', 'segreta1')
            ->expectsQuestion('Nome', 'Capo')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'capo@campo.local', 'ruolo' => 'admin']);
    }

    public function test_crea_admin_rifiuta_password_corta(): void
    {
        $this->artisan('crea:admin')
            ->expectsQuestion('Email', 'capo@campo.local')
            ->expectsQuestion('Password (min 6 caratteri)', '123')
            ->expectsQuestion('Nome', 'Capo')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'capo@campo.local']);
    }
}
