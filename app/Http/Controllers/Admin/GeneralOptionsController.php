<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MLM\PlanSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Opciones Generales, traídas del monolito (promolider.info/config/option/list).
 *
 * Allí eran pestañas de un solo componente: General, Puntos, Categorías, Bancos,
 * Métodos de pago, Roles, Certificados y Curso, además de Niveles, Logros y Gestión de
 * Premios, que en el CRM nuevo ya tienen su pantalla en Gamificación. Aquí están los
 * endpoints que faltaban para llevar el resto al panel de administración.
 */
class GeneralOptionsController extends Controller
{
    /** Roles sobre los que funciona el sistema: no se pueden borrar. */
    private const ROLES_DEL_SISTEMA = ['Admin', 'Distributor', 'Producer'];

    private const OPCIONES = [
        'default_avatar'      => 'required|string|max:255',
        'daily_question'      => 'required|integer|min:0',
        'achievement'         => 'required|integer|min:0',
        'badges_level_one'    => 'required|integer|min:0',
        'badges_level_two'    => 'required|integer|min:0',
        'badges_level_three'  => 'required|integer|min:0',
        'referral_commission' => 'required|numeric|min:0|max:100',
        PlanSettings::IVA              => 'required|numeric|min:0|max:100',
        PlanSettings::PV_POR_USD_CURSO => 'required|numeric|min:0|max:100',
    ];

    // ==========================================
    // General, Puntos y Curso (tabla options)
    // ==========================================

    /**
     * Endpoint: GET /admin/general-options
     */
    public function getOptions()
    {
        $valores = DB::table('options')
            ->whereIn('description', array_keys(self::OPCIONES))
            ->pluck('value', 'description');

        $datos = [];
        foreach (array_keys(self::OPCIONES) as $clave) {
            $datos[$clave] = $valores->get($clave);
        }

        return response()->json(['data' => $datos]);
    }

    /**
     * Endpoint: PUT /admin/general-options
     */
    public function updateOptions(Request $request)
    {
        $reglas = [];
        foreach (self::OPCIONES as $clave => $regla) {
            $reglas[$clave] = str_replace('required|', 'sometimes|', $regla);
        }

        $validator = Validator::make($request->all(), $reglas);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->only(array_keys(self::OPCIONES)) as $clave => $valor) {
            DB::table('options')->updateOrInsert(
                ['description' => $clave],
                ['value' => (string) $valor, 'updated_at' => now()]
            );
        }

        PlanSettings::olvidarCache();

