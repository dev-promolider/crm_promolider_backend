<?php

namespace Tests\Feature\Preregistro;

use Tests\TestCase;
use App\Models\User;
use App\Models\PreregistroLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class PreregistroBladeViewTest extends TestCase
{
    use DatabaseTransactions, SetupPreregistroTables;

    private User $user;
    private PreregistroLink $linkConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPreregistroTables();

        $this->user = User::create([
            'username'    => 'referidortest',
            'password'    => Hash::make('secret'),
            'email'       => 'referidor@ejemplo.com',
            'name'        => 'Carlos',
            'last_name'   => 'García',
            'date_birth'  => '1990-01-15',
            'phone'       => '999888777',
            'nro_document' => '12345678',
        ]);

        $this->linkConfig = PreregistroLink::create([
            'username' => 'referidortest',
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ]);
    }

    /** @test */
    public function vista_claro_muestra_datos_del_referidor()
    {
        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('Carlos');
        $response->assertSee('García');
        $response->assertSee('referidor@ejemplo.com');
        $response->assertSee('999888777');
    }

    /** @test */
    public function vista_claro_muestra_formulario_de_registro()
    {
        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('Comienza Ahora');
        $response->assertSee('Reserva tu posición');
        $response->assertSee('GRATIS');
        $response->assertSee('RESERVAR MI POSICIÓN');
        $response->assertSee('Nombre Completo');
        $response->assertSee('WhatsApp');
        $response->assertSee('Correo Electrónico');
        $response->assertSee('ACCEDER AL ECOSISTEMA');
    }

    /** @test */
    public function vista_claro_muestra_countdown_y_stats()
    {
        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('Cierre del pre-registro');
        $response->assertSee('Días');
        $response->assertSee('Horas');
        $response->assertSee('Min');
        $response->assertSee('Seg');
        $response->assertSee('Socios pre-registrados');
        $response->assertSee('Países de Latinoamérica');
        $response->assertSee('Herramientas de IA incluidas');
    }

    /** @test */
    public function vista_claro_muestra_secciones_principales()
    {
        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('¿Cómo funciona?');
        $response->assertSee('Te registras gratis');
        $response->assertSee('Recibes acceso prioritario');
        $response->assertSee('Construyes tu red');
        $response->assertSee('IA Content Agent');
        $response->assertSee('App Academia Mobile');
        $response->assertSee('App LeadBoost');
        $response->assertSee('Smart Funnels con IA');
        $response->assertSee('Socio Fundador');
        $response->assertSee('Términos y Condiciones');
        $response->assertSee('Políticas de Privacidad');
    }

    /** @test */
    public function vista_oscuro_muestra_datos_del_referidor()
    {
        $this->linkConfig->update(['landing' => 'oscuro']);

        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('Carlos');
        $response->assertSee('García');
        $response->assertSee('referidor@ejemplo.com');
        $response->assertSee('999888777');
    }

    /** @test */
    public function vista_oscuro_muestra_formulario_de_registro()
    {
        $this->linkConfig->update(['landing' => 'oscuro']);

        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('Comienza Ahora');
        $response->assertSee('Reserva tu posición');
        $response->assertSee('GRATIS');
        $response->assertSee('RESERVAR MI POSICIÓN');
        $response->assertSee('ACCEDER AL ECOSISTEMA');
    }

    /** @test */
    public function vista_oscuro_muestra_testimonios()
    {
        $this->linkConfig->update(['landing' => 'oscuro']);

        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        $response->assertSee('Testimonios');
        $response->assertSee('María García');
        $response->assertSee('Carlos Rodríguez');
        $response->assertSee('Andrea Pérez');
    }

    /** @test */
    public function vista_oscuro_tiene_estilo_dark()
    {
        $this->linkConfig->update(['landing' => 'oscuro']);

        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);

        // El tema oscuro usa colores bg oscuros
        $response->assertSee('#0a0a0a');
        $response->assertSee('nav-glass');
    }

    /** @test */
    public function landing_por_query_param_tema_oscuro_muestra_vista_oscura()
    {
        $response = $this->get(route('preregistro.index', [
            'username' => 'referidortest',
            'tema'     => 'oscuro',
        ]));

        $response->assertStatus(200);

        $response->assertSee('#0a0a0a');
        $response->assertSee('nav-glass');
        $response->assertSee('Testimonios');
    }

    /** @test */
    public function vista_incluye_csrf_token()
    {
        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);
        $response->assertSee('csrf-token');
    }

    /** @test */
    public function vista_incluye_datos_de_configuracion_en_javascript()
    {
        $response = $this->get(route('preregistro.index', ['username' => 'referidortest']));

        $response->assertStatus(200);
        $response->assertSee('window.USERNAME');
        $response->assertSee('referidortest');
        $response->assertSee('window.LADO');
    }
}
