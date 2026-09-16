<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Membresias configurables de verdad.
 *
 * El ingeniero lo pidio asi: poder crear membresias en la categoria que decida,
 * retirarlas u ocultarlas, y que cada una diga si lleva OPC, cuanto cuesta, cuantos
 * meses dura o si es de pago unico. Hasta ahora esas decisiones estaban repartidas
 * por el codigo con identificadores fijos (el 4 era "University", el 5 y el 6 "no
 * alimentan la red", el listado publico filtraba por nombre) y el OPC vivia en un
 * apartado propio, separado de su membresia.
 *
 * Todo lo que se rellena aqui reproduce el comportamiento de hoy: nadie cambia de
 * condicion al migrar. Lo nuevo es que a partir de ahora se puede cambiar desde el
 * panel.
 *
 * La tabla account_type la sigue leyendo el monolito (promolider.info), que usa la
 * misma base; por eso solo se anaden columnas y no se toca ninguna existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        $ahora = now();
        $categorias = [
            ['slug' => 'preregistro', 'name' => 'Pre-registro', 'sort_order' => 10,
             'description' => 'Reserva de posición antes del lanzamiento. Solo existe una membresía en esta categoría.'],
            ['slug' => 'consumidor', 'name' => 'Consumidor', 'sort_order' => 20,
             'description' => 'Personas que acceden a la plataforma para formarse y consumir contenido.'],
            ['slug' => 'creador', 'name' => 'Creador', 'sort_order' => 30,
             'description' => 'Productores que publican cursos y otros infoproductos.'],
            ['slug' => 'constructor', 'name' => 'Constructor', 'sort_order' => 40,
             'description' => 'Constructores de comunidad que participan en el plan de compensación.'],
        ];

        foreach ($categorias as $categoria) {
            DB::table('membership_categories')->insert($categoria + [
                'status'     => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        Schema::table('account_type', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('account');
            $table->string('system_key', 40)->nullable()->unique()->after('category_id');
            $table->text('description')->nullable()->after('system_key');
            $table->unsignedInteger('sort_order')->default(0)->after('description');
            $table->boolean('is_visible')->default(false)->after('status');
            $table->boolean('is_permanent')->default(false)->after('enrollment_duration');
            $table->boolean('requires_opc')->default(false)->after('is_permanent');
            $table->boolean('feeds_network')->default(true)->after('requires_opc');
            $table->boolean('counts_as_top_tier')->default(false)->after('feeds_network');
            $table->unsignedInteger('max_members')->nullable()->after('counts_as_top_tier');
            $table->string('highlight_label', 60)->nullable()->after('max_members');
            $table->string('highlight_color', 20)->nullable()->after('highlight_label');
        });

        $idCategoria = DB::table('membership_categories')->pluck('id', 'slug');

        $conOpc = DB::table('product')
            ->where('name', 'opc')
            ->where('status', '1')
            ->whereNotNull('account_type_id')
            ->pluck('account_type_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $idUniversity = (int) (DB::table('options')
            ->where('description', 'university_account_type_id')
            ->value('value') ?: 4);

        // Las que hoy salen en el listado publico de planes, que filtraba por nombre.
        $visibles = ['school', 'academy', 'university', 'sociofundador'];

        $posicion = 10;

        foreach (DB::table('account_type')->orderBy('price')->orderBy('id')->get() as $membresia) {
            $clave = $this->normalizar($membresia->account);
            $id = (int) $membresia->id;

            $categoria = null;
            if ($clave === 'preregistro') {
                $categoria = 'preregistro';
            } elseif (in_array($clave, ['school', 'academy', 'university', 'sociofundador'], true)) {
                $categoria = 'constructor';
            } elseif ($clave === 'productorinvitado') {
                $categoria = 'creador';
            } elseif (in_array($clave, ['consumidorinvitado', 'gratuito'], true)) {
                $categoria = 'consumidor';
            }

            DB::table('account_type')->where('id', $id)->update([
                'category_id'        => $categoria ? $idCategoria[$categoria] : null,
                'system_key'         => $clave === 'preregistro' ? 'preregistro' : null,
                'sort_order'         => $posicion,
                'is_visible'         => in_array($clave, $visibles, true),
                // Hasta hoy toda cuenta de pago necesitaba el OPC al dia para estar
                // activa, tuviera o no un producto OPC con el que pagarlo (Socio
                // Fundador y Pre registro no lo tienen). Se conserva tal cual: quitar la
                // exigencia cambiaria quien esta activo y, con ello, lo que paga el
                // corte. El panel avisa de las que la exigen sin tener precio.
                'requires_opc'       => (float) $membresia->price > 0 || in_array($id, $conOpc, true),
                // El reparto de puntos y la calificacion excluian los tipos 5 y 6.
                'feeds_network'      => !in_array($id, [5, 6], true),
                // El corte y el generacional usaban el tipo 4 como "University".
                'counts_as_top_tier' => $id === $idUniversity,
                'highlight_label'    => $clave === 'sociofundador' ? $membresia->account : null,
                'highlight_color'    => $clave === 'sociofundador' ? '#d4a017' : null,
            ]);

            $posicion += 10;
        }
    }

    public function down(): void
    {
        Schema::table('account_type', function (Blueprint $table) {
            $table->dropUnique(['system_key']);
            $table->dropColumn([
                'category_id', 'system_key', 'description', 'sort_order', 'is_visible',
                'is_permanent', 'requires_opc', 'feeds_network', 'counts_as_top_tier',
                'max_members', 'highlight_label', 'highlight_color',
            ]);
        });

        Schema::dropIfExists('membership_categories');
    }

    private function normalizar(?string $nombre): string
    {
        $limpio = mb_strtolower(trim((string) $nombre));
        $limpio = strtr($limpio, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', '-' => '']);

        return preg_replace('/\s+/', '', $limpio) ?? $limpio;
    }
};
