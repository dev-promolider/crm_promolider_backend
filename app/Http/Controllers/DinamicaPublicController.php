<?php

namespace App\Http\Controllers;

use App\Models\Dinamica;
use App\Events\RuletaSpinEvent;
use App\Models\DinamicaRegistro;
use App\Services\DinamicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DinamicaPublicController extends Controller
{
    protected $dinamicaService;

    public function __construct(DinamicaService $dinamicaService)
    {
        $this->dinamicaService = $dinamicaService;
    }

    /**
     * Mostrar la dinámica pública
     */
    public function show($slug)
    {
        $dinamica = Dinamica::where('slug', $slug)->firstOrFail();
        
        // Contar ganadores y jugados
        $totalGanadores = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('ha_ganado', true)
            ->count();
        $totalJugados = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('ha_jugado', true)
            ->count();
        $totalParticipantes = DinamicaRegistro::where('dinamica_id', $dinamica->id)->count();
        $hayGanador = $totalGanadores > 0;
        $cerrarPorGanadores = $dinamica->max_ganadores && $totalGanadores >= $dinamica->max_ganadores;
        $cerrarPorJugados = $dinamica->max_participantes && $totalJugados >= $dinamica->max_participantes;
        if (($cerrarPorGanadores || $cerrarPorJugados) && $dinamica->is_active) {
            $dinamica->update(['is_active' => false]);
        }
        
        // Obtener todos los registros ordenados por turno
            $registros = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->orderBy('turno', 'asc')
                ->get();

            // Filtrar premios ya ganados para la ruleta
            $premios = $dinamica->premios ? $dinamica->premios->toArray() : [];
            $premiosGanados = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->whereNotNull('premio_ganado')
                ->pluck('premio_ganado')
                ->toArray();
            $premiosFiltrados = array_filter($premios, function($premio) use ($premiosGanados) {
                // Solo filtra si el premio no es "vacio" y ya fue ganado
                return $premio['tipo'] === 'vacio' || !in_array($premio['nombre'], $premiosGanados);
            });
            $premiosFiltrados = array_values($premiosFiltrados);
        
        // Determinar y gestionar el turno actual SOLO si la dinámica está activa
        $turnoActual = null;
        if ($dinamica->is_active && !$cerrarPorGanadores && !$cerrarPorJugados) {
            // El primero que no ha jugado y no ha ganado
            $turnoActual = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('ha_jugado', false)
                ->where('ha_ganado', false)
                ->orderBy('turno', 'asc')
                ->first();
            // Nota: no actualizamos la BD aquí. Los cambios de estado
            // (turno_inicio, ha_jugado) se realizan solo vía acciones explícitas
            // como girar la ruleta o timeouts gestionados por endpoints.
        }
        
        // Verificar si el usuario actual está registrado
        $emailSesion = Session::get('dinamica_email_' . $dinamica->id);
        $usuarioRegistro = null;
        $esMiTurno = false;
        
        if ($emailSesion) {
            $usuarioRegistro = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('email', $emailSesion)
                ->first();
            
            // Verificar si es su turno
            if ($turnoActual && $usuarioRegistro && $turnoActual->id === $usuarioRegistro->id) {
                $esMiTurno = true;
            }
        }
        
        return view('content.marketing.dinamica.public', compact(
        'dinamica',
        'registros',
        'turnoActual',
        'usuarioRegistro',
        'esMiTurno',
        'hayGanador',
        'premiosFiltrados'
    ));
    }

    /**
     * Registrar un participante en la dinámica
     */
    public function register(Request $request, $slug)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $dinamica = Dinamica::where('slug', $slug)->firstOrFail();
        
        // Verificar si ya hay un ganador
        $hayGanador = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('ha_ganado', true)
            ->exists();
            
        if ($hayGanador) {
            return response()->json([
                'success' => false,
                'message' => 'Esta dinámica ya tiene un ganador y ha finalizado.'
            ], 400);
        }
        
        // Verificar si ya está registrado
        $registroExistente = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('email', $request->email)
            ->first();
            
        if ($registroExistente) {
            // Guardar el email en sesión
            Session::put('dinamica_email_' . $dinamica->id, $request->email);
            
            return response()->json([
                'success' => true,
                'message' => 'Ya estás registrado en esta dinámica.',
                'turno' => $registroExistente->turno
            ]);
        }
        
        // Verificar límite de participantes
        if ($dinamica->max_participantes) {
            $totalRegistrados = DinamicaRegistro::where('dinamica_id', $dinamica->id)->count();
            if ($totalRegistrados >= $dinamica->max_participantes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lo sentimos, se ha alcanzado el límite de participantes.'
                ], 400);
            }
        }
        
        // Obtener el próximo número de turno
        $ultimoTurno = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->max('turno') ?? 0;
        $nuevoTurno = $ultimoTurno + 1;
        
        // Registrar al participante
        $registro = DinamicaRegistro::create([
            'dinamica_id' => $dinamica->id,
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'turno' => $nuevoTurno,
            'ha_jugado' => false,
            'ha_ganado' => false,
        ]);
        
        // Guardar el email en sesión
        Session::put('dinamica_email_' . $dinamica->id, $request->email);
        
        return response()->json([
            'success' => true,
            'message' => 'Te has registrado exitosamente. Tu turno es: ' . $nuevoTurno,
            'turno' => $nuevoTurno
        ]);
    }

    public function spin(Request $request, $slug)
    {
        $dinamica = Dinamica::where('slug', $slug)->firstOrFail();

        if (! $dinamica->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Esta ruleta no está activa en este momento.'
            ], 422);
        }

        $emailSesion = Session::get('dinamica_email_' . $dinamica->id);
        if (! $emailSesion) {
            return response()->json([
                'success' => false,
                'message' => 'Debes registrarte antes de poder jugar.'
            ], 403);
        }

        $registro = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('email', $emailSesion)
            ->first();

        if (! $registro) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos tu registro para esta dinámica.'
            ], 404);
        }

        if ($registro->ha_ganado) {
            return response()->json([
                'success' => false,
                'message' => 'Ya ganaste esta dinámica. No es necesario girar nuevamente.'
            ], 422);
        }

        if ($registro->ha_jugado) {
            return response()->json([
                'success' => false,
                'message' => 'Este turno ya fue utilizado.'
            ], 422);
        }

        $turnoActual = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('ha_jugado', false)
            ->where('ha_ganado', false)
            ->orderBy('turno', 'asc')
            ->first();

        if (! $turnoActual || $turnoActual->id !== $registro->id) {
            return response()->json([
                'success' => false,
                'message' => 'Debes esperar tu turno para poder girar.'
            ], 409);
        }

        $this->dinamicaService->ensureTurnTimerForTurn($dinamica, $turnoActual);

        $angle = rand(0, 359);
        $this->dinamicaService->recordTurnAngle($dinamica, $registro, $angle);
        broadcast(new RuletaSpinEvent($angle, $dinamica->slug, $registro->id));

        return response()->json([
            'success' => true,
            'message' => 'Giro autorizado.',
            'angle' => $angle
        ]);
    }
    
    /**
     * Marcar que el participante ha jugado su turno
     */
    public function marcarJugado(Request $request, $slug)
    {
        $dinamica = Dinamica::where('slug', $slug)->firstOrFail();
        $emailSesion = Session::get('dinamica_email_' . $dinamica->id);
        
        if (!$emailSesion) {
            return response()->json([
                'success' => false,
                'message' => 'No estás registrado en esta dinámica.'
            ], 403);
        }
        
        $registro = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('email', $emailSesion)
            ->first();
            
        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró tu registro.'
            ], 404);
        }
        
        // Marcar como jugado
        $registro->update([
            'ha_jugado' => true,
            'turno_inicio' => now()
        ]);

        $this->dinamicaService->finalizeTurnHistory($dinamica, $registro, 'sin_premio');
        $this->dinamicaService->advanceToNextTurn($dinamica);
        
        return response()->json([
            'success' => true,
            'message' => 'Uy, no ganaste. Suerte para la próxima.'
        ]);
    }
    
    /**
     * Registrar al ganador
     */
    public function registrarGanador(Request $request, $slug)
    {
        $dinamica = Dinamica::where('slug', $slug)->firstOrFail();
        $emailSesion = Session::get('dinamica_email_' . $dinamica->id);
        
        if (!$emailSesion) {
            return response()->json([
                'success' => false,
                'message' => 'No estás registrado en esta dinámica.'
            ], 403);
        }
        
        $registro = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('email', $emailSesion)
            ->first();
            
        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró tu registro.'
            ], 404);
        }
        
        // Registrar como ganador (manejo robusto del campo premio)
        $premio = $request->input('premio');
        if ($premio === null) {
            $raw = $request->getContent();
            if (!empty($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $premio = $decoded['premio'] ?? null;
                }
            }
        }

        $registro->ha_ganado = true;
        $registro->ha_jugado = true;
        $registro->turno_inicio = now();
        if (!empty($premio)) {
            $registro->premio_ganado = $premio;
        }
        $registro->save();

        $this->dinamicaService->finalizeTurnHistory($dinamica, $registro, 'ganador', [
            'premio_nombre' => $premio,
            'premio_tipo' => null,
        ]);

        // Cerrar la dinámica en cuanto exista un ganador
        if ($dinamica->is_active) {
            $dinamica->update(['is_active' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => '¡Felicidades! Has ganado.',
            'premio' => $request->input('premio')
        ]);
    }
}