        return $this->getOptions()->setStatusCode(200);
    }

    // ==========================================
    // Categorías de cursos (tabla categories)
    // ==========================================

    public function getCategories()
    {
        $cursos = DB::table('courses')
            ->select('id_categories', DB::raw('COUNT(*) as total'))
            ->groupBy('id_categories')
            ->pluck('total', 'id_categories');

        $categorias = DB::table('categories')->orderBy('name')->get()
            ->map(function ($c) use ($cursos) {
                $c->courses_count = (int) ($cursos->get($c->id) ?? 0);
                return $c;
            });

        return response()->json(['data' => $categorias]);
    }

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // La columna icon no admite nulos: sin icono se guarda vacío.
        $id = DB::table('categories')->insertGetId([
            'name'       => $request->input('name'),
            'icon'       => (string) $request->input('icon', ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Categoría creada.', 'data' => DB::table('categories')->find($id)], 201);
    }

    public function updateCategory(Request $request, int $id)
    {
        if (!DB::table('categories')->where('id', $id)->exists()) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $datos = $request->only(['name']);
        if ($request->has('icon')) {
            $datos['icon'] = (string) $request->input('icon', '');
        }

        DB::table('categories')->where('id', $id)->update($datos + ['updated_at' => now()]);

        return response()->json(['message' => 'Categoría actualizada.', 'data' => DB::table('categories')->find($id)]);
    }

    public function destroyCategory(int $id)
    {
        $cursos = DB::table('courses')->where('id_categories', $id)->count();

        if ($cursos > 0) {
            return response()->json(['message' => "Hay {$cursos} cursos en esta categoría. Muévelos a otra antes de borrarla."], 422);
        }

        return $this->borrar('categories', $id, 'Categoría');
    }

    // ==========================================
    // Bancos (tabla bank)
    // ==========================================

    public function getBanks()
    {
        return response()->json(['data' => DB::table('bank')->orderBy('name')->get()]);
    }

    public function storeBank(Request $request)
    {
        return $this->guardarNombre('bank', $request, null, 'Banco');
    }

    public function updateBank(Request $request, int $id)
    {
        return $this->guardarNombre('bank', $request, $id, 'Banco');
    }

    public function destroyBank(int $id)
    {
        return $this->borrar('bank', $id, 'Banco');
    }

    // ==========================================
    // Métodos de pago (tabla payment_method)
    // ==========================================

    public function getPaymentMethods()
    {
        return response()->json(['data' => DB::table('payment_method')->orderBy('name')->get()
            ->map(function ($m) {
                $m->status = (int) $m->status === 1;
                return $m;
            })]);
    }

    public function storePaymentMethod(Request $request)
    {
        return $this->guardarNombre('payment_method', $request, null, 'Método de pago', true);
    }

    public function updatePaymentMethod(Request $request, int $id)
    {
        return $this->guardarNombre('payment_method', $request, $id, 'Método de pago', true);
    }

    public function destroyPaymentMethod(int $id)
    {
        return $this->borrar('payment_method', $id, 'Método de pago');
    }

    // ==========================================
    // Roles (Spatie)
    // ==========================================

    public function getRoles()
    {
        $usuarios = DB::table('model_has_roles')
            ->select('role_id', DB::raw('COUNT(*) as total'))
            ->groupBy('role_id')
            ->pluck('total', 'role_id');

        $roles = DB::table('roles')->orderBy('id')->get(['id', 'name', 'guard_name', 'created_at'])
            ->map(function ($r) use ($usuarios) {
                $r->users_count = (int) ($usuarios->get($r->id) ?? 0);
                $r->del_sistema = in_array($r->name, self::ROLES_DEL_SISTEMA, true);
                return $r;
            });

        return response()->json(['data' => $roles]);
    }

    public function storeRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:125|unique:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $guard = DB::table('roles')->where('name', 'Admin')->value('guard_name') ?? 'web';

        $rol = \Spatie\Permission\Models\Role::create(['name' => $request->input('name'), 'guard_name' => $guard]);

        return response()->json(['message' => 'Rol creado.', 'data' => $rol], 201);
    }

    public function destroyRole(int $id)
    {
        $rol = DB::table('roles')->find($id);

        if (!$rol) {
            return response()->json(['message' => 'Rol no encontrado.'], 404);
        }

        if (in_array($rol->name, self::ROLES_DEL_SISTEMA, true)) {
            return response()->json(['message' => 'Es un rol del sistema y no se puede borrar.'], 422);
        }

        if (DB::table('model_has_roles')->where('role_id', $id)->exists()) {
            return response()->json(['message' => 'Hay usuarios con este rol. Quítaselo antes de borrarlo.'], 422);
        }

        \Spatie\Permission\Models\Role::findById($id, $rol->guard_name)->delete();

        return response()->json(['message' => 'Rol eliminado.']);
    }

    // ==========================================
    // Plantillas de certificado
    // ==========================================

    public function getCertificateTemplates()
    {
        $plantillas = DB::table('certificate_templates')
            ->orderBy('id')
            ->get(['id', 'name', 'preview_image', 'is_active', 'created_at'])
            ->map(function ($p) {
                $p->is_active = (bool) $p->is_active;
                $p->courses_count = DB::table('courses')->where('certificate_template_id', $p->id)->count();
                return $p;
            });

        return response()->json(['data' => $plantillas]);
    }

    public function toggleCertificateTemplate(int $id)
    {
        $plantilla = DB::table('certificate_templates')->find($id);

        if (!$plantilla) {
            return response()->json(['message' => 'Plantilla no encontrada.'], 404);
        }

        DB::table('certificate_templates')->where('id', $id)->update([
            'is_active'  => !$plantilla->is_active,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => $plantilla->is_active ? 'Plantilla desactivada.' : 'Plantilla activada.']);
    }

    // ==========================================
    // Auxiliares
    // ==========================================

    private function guardarNombre(string $tabla, Request $request, ?int $id, string $etiqueta, bool $conEstado = false)
    {
        if ($id !== null && !DB::table($tabla)->where('id', $id)->exists()) {
            return response()->json(['message' => $etiqueta . ' no encontrado.'], 404);
        }

        $reglas = ['name' => ($id ? 'sometimes' : 'required') . '|string|max:255|unique:' . $tabla . ',name' . ($id ? ',' . $id : '')];
        if ($conEstado) {
            $reglas['status'] = 'nullable|boolean';
        }

        $validator = Validator::make($request->all(), $reglas);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $datos = $request->only(['name']);
        if ($conEstado && $request->has('status')) {
            $datos['status'] = $request->boolean('status') ? 1 : 0;
        }
        $datos['updated_at'] = now();

        if ($id === null) {
            if ($conEstado && !isset($datos['status'])) {
                $datos['status'] = 1;
            }
            $id = DB::table($tabla)->insertGetId($datos + ['created_at' => now()]);

            return response()->json(['message' => $etiqueta . ' creado.', 'data' => DB::table($tabla)->find($id)], 201);
        }

        DB::table($tabla)->where('id', $id)->update($datos);

        return response()->json(['message' => $etiqueta . ' actualizado.', 'data' => DB::table($tabla)->find($id)]);
    }

    private function borrar(string $tabla, int $id, string $etiqueta)
    {
        try {
            $borradas = DB::table($tabla)->where('id', $id)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Clave foránea: hay registros que lo usan.
            return response()->json(['message' => $etiqueta . ' en uso: hay registros que dependen de él.'], 422);
        }

        return $borradas
            ? response()->json(['message' => $etiqueta . ' eliminado.'])
            : response()->json(['message' => $etiqueta . ' no encontrado.'], 404);
    }
}
