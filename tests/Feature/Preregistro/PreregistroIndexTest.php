<?php

namespace Tests\Feature\Preregistro;

use Tests\TestCase;
use App\Models\User;
use App\Models\PreregistroLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class PreregistroIndexTest extends TestCase
{
    use DatabaseTransactions, SetupPreregistroTables;

    private const PREREGISTRO_ROUTE = 'preregistro.index';

    private User $user;
    private PreregistroLink $linkConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPreregistroTables();

        $this->user = User::create([
            'username'    => 'testuser',
            'password'    => Hash::make('secret'),
            'email'       => 'referidor@ejemplo.com',
            'name'        => 'Carlos',
            'last_name'   => 'García',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '12345678',
        ]);

        $this->linkConfig = PreregistroLink::create([
            'username' => 'testuser',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);
    }

    /** @test */
    public function devolver_404_cuando_no_existe_configuracion_de_link()
    {
        $response = $this->get(route(self::PREREGISTRO_ROUTE, ['username' => 'noexist']));

        $response->assertStatus(404);
    }

    /** @test */
    public function devolver_404_cuando_no_existe_usuario_referidor()
    {
        PreregistroLink::create([
            'username' => 'usuario_sin_user',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);

        $response = $this->get(route(self::PREREGISTRO_ROUTE, ['username' => 'usuario_sin_user']));

        $response->assertStatus(404);
    }

    /** @test */
    public function mostrar_vista_correcta_cuando_todo_es_valido()
    {
        $response = $this->get(route(self::PREREGISTRO_ROUTE, ['username' => 'testuser']));

        $response->assertStatus(200);

        $response->assertViewHas('username', 'testuser');
        $response->assertViewHas('lado', 'izquierda');
        $response->assertViewHas('landing', 'claro');
        $response->assertViewHas('nombre_referidor', $this->user->name);
        $response->assertViewHas('apellido_referidor', $this->user->last_name);
        $response->assertViewHas('correo_referidor', $this->user->email);
        $response->assertViewHas('telefono_referidor', $this->user->phone);
    }

    /** @test */
    public function usar_tema_desde_query_param_si_es_valido()
    {
        $response = $this->get(route(self::PREREGISTRO_ROUTE, [
            'username' => 'testuser',
            'tema'     => 'oscuro',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('landing', 'oscuro');
    }

    /** @test */
    public function usar_tema_de_configuracion_cuando_query_param_es_invalido()
    {
        $response = $this->get(route(self::PREREGISTRO_ROUTE, [
            'username' => 'testuser',
            'tema'     => 'invalido',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('landing', 'claro');
    }

    /** @test */
    public function mostrar_vista_oscura_cuando_la_configuracion_es_oscuro()
    {
        $this->linkConfig->update(['landing' => 'oscuro']);

        $response = $this->get(route(self::PREREGISTRO_ROUTE, ['username' => 'testuser']));

        $response->assertStatus(200);
        $response->assertViewHas('landing', 'oscuro');
    }
}
