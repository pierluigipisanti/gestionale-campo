<?php

namespace Tests\Feature;

use App\Actions\EseguiCheckIn;
use App\Models\Persona;
use App\Models\Posto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostiWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create(['ruolo' => 'operatore']));
    }

    /** @return array{campo:int, posto:Posto, ospite:int} */
    private function setupCampo(): array
    {
        $enteId = DB::table('enti')->insertGetId(['nome' => 'Ente', 'created_at' => now(), 'updated_at' => now()]);
        $campoId = DB::table('campi')->insertGetId(['ente_id' => $enteId, 'nome' => 'Campo Test', 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $tendaId = DB::table('tende')->insertGetId(['campo_id' => $campoId, 'settore' => 'A', 'fila' => '1', 'codice' => 'A-101', 'tipo' => 'alloggio', 'created_at' => now(), 'updated_at' => now()]);
        $ospiteId = DB::table('categorie_persona')->insertGetId(['nome' => 'Ospite', 'attiva' => true, 'created_at' => now(), 'updated_at' => now()]);
        $posto = Posto::create(['tenda_id' => $tendaId, 'numero' => '1', 'stato' => 'libero']);

        return ['campo' => $campoId, 'posto' => $posto, 'ospite' => $ospiteId];
    }

    public function test_griglia_posti_si_apre(): void
    {
        $this->setupCampo();
        $this->get('/posti')->assertOk()->assertSee('occupati')->assertSee('A-101');
    }

    public function test_checkin_dal_posto_crea_persona_e_occupa(): void
    {
        ['posto' => $posto, 'ospite' => $ospiteId] = $this->setupCampo();

        $this->post(route('posti.checkin.store', $posto), [
            'cognome' => 'Rossi', 'nome' => 'Mario', 'categoria_id' => $ospiteId,
            'documento_tipo' => 'cie', 'documento_numero' => 'CA999',
        ])->assertRedirect(route('posti.index'));

        $this->assertDatabaseHas('persone', ['cognome' => 'Rossi', 'stato' => 'presente', 'posto_id' => $posto->id]);
        $this->assertSame('occupato', $posto->fresh()->stato);
    }

    public function test_form_checkin_si_apre(): void
    {
        ['posto' => $posto] = $this->setupCampo();
        $this->get(route('posti.checkin.form', $posto))->assertOk()->assertSee('Cerca la persona per cognome');
    }

    public function test_checkin_salva_sesso_e_documento(): void
    {
        ['posto' => $posto, 'ospite' => $ospiteId] = $this->setupCampo();

        $this->post(route('posti.checkin.store', $posto), [
            'cognome' => 'Neri', 'nome' => 'Lia', 'categoria_id' => $ospiteId,
            'sesso' => 'F', 'codice_fiscale' => 'NRELIA90A41H501Z',
        ])->assertRedirect(route('posti.index'));

        $this->assertDatabaseHas('persone', ['cognome' => 'Neri', 'sesso' => 'F', 'codice_fiscale' => 'NRELIA90A41H501Z']);
    }

    public function test_checkin_riusa_preregistrato_per_cf(): void
    {
        ['campo' => $campoId, 'posto' => $posto, 'ospite' => $ospiteId] = $this->setupCampo();
        // volontario importato (pre-registrato) con CF
        $pre = Persona::create(['campo_id' => $campoId, 'cognome' => 'Vol', 'nome' => 'Ada', 'codice_fiscale' => 'VLNRSS80A41H501U', 'stato' => 'pre_registrato']);

        $this->post(route('posti.checkin.store', $posto), [
            'cognome' => 'Vol', 'nome' => 'Ada', 'categoria_id' => $ospiteId, 'codice_fiscale' => 'VLNRSS80A41H501U',
        ])->assertRedirect(route('posti.index'));

        // nessun duplicato: la stessa persona ora è presente sul posto
        $this->assertSame(1, Persona::where('codice_fiscale', 'VLNRSS80A41H501U')->count());
        $this->assertSame('presente', $pre->fresh()->stato);
        $this->assertSame($posto->id, $pre->fresh()->posto_id);
    }

    public function test_ricerca_anagrafica_per_cognome(): void
    {
        ['campo' => $campoId] = $this->setupCampo();
        Persona::create(['campo_id' => $campoId, 'cognome' => 'Ferri', 'nome' => 'Luca', 'stato' => 'pre_registrato']);

        $this->getJson(route('anagrafica.cerca', ['q' => 'Fer']))->assertOk()->assertJsonFragment(['cognome' => 'Ferri']);
    }

    public function test_checkin_riusa_per_persona_id(): void
    {
        ['campo' => $campoId, 'posto' => $posto, 'ospite' => $ospiteId] = $this->setupCampo();
        $pre = Persona::create(['campo_id' => $campoId, 'cognome' => 'Ferri', 'nome' => 'Luca', 'stato' => 'pre_registrato']);

        $this->post(route('posti.checkin.store', $posto), [
            'persona_id' => $pre->id, 'cognome' => 'Ferri', 'nome' => 'Luca', 'categoria_id' => $ospiteId,
        ])->assertRedirect(route('posti.index'));

        $this->assertSame(1, Persona::where('cognome', 'Ferri')->count());
        $this->assertSame('presente', $pre->fresh()->stato);
        $this->assertSame($posto->id, $pre->fresh()->posto_id);
    }

    public function test_lookup_cf_riconosce_preregistrato(): void
    {
        ['campo' => $campoId] = $this->setupCampo();
        Persona::create(['campo_id' => $campoId, 'cognome' => 'Bianchi', 'nome' => 'Ivo', 'codice_fiscale' => 'BNCHIV80A01H501U', 'stato' => 'pre_registrato']);

        $this->getJson(route('anagrafica.lookup', ['cf' => 'BNCHIV80A01H501U']))
            ->assertOk()->assertJson(['found' => true, 'cognome' => 'Bianchi']);
    }

    public function test_checkin_richiede_cognome_e_nome(): void
    {
        ['posto' => $posto] = $this->setupCampo();
        $this->post(route('posti.checkin.store', $posto), [])->assertSessionHasErrors(['cognome', 'nome']);
    }

    public function test_form_checkin_su_posto_occupato_reindirizza(): void
    {
        ['posto' => $posto] = $this->setupCampo();
        $posto->update(['stato' => 'occupato']);
        $this->get(route('posti.checkin.form', $posto))->assertRedirect(route('posti.index'));
    }

    public function test_checkout_dal_posto_libera_e_dimette(): void
    {
        ['campo' => $campoId, 'posto' => $posto] = $this->setupCampo();
        $persona = Persona::create(['campo_id' => $campoId, 'cognome' => 'Verdi', 'nome' => 'Ada', 'stato' => 'pre_registrato']);
        (new EseguiCheckIn)($persona, $posto);

        $this->post(route('posti.checkout', $posto))->assertRedirect(route('posti.index'));

        $this->assertSame('libero', $posto->fresh()->stato);
        $this->assertSame('dimesso', $persona->fresh()->stato);
    }
}
