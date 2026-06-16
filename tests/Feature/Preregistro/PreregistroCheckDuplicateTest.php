<?php

namespace Tests\Feature\Preregistro;

use Tests\TestCase;
use App\Models\User;
use App\Models\UnverifiedUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class PreregistroCheckDuplicateTest extends TestCase
{
    use DatabaseTransactions, SetupPreregistroTables;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPreregistroTables();
    }

    /** @test */
    public function devolver_taken_true_cuando_username_ya_existe_en_users()
    {
        User::create([
            'username'    => 'usuarioexistente',
            'password'    => Hash::make('secret'),
            'email'       => 'existente@ejemplo.com',
            'name'        => 'Test',
            'last_name'   => 'User',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '12345678',
        ]);

        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'usuario',
            'value' => 'usuarioexistente',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'taken'   => true,
            'message' => 'Este nombre de usuario ya está en uso. Por favor elige otro.',
        ]);
    }

    /** @test */
    public function devolver_taken_true_cuando_username_ya_existe_en_unverified_users()
    {
        $unverified = new UnverifiedUser();
        $unverified->username = 'usuariopendiente';
        $unverified->password = Hash::make('secret');
        $unverified->openpay_order_id = 'ord_123';
        $unverified->data = '{}';
        $unverified->save();

        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'usuario',
            'value' => 'usuariopendiente',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'taken'   => true,
            'message' => 'Este nombre de usuario ya está en uso. Por favor elige otro.',
        ]);
    }

    /** @test */
    public function devolver_taken_false_cuando_username_no_existe()
    {
        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'usuario',
            'value' => 'nuevousuario',
        ]));

        $response->assertStatus(200);
        $response->assertJson(['taken' => false]);
    }

    /** @test */
    public function devolver_taken_true_cuando_correo_ya_existe_en_users()
    {
        User::create([
            'username'    => 'otrousuario',
            'password'    => Hash::make('secret'),
            'email'       => 'existente@ejemplo.com',
            'name'        => 'Test',
            'last_name'   => 'User',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '12345678',
        ]);

        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'correo',
            'value' => 'existente@ejemplo.com',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'taken'   => true,
            'message' => 'Este correo ya está registrado en el sistema. Si ya tienes una cuenta, inicia sesión.',
        ]);
    }

    /** @test */
    public function devolver_taken_false_cuando_correo_no_existe()
    {
        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'correo',
            'value' => 'nuevo@ejemplo.com',
        ]));

        $response->assertStatus(200);
        $response->assertJson(['taken' => false]);
    }

    /** @test */
    public function devolver_taken_true_cuando_numero_documento_ya_existe_en_users()
    {
        User::create([
            'username'    => 'otrousuario',
            'password'    => Hash::make('secret'),
            'email'       => 'otro@ejemplo.com',
            'name'        => 'Test',
            'last_name'   => 'User',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '12345678',
        ]);

        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'numero_documento',
            'value' => '12345678',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'taken'   => true,
            'message' => 'Este número de documento ya está registrado en otra cuenta.',
        ]);
    }

    /** @test */
    public function devolver_taken_false_cuando_numero_documento_no_existe()
    {
        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'numero_documento',
            'value' => '87654321',
        ]));

        $response->assertStatus(200);
        $response->assertJson(['taken' => false]);
    }

    /** @test */
    public function devolver_taken_false_cuando_no_se_envian_campos()
    {
        $response = $this->getJson('/preregistro/check-duplicate');

        $response->assertStatus(200);
        $response->assertJson(['taken' => false]);
    }

    /** @test */
    public function devolver_taken_false_para_campo_desconocido()
    {
        $response = $this->getJson('/preregistro/check-duplicate?' . http_build_query([
            'field' => 'campo_desconocido',
            'value' => 'cualquiervalor',
        ]));

        $response->assertStatus(200);
        $response->assertJson(['taken' => false]);
    }
}
