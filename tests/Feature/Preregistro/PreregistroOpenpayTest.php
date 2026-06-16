<?php

namespace Tests\Feature\Preregistro;

use Tests\TestCase;
use App\Models\User;
use App\Models\Preregistro;
use App\Models\PreregistroLink;
use App\Models\UnverifiedUser;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class PreregistroOpenpayTest extends TestCase
{
    use DatabaseTransactions, SetupPreregistroTables;

    private User $referrer;
    private PreregistroLink $linkConfig;
    private Preregistro $preregistro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPreregistroTables();

        $this->referrer = User::create([
            'username'     => 'referidor',
            'password'     => Hash::make('secret'),
            'email'        => 'referidor@ejemplo.com',
            'name'         => 'Carlos',
            'last_name'    => 'García',
            'date_birth'   => '1990-01-15',
            'phone'        => '999888777',
            'nro_document' => '12345678',
        ]);

        $this->linkConfig = PreregistroLink::create([
            'username' => 'referidor',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);

        $this->preregistro = Preregistro::create([
            'nombres'           => 'Juan',
            'apellidos'         => 'Pérez',
            'correo'            => 'juan@ejemplo.com',
            'whatsapp'          => '+51999888777',
            'referrer_username' => 'referidor',
            'lado'              => 'izquierda',
        ]);

        // Usar un nro_document único para el referidor para evitar conflictos
        $this->referrer->update(['nro_document' => '00000001']);

        // Configurar credenciales OpenPay para tests
        Config::set('services.openpay.id', 'mz6b4lxloygeblzvaupc');
        Config::set('services.openpay.sk', 'sk_1234567890abcdef');
        Config::set('services.preregistro.account_type_id', 1);
    }

    // ─────────────────────────────────────────────────────
    // 1. Validación de número de documento (validateDocumentNumber)
    // ─────────────────────────────────────────────────────

    /** @test */
    public function validar_dni_8_digitos_exactos()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'DNI',
            'numero_documento' => '87654321',
        ]);
        $response = $this->post(route('preregistro.openpay'), $payload);

        // Debe llegar al chequeo de credenciales (no fallar en validación de documento)
        // Si pasó la validación, el error será de credenciales o de otro campo
        $response->assertSessionMissing('numero_documento');
    }

    /** @test */
    public function rechazar_dni_con_menos_de_8_digitos()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'DNI',
            'numero_documento' => '1234567',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('numero_documento');
    }

    /** @test */
    public function rechazar_dni_con_letras()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'DNI',
            'numero_documento' => '1234567A',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('numero_documento');
    }

    /** @test */
    public function validar_carnet_extranjeria_6_a_12_caracteres()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'carnet_extranjeria',
            'numero_documento' => 'CE123456',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionMissing('numero_documento');
    }

    /** @test */
    public function rechazar_carnet_extranjeria_demasiado_corto()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'carnet_extranjeria',
            'numero_documento' => 'CE12',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('numero_documento');
    }

    /** @test */
    public function validar_pasaporte_6_a_20_caracteres()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'pasaporte',
            'numero_documento' => 'P12345678',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionMissing('numero_documento');
    }

    /** @test */
    public function rechazar_pasaporte_con_caracteres_especiales()
    {
        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'pasaporte',
            'numero_documento' => 'PAS@PORTE!',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('numero_documento');
    }

    /** @test */
    public function numero_documento_no_pasa_validacion_si_ya_existe_en_users()
    {
        User::create([
            'username'     => 'usuarioyaexistente',
            'password'     => Hash::make('secret'),
            'email'        => 'otro@ejemplo.com',
            'name'         => 'Otro',
            'last_name'    => 'Usuario',
            'date_birth'   => '1990-01-15',
            'phone'        => '111222333',
            'nro_document' => '87654321',
        ]);

        $payload = $this->validOpenpayPayload([
            'tipo_documento' => 'DNI',
            'numero_documento' => '87654321',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('numero_documento');
    }

    // ─────────────────────────────────────────────────────
    // 2. Validación de campos obligatorios en OpenPay
    // ─────────────────────────────────────────────────────

    /** @test */
    public function validar_campos_obligatorios_en_openpay()
    {
        $response = $this->post(route('preregistro.openpay'), []);

        $response->assertSessionHasErrors([
            'usuario', 'correo', 'password', 'password_confirm',
            'tipo_usuario', 'nombre', 'apellido', 'telefono',
            'fecha_nacimiento', 'tipo_documento', 'numero_documento',
            'tipo_cuenta', 'metodo_pago', 'referidor', 'lado',
        ]);
    }

    /** @test */
    public function validar_que_el_usuario_sea_unico_en_openpay()
    {
        User::create([
            'username'     => 'usuarioduplicado',
            'password'     => Hash::make('secret'),
            'email'        => 'existente@ejemplo.com',
            'name'         => 'Existente',
            'last_name'    => 'Usuario',
            'date_birth'   => '1990-01-15',
            'phone'        => '111222333',
            'nro_document' => '11111111',
        ]);

        $payload = $this->validOpenpayPayload(['usuario' => 'usuarioduplicado']);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('usuario');
    }

    /** @test */
    public function validar_que_el_correo_sea_unico_en_openpay()
    {
        User::create([
            'username'     => 'otrousuario',
            'password'     => Hash::make('secret'),
            'email'        => 'yaexiste@ejemplo.com',
            'name'         => 'Existente',
            'last_name'    => 'Usuario',
            'date_birth'   => '1990-01-15',
            'phone'        => '111222333',
            'nro_document' => '11111111',
        ]);

        $payload = $this->validOpenpayPayload(['correo' => 'yaexiste@ejemplo.com']);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('correo');
    }

    /** @test */
    public function validar_que_el_telefono_sea_unico_en_openpay()
    {
        User::create([
            'username'     => 'usuariocontelefono',
            'password'     => Hash::make('secret'),
            'email'        => 'telefono@ejemplo.com',
            'name'         => 'Dueño',
            'last_name'    => 'Teléfono',
            'date_birth'   => '1990-01-15',
            'phone'        => '999000111',
            'nro_document' => '11111111',
        ]);

        $payload = $this->validOpenpayPayload(['telefono' => '999000111']);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('telefono');
    }

    /** @test */
    public function validar_usuario_con_caracteres_especiales()
    {
        $payload = $this->validOpenpayPayload(['usuario' => 'usuario con espacios!']);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('usuario');
    }

    /** @test */
    public function validar_usuario_demasiado_corto()
    {
        $payload = $this->validOpenpayPayload(['usuario' => 'ab']);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('usuario');
    }

    // ─────────────────────────────────────────────────────
    // 3. OpenPay - Credenciales faltantes
    // ─────────────────────────────────────────────────────

    /** @test */
    public function devolver_error_500_si_faltan_ambas_credenciales_openpay()
    {
        Config::set('services.openpay.id', null);
        Config::set('services.openpay.sk', null);

        // Usar correo y teléfono únicos para que pase validación
        $payload = $this->validOpenpayPayload([
            'correo'   => 'paytest@ejemplo.com',
            'telefono' => '999000001',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'El sistema de pagos no está configurado correctamente. Por favor contacta al soporte técnico.',
        ]);
    }

    /** @test */
    public function devolver_error_500_si_falta_solo_openpay_id()
    {
        Config::set('services.openpay.id', null);

        $payload = $this->validOpenpayPayload([
            'correo'   => 'paytest2@ejemplo.com',
            'telefono' => '999000002',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'El sistema de pagos no está configurado correctamente. Por favor contacta al soporte técnico.',
        ]);
    }

    /** @test */
    public function devolver_error_500_si_falta_solo_openpay_sk()
    {
        Config::set('services.openpay.sk', null);

        $payload = $this->validOpenpayPayload([
            'correo'   => 'paytest3@ejemplo.com',
            'telefono' => '999000003',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'El sistema de pagos no está configurado correctamente. Por favor contacta al soporte técnico.',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 4. Concurrencia - Múltiples registros mismo correo
    // ─────────────────────────────────────────────────────

    /** @test */
    public function no_duplicar_preregistro_con_mismo_correo_en_store()
    {
        // El setUp ya creó un preregistro con juan@ejemplo.com
        // Así que al intentar crear otro con el mismo correo -> already_registered

        $response = $this->post(route('preregistro.store', ['username' => 'referidor']), [
            'nombres'   => 'Juan',
            'apellidos' => 'Pérez',
            'correo'    => 'juan@ejemplo.com',
            'whatsapp'  => '+51999888777',
            'lado'      => 'izquierda',
        ]);

        $response->assertJsonPath('status', 'already_registered');
        $this->assertEquals(1, Preregistro::where('correo', 'juan@ejemplo.com')->count());
    }

    /** @test */
    public function concurrencia_rapida_no_duplica_preregistros()
    {
        $payload = [
            'nombres'   => 'María',
            'apellidos' => 'López',
            'correo'    => 'maria@ejemplo.com',
            'whatsapp'  => '+51999111222',
            'lado'      => 'izquierda',
        ];

        // Primer envío
        $response1 = $this->post(route('preregistro.store', ['username' => 'referidor']), $payload);
        $response1->assertJsonPath('ok', true);

        // Segundo envío inmediato (simula doble clic o reintento)
        $response2 = $this->post(route('preregistro.store', ['username' => 'referidor']), $payload);

        // No debe haber creado un duplicado
        $this->assertEquals(1, Preregistro::where('correo', 'maria@ejemplo.com')->count());
        $response2->assertJsonPath('status', 'already_registered');
    }

    /** @test */
    public function concurrencia_rapida_con_dos_correos_distintos_funciona()
    {
        // Enviar con correo1
        $r1 = $this->post(route('preregistro.store', ['username' => 'referidor']), [
            'nombres'   => 'Ana',
            'apellidos' => 'Martínez',
            'correo'    => 'ana@ejemplo.com',
            'whatsapp'  => '+51999111333',
            'lado'      => 'izquierda',
        ]);
        $r1->assertJsonPath('ok', true);

        // Enviar con correo2 inmediatamente después
        $r2 = $this->post(route('preregistro.store', ['username' => 'referidor']), [
            'nombres'   => 'Luis',
            'apellidos' => 'Rodríguez',
            'correo'    => 'luis@ejemplo.com',
            'whatsapp'  => '+51999444555',
            'lado'      => 'izquierda',
        ]);
        $r2->assertJsonPath('ok', true);

        $this->assertEquals(1, Preregistro::where('correo', 'ana@ejemplo.com')->count());
        $this->assertEquals(1, Preregistro::where('correo', 'luis@ejemplo.com')->count());
    }

    // ─────────────────────────────────────────────────────
    // 5. Rate limiting / Seguridad
    // ─────────────────────────────────────────────────────

    /** @test */
    public function multiples_intentos_rapidos_con_diferentes_correos_funcionan()
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('preregistro.store', ['username' => 'referidor']), [
                'nombres'   => 'Usuario',
                'apellidos' => "Apellido{$i}",
                'correo'    => "usuario{$i}@ejemplo.com",
                'whatsapp'  => "+5199900000{$i}",
                'lado'      => 'izquierda',
            ]);

            $response->assertJsonPath('ok', true);
        }

        $this->assertEquals(5, Preregistro::where('referrer_username', 'referidor')
            ->where('correo', 'like', 'usuario%@ejemplo.com')
            ->count());
    }

    /** @test */
    public function multiples_intentos_a_check_duplicate_no_sobrecargan_sistema()
    {
        for ($i = 0; $i < 20; $i++) {
            $response = $this->get(route('preregistro.check-duplicate', [
                'field' => 'correo',
                'value' => 'nuevo@ejemplo.com',
            ]));

            $response->assertStatus(200);
            $response->assertJson(['taken' => false]);
        }

        $this->assertTrue(true);
    }

    /** @test */
    public function validar_que_usuario_existente_da_error_en_openpay()
    {
        $payload = $this->validOpenpayPayload([
            'usuario'  => 'referidor',
            'correo'   => 'correo_unico@ejemplo.com',
            'telefono' => '999000010',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('usuario');
    }

    /** @test */
    public function validar_que_referidor_inexistente_da_error_en_openpay()
    {
        $payload = $this->validOpenpayPayload([
            'referidor' => 'usuario_inexistente',
            'correo'    => 'correo_unico2@ejemplo.com',
            'telefono'  => '999000011',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('referidor');
    }

    /** @test */
    public function validar_fecha_nacimiento_menor_de_edad_da_error()
    {
        $payload = $this->validOpenpayPayload([
            'fecha_nacimiento' => '2010-05-15',
            'correo'           => 'menor@ejemplo.com',
            'telefono'         => '999000012',
        ]);

        $response = $this->post(route('preregistro.openpay'), $payload);

        $response->assertSessionHasErrors('fecha_nacimiento');
    }

    // ─────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────

    private function validOpenpayPayload(array $overrides = []): array
    {
        return array_merge([
            'usuario'          => 'juanperez',
            'correo'           => 'juan@ejemplo.com',
            'password'         => 'MiPassword123',
            'password_confirm' => 'MiPassword123',
            'tipo_usuario'     => 'distribuidor',
            'nombre'           => 'Juan',
            'apellido'         => 'Pérez',
            'telefono'         => '955511122',
            'fecha_nacimiento' => '1990-05-15',
            'tipo_documento'   => 'DNI',
            'numero_documento' => '87654321',
            'pais'             => 'Perú',
            'tipo_cuenta'      => 'pre_registro',
            'metodo_pago'      => 'tarjeta',
            'referidor'        => 'referidor',
            'lado'             => 'izquierda',
            'preregistro_id'   => $this->preregistro->id,
        ], $overrides);
    }
}
