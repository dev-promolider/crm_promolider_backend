<?php

namespace Tests\Feature\Preregistro;

use Tests\TestCase;
use App\Models\User;
use App\Models\Preregistro;
use App\Models\PreregistroLink;
use App\Models\UnverifiedUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;


class PreregistroStoreTest extends TestCase
{
    use DatabaseTransactions, SetupPreregistroTables;

    private const PREREGISTRO_STORE_ROUTE = 'preregistro.store';

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

        // Mockear Http para evitar llamadas reales a n8n
        Http::fake();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nombres'   => 'Juan',
            'apellidos' => 'Pérez',
            'correo'    => 'juan@ejemplo.com',
            'whatsapp'  => '999888777',
            'lado'      => 'izquierda',
        ], $overrides);
    }

    /** @test */
    public function validar_que_nombres_es_obligatorio()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload(['nombres' => ''])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombres']);
    }

    /** @test */
    public function validar_que_apellidos_es_obligatorio()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload(['apellidos' => ''])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['apellidos']);
    }

    /** @test */
    public function validar_que_correo_es_obligatorio_y_tiene_formato_valido()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload(['correo' => ''])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['correo']);

        $response2 = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload(['correo' => 'invalido'])
        );

        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['correo']);
    }

    /** @test */
    public function validar_que_whatsapp_es_obligatorio()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload(['whatsapp' => ''])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['whatsapp']);
    }

    /** @test */
    public function devolver_404_cuando_no_existe_configuracion_de_link()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'noexist']),
            $this->validPayload()
        );

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Configuración de preregistro no encontrada.',
        ]);
    }

    /** @test */
    public function redirigir_al_login_si_el_usuario_ya_existe_como_usuario_real()
    {
        User::create([
            'username'    => 'usuarioyaexiste',
            'password'    => Hash::make('secret'),
            'email'       => 'juan@ejemplo.com',
            'name'        => 'Juan',
            'last_name'   => 'Pérez',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '87654321',
        ]);

        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload()
        );

        $response->assertJson([
            'redirect_url' => url('/login'),
            'status'       => 'user_exists',
        ]);
    }

    /** @test */
    public function notificar_pago_pendiente_cuando_existe_preregistro_con_unverified_user()
    {
        $preregistro = Preregistro::create([
            'nombres'           => 'Juan',
            'apellidos'         => 'Pérez',
            'correo'            => 'juan@ejemplo.com',
            'whatsapp'          => '999888777',
            'referrer_username' => 'testuser',
            'lado'              => 'izquierda',
        ]);

        $unverified = new UnverifiedUser();
        $unverified->username = 'juanperez';
        $unverified->password = Hash::make('secret');
        $unverified->openpay_order_id = 'ord_123';
        $unverified->data = json_encode([
            'email'          => 'juan@ejemplo.com',
            'preregistro_id' => $preregistro->id,
        ]);
        $unverified->save();

        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload()
        );

        $response->assertJson([
            'status' => 'payment_pending',
        ]);

        $response->assertJsonStructure([
            'redirect_url',
            'preregistro_id',
            'username',
            'lado',
            'status',
            'preregistro_prefill',
        ]);
    }

    /** @test */
    public function notificar_ya_registrado_cuando_existe_preregistro_sin_pago_pendiente()
    {
        Preregistro::create([
            'nombres'           => 'Juan',
            'apellidos'         => 'Pérez',
            'correo'            => 'juan@ejemplo.com',
            'whatsapp'          => '999888777',
            'referrer_username' => 'testuser',
            'lado'              => 'izquierda',
        ]);

        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload()
        );

        $response->assertJson([
            'status' => 'already_registered',
        ]);
    }

    /** @test */
    public function crear_preregistro_exitosamente()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload()
        );

        $response->assertJson([
            'ok'             => true,
            'username'       => 'testuser',
            'lado'           => 'izquierda',
        ]);

        $response->assertJsonStructure([
            'ok',
            'preregistro_id',
            'redirect_url',
            'username',
            'lado',
        ]);

        $this->assertDatabaseHas('preregistros', [
            'correo'            => 'juan@ejemplo.com',
            'nombres'           => 'Juan',
            'apellidos'         => 'Pérez',
            'whatsapp'          => '999888777',
            'referrer_username' => 'testuser',
            'lado'              => 'izquierda',
        ]);
    }

    /** @test */
    public function crear_preregistro_con_lado_derecho()
    {
        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload(['lado' => 'derecha'])
        );

        $response->assertJson([
            'ok'   => true,
            'lado' => 'derecha',
        ]);

        $this->assertDatabaseHas('preregistros', [
            'correo' => 'juan@ejemplo.com',
            'lado'   => 'derecha',
        ]);
    }

    /** @test */
    public function enviar_payload_a_n8n_tras_crear_preregistro()
    {
        $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload()
        );

        Http::assertSent(function ($request) {
            return $request->url() === 'https://ia.promolider.org/webhook-test/registro-promolider';
        });
    }

    /** @test */
    public function no_llorar_si_n8n_falla()
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $response = $this->postJson(
            route(self::PREREGISTRO_STORE_ROUTE, ['username' => 'testuser']),
            $this->validPayload()
        );

        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('preregistros', [
            'correo' => 'juan@ejemplo.com',
        ]);
    }
}
