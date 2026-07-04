<?php

namespace Tests\Feature;

use App\Models\Logo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoghiWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['ruolo' => 'admin']));

        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('campi')->insert(['ente_id' => $enteId, 'nome' => 'Campo', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_pagina_si_apre(): void
    {
        $this->get('/loghi')->assertOk()->assertSee('Carica logo');
    }

    public function test_carica_logo(): void
    {
        Storage::fake('public');

        $this->post('/loghi', [
            'etichetta' => 'Comune di Esempio',
            'file' => UploadedFile::fake()->create('logo.png', 50, 'image/png'),
        ])->assertRedirect(route('loghi.index'));

        $this->assertDatabaseHas('loghi', ['etichetta' => 'Comune di Esempio']);
        Storage::disk('public')->assertExists(Logo::first()->path);
    }

    public function test_file_deve_essere_immagine(): void
    {
        Storage::fake('public');

        $this->post('/loghi', [
            'etichetta' => 'X',
            'file' => UploadedFile::fake()->create('malware.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('file');
    }

    public function test_rinomina_logo(): void
    {
        Storage::fake('public');
        $this->post('/loghi', ['etichetta' => 'Comune', 'file' => UploadedFile::fake()->create('l.png', 20, 'image/png')]);
        $logo = Logo::first();

        $this->patch(route('loghi.update', $logo), ['etichetta' => 'Comune di Rivalta'])->assertRedirect();

        $this->assertDatabaseHas('loghi', ['id' => $logo->id, 'etichetta' => 'Comune di Rivalta']);
    }

    public function test_aggiorna_stampe_e_ordine(): void
    {
        Storage::fake('public');
        $this->post('/loghi', ['etichetta' => 'X', 'file' => UploadedFile::fake()->create('l.png', 20, 'image/png')]);
        $logo = Logo::first();

        // senza checkbox 'stampe' → false, con ordine
        $this->patch(route('loghi.update', $logo), ['etichetta' => 'X', 'ordine' => 3]);
        $logo->refresh();
        $this->assertFalse($logo->stampe);
        $this->assertSame(3, $logo->ordine);

        $this->patch(route('loghi.update', $logo), ['etichetta' => 'X', 'stampe' => '1', 'ordine' => 1]);
        $this->assertTrue($logo->fresh()->stampe);
    }

    public function test_elimina_logo(): void
    {
        Storage::fake('public');
        $this->post('/loghi', ['etichetta' => 'Logo', 'file' => UploadedFile::fake()->create('l.png', 20, 'image/png')]);
        $logo = Logo::first();

        $this->delete(route('loghi.destroy', $logo))->assertRedirect();

        $this->assertDatabaseMissing('loghi', ['id' => $logo->id]);
        Storage::disk('public')->assertMissing($logo->path);
    }

    public function test_operatore_non_accede(): void
    {
        $this->actingAs(User::factory()->create(['ruolo' => 'operatore']));
        $this->get('/loghi')->assertForbidden();
    }
}
