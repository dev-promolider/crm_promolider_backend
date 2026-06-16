<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterDinamicaParticipantRequest;
use App\Http\Requests\StoreDinamicaSpecificationsRequest;
use App\Http\Requests\StoreTriviaDinamicaRequest;
use App\Models\DinamicaRegistro;
use App\Models\Dinamica;
use App\Services\CategoryService;
use App\Services\DinamicaService;
use App\Services\QuestionCategoryService;

class DinamicaController extends Controller
{
    protected $dinamicaService;
    protected $categoryService;
    protected $questionCategoryService;

    public function __construct(
        DinamicaService $dinamicaService,
        CategoryService $categoryService,
        QuestionCategoryService $questionCategoryService
    ) {
        $this->dinamicaService = $dinamicaService;
        $this->categoryService = $categoryService;
        $this->questionCategoryService = $questionCategoryService;
    }

    /**
     * Muestra el formulario para crear dinámicas
     */
    public function create()
    {
        return view('content.marketing.dinamica.create', [
            'categories' => $this->categoryService->getAll(),
            'dinamicas' => $this->dinamicaService->getUserDinamicas(10),
        ]);
    }

    /**
     * Muestra el formulario de especificaciones de dinámica
     */
    public function createSpecifications(Request $request)
    {
        $data = ['dinamica' => null, 'premios' => []];
        
        if ($request->has('edit')) {
            $result = $this->dinamicaService->getDinamicaForEdit($request->edit);
            if ($result) {
                $data = $result;
            } else {
                abort(404, 'Dinámica no encontrada o sin permisos');
            }
        }
        
        return view('content.marketing.dinamica.specifications', $data);
    }

    /**
     * Vista blueprint para la dinámica de trivia
     */
    public function showTriviaDesigner()
    {
        return view('content.marketing.dinamica.trivia', [
            'categories' => $this->questionCategoryService->list(['is_active' => true]),
            'mode' => 'create',
        ]);
    }

    public function editTrivia($id)
    {
        $payload = $this->dinamicaService->getTriviaDesignerData($id);

        return view('content.marketing.dinamica.trivia', [
            'categories' => $this->questionCategoryService->list(['is_active' => true]),
            'mode' => 'edit',
            'dinamicaId' => $payload['dinamica']->id,
            'registrationConfig' => $payload['registration_config'],
            'triviaConfig' => $payload['trivia_config'],
            'gameBlocks' => $payload['game_blocks'],
        ]);
    }

    public function storeTrivia(StoreTriviaDinamicaRequest $request)
    {
        $result = $this->dinamicaService->storeTrivia($request->validated());
        $status = $result['success'] ? 201 : ($result['error'] ?? false ? 500 : 422);
        return response()->json($result, $status);
    }

    /**
     * Guarda las especificaciones de una dinámica
     */
    public function storeSpecifications(StoreDinamicaSpecificationsRequest $request)
    {
        $result = $this->dinamicaService->storeSpecifications($request->validated());

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'dinamica_id' => $result['dinamica_id'],
            'public_url' => route('dinamica.public', $result['slug']),
            'redirect' => route('marketing.dinamica.create'),
        ]);
    }

    /**
     * Muestra la página pública de una dinámica
     */
    public function showPublic($slug)
    {
        $data = $this->dinamicaService->getPublicDinamicaData($slug);
        if (($data['dinamica']->tipo_dinamica ?? null) === 'trivia') {
            return view('content.marketing.dinamica.public-trivia', $data);
        }

        return view('content.marketing.dinamica.public', $data);
    }

    /**
     * Registra un participante en una dinámica pública
     */
    public function registerPublic($slug, RegisterDinamicaParticipantRequest $request)
    {
        $result = $this->dinamicaService->registerParticipant($slug, $request->validated());

        if ($request->expectsJson()) {
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Error al procesar el registro',
            ], 422);
        }

        if ($result['success']) {
            // Si es trivia y está activa, redirigir al preview real
            $dinamica = \App\Models\Dinamica::where('slug', $slug)->first();
            if ($dinamica && $dinamica->tipo_dinamica === 'trivia' && $dinamica->is_active) {
                return redirect()->route('dinamica.public.preview', ['slug' => $slug]);
            }
            return back()->with('status', $result['message']);
        }
        return back()->withErrors(['registro' => $result['error']])->withInput();
    }

    public function participantsFeed($slug)
    {
        $data = $this->dinamicaService->getPublicDinamicaData($slug);
        $dinamica = $data['dinamica'];

        $participants = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $turnoActual = $data['turnoActual'] ?? null;

        return response()->json([
            'participants' => $participants->map(function (DinamicaRegistro $registro) use ($dinamica) {
                return [
                    'id' => $registro->id,
                    'nombre' => $registro->nombre,
                    'apellido' => $registro->apellido,
                    'email' => $registro->email,
                    'turno' => $dinamica->tipo_dinamica === 'trivia' ? null : $registro->turno,
                    'ha_jugado' => (bool) $registro->ha_jugado,
                    'ha_ganado' => (bool) $registro->ha_ganado,
                ];
            }),
            'total' => $participants->count(),
            'updated_at' => now()->toIso8601String(),
            'turno_actual' => $turnoActual ? [
                'id' => $turnoActual->id,
                'turno' => $turnoActual->turno,
                'nombre' => $turnoActual->nombre,
                'apellido' => $turnoActual->apellido,
                'started_at' => $data['turno_started_at'] ?? null,
                'expires_at' => $data['turno_expires_at'] ?? null,
            ] : null,
            'turno_remaining_seconds' => $data['turno_remaining_seconds'] ?? null,
            'turno_duration_seconds' => $data['turno_duration_seconds'] ?? null,
        ]);
    }

    public function publicStatus($slug)
    {
        $dinamica = Dinamica::where('slug', $slug)->first();

        if (! $dinamica) {
            return response()->json([
                'success' => false,
                'message' => 'Dinámica no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_active' => (bool) $dinamica->is_active,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Alterna el estado activo/inactivo de una dinámica
     */
    public function toggleStatus($id)
    {
        $result = $this->dinamicaService->toggleStatus($id);
        return $result['success']
            ? redirect()->back()->with('status', $result['message'])
            : redirect()->back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Elimina una dinámica
     */
    public function destroy($id)
    {
        $result = $this->dinamicaService->delete($id);
        $status = $result['success'] ? 200 : (str_contains($result['message'], 'permiso') ? 403 : 500);
        return response()->json($result, $status);
    }

}
