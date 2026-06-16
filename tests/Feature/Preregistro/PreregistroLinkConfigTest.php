<?php

namespace Tests\Feature\Preregistro;

use Tests\TestCase;
use App\Models\User;
use App\Models\PreregistroLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class PreregistroLinkConfigTest extends TestCase
{
    use DatabaseTransactions, SetupPreregistroTables;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPreregistroTables();

        $this->user = User::create([
            'username'    => 'testuser',
            'password'    => Hash::make('secret'),
            'email'       => 'test@ejemplo.com',
            'name'        => 'Carlos',
            'last_name'   => 'García',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '12345678',
        ]);
    }

    /** @test */
    public function generate_link_crea_configuracion_si_no_existe()
    {
        $response = $this->postJson('/preregistro/generate-link', [
            'username' => 'testuser',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'url',
            'config' => ['lado', 'landing'],
        ]);

        $this->assertDatabaseHas('preregistro_links', [
            'username' => 'testuser',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);
    }

    /** @test */
    public function generate_link_devuelve_configuracion_existente()
    {
        PreregistroLink::create([
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);

        $response = $this->postJson('/preregistro/generate-link', [
            'username' => 'testuser',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'config' => [
                'lado'    => 'derecha',
                'landing' => 'oscuro',
            ],
        ]);
    }

    /** @test */
    public function generate_link_valida_que_username_exista_en_users()
    {
        $response = $this->postJson('/preregistro/generate-link', [
            'username' => 'usuario_inexistente',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function generate_link_requiere_username()
    {
        $response = $this->postJson('/preregistro/generate-link', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function save_config_crea_configuracion_si_no_existe()
    {
        $response = $this->postJson('/preregistro/save-config', [
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('preregistro_links', [
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);
    }

    /** @test */
    public function save_config_actualiza_configuracion_existente()
    {
        PreregistroLink::create([
            'username' => 'testuser',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);

        $response = $this->postJson('/preregistro/save-config', [
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('preregistro_links', [
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);

        $this->assertDatabaseCount('preregistro_links', 1);
    }

    /** @test */
    public function save_config_valida_campos_requeridos()
    {
        $response = $this->postJson('/preregistro/save-config', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username', 'lado', 'landing']);
    }

    /** @test */
    public function save_config_valida_valores_permitidos_para_lado()
    {
        $response = $this->postJson('/preregistro/save-config', [
            'username' => 'testuser',
            'lado'     => 'invalido',
            'landing'  => 'claro',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lado']);
    }

    /** @test */
    public function save_config_valida_valores_permitidos_para_landing()
    {
        $response = $this->postJson('/preregistro/save-config', [
            'username' => 'testuser',
            'lado'     => 'izquierda',
            'landing'  => 'invalido',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['landing']);
    }

    /** @test */
    public function get_config_devuelve_configuracion_existente()
    {
        PreregistroLink::create([
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);

        $response = $this->getJson('/preregistro/config/testuser');

        $response->assertStatus(200);
        $response->assertJson([
            'username' => 'testuser',
            'lado'     => 'derecha',
            'landing'  => 'oscuro',
        ]);
    }

    /** @test */
    public function get_config_crea_configuracion_por_defecto_si_no_existe()
    {
        $response = $this->getJson('/preregistro/config/testuser');

        $response->assertStatus(200);
        $response->assertJson([
            'username' => 'testuser',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);

        $this->assertDatabaseHas('preregistro_links', [
            'username' => 'testuser',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);
    }
}
