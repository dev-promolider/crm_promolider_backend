<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MiniCourse;
use App\Models\MiniCourseModule;
use App\Models\MiniCourseDocument;
use App\Models\MiniCourseClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Traits\S3FileTrait;

class MiniCourseModuleController extends Controller
{
    use S3FileTrait;

    public function __construct()
    {
        $this->middleware('can:marketing.tools');
        $this->initializeS3Client();
    }

    /**
     * Mostrar formulario para agregar módulos
     */
    public function create($miniCourseId)
    {
        try {
            $miniCourse = MiniCourse::with('category')
                ->where('id', $miniCourseId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$miniCourse) {
                return redirect()->back()->withErrors('Mini curso no encontrado o sin permisos.');
            }

            return view('content.marketing.mini-course.add-modules', compact('miniCourse'));

        } catch (\Throwable $th) {
            Log::error('Error al cargar formulario de agregar módulo:', [
                'mini_course_id' => $miniCourseId,
                'error' => $th->getMessage(),
            ]);

            return redirect()->back()->withErrors('Error al cargar el formulario.');
        }
    }

    /**
     * Obtener módulos de un mini curso específico
     */
    public function getModulesByMiniCourse($miniCourseId)
    {
        try {
            $user = Auth::user();
            
            $miniCourse = MiniCourse::with([
                'modules.classes',           // ✅ CAMBIO: usar 'classes'
                'modules.classes.documents', // ✅ CAMBIO: usar 'classes.documents'
                'category'
            ])
            ->where('id', $miniCourseId)
            ->where('user_id', $user->id)
            ->first();
            
            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado o no tienes permisos para verlo'
                ], 404);
            }
        
            $result = [
                'mini_course_id' => $miniCourse->id,
                'mini_course_title' => $miniCourse->title,
                'mini_course_description' => $miniCourse->description,
                'mini_course_level' => $miniCourse->level,
                'mini_course_duration' => $miniCourse->duration,
                'mini_course_status' => $miniCourse->status,
                'category' => $miniCourse->category ? $miniCourse->category->name : null,
                'modules_count' => $miniCourse->modules->count(),
                'modules' => $miniCourse->modules->map(function ($module) {
                    return [
                        'module_id' => $module->id,
                        'module_title' => $module->title,
                        'module_content' => $module->content,
                        'module_duration' => $module->duration,
                        'created_at' => $module->created_at,
                        'videos_count' => $module->classes->count(),  // ✅ CAMBIO: usar 'classes'
                        'documents_count' => $module->classes->sum(function($class) {
                            return $class->documents->count();
                        }), // ✅ CAMBIO: contar documentos de todas las classes
                        'classes' => $module->classes->sortBy('order')->map(function ($video) {  // ✅ CAMBIO: usar 'classes'
                            return [
                                'video_id' => $video->id,
                                'title' => $video->title,
                                'description' => $video->description,
                                'duration' => $video->duration,
                                'order' => $video->order,
                                'video_url' => $this->generateS3Url($video->video),
                                'created_at' => $video->created_at,
                                'documents' => $video->documents->map(function ($document) {  // ✅ NUEVO: documentos por clase
                                    return [
                                        'document_id' => $document->id,
                                        'document_url' => $this->generateS3Url($document->document),
                                        'created_at' => $document->created_at
                                    ];
                                })
                            ];
                        })->values(),
                    ];
                })
            ];
        
            return response()->json([
                'message' => 'Módulos del mini curso obtenidos correctamente',
                'data' => $result
            ], 200);
        
        } catch (\Throwable $th) {
            Log::error('Error al obtener módulos del mini curso específico', [
                'user_id' => Auth::id(),
                'mini_course_id' => $miniCourseId,
                'error' => $th->getMessage(),
            ]);
        
            return response()->json([
                'message' => 'Error al obtener los módulos del mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un módulo básico (solo título)
     */
    public function storeBasic(Request $request, $miniCourseId)
    {
        $user = Auth::user();
        Log::info('Creando módulo básico', [
            'user_id' => $user->id,
            'mini_course_id' => $miniCourseId
        ]);
        
        try {
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado o sin permisos'], 404);
            }
            
            $validated = $request->validate([
                'titulo' => 'required|string|max:255'
            ]);
            
            DB::beginTransaction();
            
            $module = MiniCourseModule::create([
                'mini_course_id' => $miniCourse->id,
                'title' => $request->titulo,
                'content' => 'Contenido por definir',
                'duration' => 1,
            ]);
            
            Log::info('Módulo básico creado', ['module_id' => $module->id]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Módulo creado con éxito.',
                'data' => [
                    'module' => $module,
                    'mini_course_id' => $miniCourse->id
                ]
            ], 201);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al crear módulo básico', [
                'mini_course_id' => $miniCourseId,
                'error' => $th->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al crear el módulo.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar módulo específico
     */
    public function show($miniCourseId, $moduleId)
    {
        try {
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado o sin permisos'], 404);
            }
            
            $module = MiniCourseModule::with(['videos', 'documents'])
                ->where('mini_course_id', $miniCourseId)
                ->where('id', $moduleId)
                ->first();
            
            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }

            // Formatear URLs
            $module->videos->each(function ($video) {
                $video->video = $this->generateS3Url($video->video);
            });
            $module->documents->each(function ($document) {
                $document->document = $this->generateS3Url($document->document);
            });

            return response()->json([
                'message' => 'Módulo obtenido correctamente',
                'data' => $module
            ], 200);
            
        } catch (\Throwable $th) {
            Log::error('Error al obtener módulo específico', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Error al obtener el módulo',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un módulo completo con sus clases
     */
    public function update(Request $request, $miniCourseId, $moduleId)
    {
        try {
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado o sin permisos'], 404);
            }
            
            $module = MiniCourseModule::where('mini_course_id', $miniCourseId)
                                       ->where('id', $moduleId)
                                       ->first();
            
            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }
        
            // ✅ VALIDACIÓN CORREGIDA
            $validated = $request->validate([
                // Datos básicos del módulo
                'titulo' => 'required|string|max:255',
                'contenido' => 'required|string',
                
                // Videos nuevos
                'videos' => 'nullable|array',
                'videos.*' => 'nullable|file|mimes:mp4,mov,avi,webm|max:512000',
                'video_titles' => 'nullable|array',
                'video_titles.*' => 'nullable|string|max:255',
                'video_descriptions' => 'nullable|array',
                'video_descriptions.*' => 'nullable|string',
                'video_durations' => 'nullable|array',
                'video_durations.*' => 'nullable|integer|min:1',
                'video_orders' => 'nullable|array',
                'video_orders.*' => 'nullable|integer|min:1',
                
                // Documentos nuevos
                'documentos' => 'nullable|array',
                'documentos.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
                
                // IDs de videos/documentos a eliminar
                'delete_videos' => 'nullable|array',
                'delete_videos.*' => 'integer|exists:mini_course_classes,id',
                'delete_documents' => 'nullable|array',
                'delete_documents.*' => 'integer|exists:mini_course_documents,id',
                
                // ✅ ACTUALIZACIÓN DE VIDEOS EXISTENTES - REGLAS CORREGIDAS
                'existing_videos' => 'nullable|array',
                'existing_videos.*.id' => 'required|integer|exists:mini_course_classes,id',
                'existing_videos.*.title' => 'nullable|string|max:255',
                'existing_videos.*.description' => 'nullable|string',
                'existing_videos.*.duration' => 'nullable|integer|min:1',
                'existing_videos.*.order' => 'nullable|integer|min:1',
                'existing_videos.*.delete_documents' => 'nullable|array',
                'existing_videos.*.delete_documents.*' => 'integer|exists:mini_course_documents,id',
                'existing_videos.*.new_documents' => 'nullable|array',
                'existing_videos.*.new_documents.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
            ]);
            
            // ✅ LOG DETALLADO PARA DEBUG
            Log::info('Datos recibidos para actualización de módulo', [
                'module_id' => $moduleId,
                'titulo' => $request->titulo,
                'contenido' => substr($request->contenido, 0, 100) . '...',
                'duracion' => $request->duracion,
                'existing_videos' => $request->existing_videos,
                'new_videos_count' => $request->hasFile('videos') ? count($request->file('videos')) : 0,
                'new_documents_count' => $request->hasFile('documentos') ? count($request->file('documentos')) : 0,
            ]);
            
            DB::beginTransaction();
            
            // Actualizar datos básicos del módulo
            $module->update([
                'title' => $request->titulo,
                'content' => $request->contenido,
            ]);
            
            // ✅ ACTUALIZAR VIDEOS EXISTENTES (MEJORADO CON DOCUMENTOS)
            if ($request->has('existing_videos')) {
                foreach ($request->existing_videos as $videoData) {
                    $video = MiniCourseClass::where('id', $videoData['id'])
                        ->where('mini_course_module_id', $moduleId)
                        ->first();

                    if ($video) {
                        $updateData = [];

                        if (isset($videoData['title'])) {
                            $updateData['title'] = $videoData['title'];
                        }
                        if (isset($videoData['description'])) {
                            $updateData['description'] = $videoData['description'];
                        }
                        if (isset($videoData['duration'])) {
                            $updateData['duration'] = $videoData['duration'];
                        }
                        if (isset($videoData['order'])) {
                            $updateData['order'] = $videoData['order'];
                        }

                        if (!empty($updateData)) {
                            $video->update($updateData);
                        }

                        // AÑADIR: Procesar documentos de esta clase
                        // Eliminar documentos de esta clase
                        if (isset($videoData['delete_documents']) && is_array($videoData['delete_documents'])) {
                            $documentsToDelete = MiniCourseDocument::whereIn('id', $videoData['delete_documents'])
                                ->where('mini_course_class_id', $video->id)
                                ->get();

                            foreach ($documentsToDelete as $document) {
                                $this->deleteFileFromS3($document->document);
                                $document->delete();
                            }
                        }

                        // Agregar nuevos documentos a esta clase
                        if (isset($videoData['new_documents']) && is_array($videoData['new_documents'])) {
                            foreach ($videoData['new_documents'] as $documentFile) {
                                if ($documentFile && $documentFile->isValid()) {
                                    $documentName = time() . '_' . $documentFile->getClientOriginalName();
                                    $documentPath = $documentFile->storeAs('mini-courses/documents', $documentName, 's3');

                                    MiniCourseDocument::create([
                                        'mini_course_id' => $miniCourse->id,
                                        'mini_course_module_id' => $moduleId,
                                        'mini_course_class_id' => $video->id,
                                        'user_id' => Auth::id(),
                                        'document' => $documentPath,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            
            // Eliminar videos solicitados
            if ($request->has('delete_videos')) {
                $videosToDelete = MiniCourseClass::whereIn('id', $request->delete_videos)
                    ->where('mini_course_module_id', $moduleId)
                    ->get();
                
                foreach ($videosToDelete as $video) {
                    $this->deleteFileFromS3($video->video);
                    $video->delete();
                    Log::info('Video eliminado', ['video_id' => $video->id]);
                }
            }
            
            // Eliminar documentos solicitados
            if ($request->has('delete_documents')) {
                $documentsToDelete = MiniCourseDocument::whereIn('id', $request->delete_documents)
                    ->where('mini_course_module_id', $moduleId)
                    ->get();
                
                foreach ($documentsToDelete as $document) {
                    $this->deleteFileFromS3($document->document);
                    $document->delete();
                    Log::info('Documento eliminado', ['document_id' => $document->id]);
                }
            }
            
            // Agregar nuevos videos
            if ($request->hasFile('videos')) {
                $this->processVideosForModule($request, $module, $miniCourse, Auth::user());
            }
            
            // Agregar nuevos documentos
            if ($request->hasFile('documentos')) {
                $this->processDocumentsForModule($request, $module, $miniCourse, Auth::user());
            }

            $moduleDuration = $module->classes()->sum('duration');
            $module->update(['duration' => $moduleDuration]);
            
            // Actualizar duración total del mini curso
            $totalDuration = $miniCourse->modules()->sum('duration');
            $miniCourse->update(['duration' => $totalDuration]);
            
            DB::commit();
            
            Log::info('Módulo actualizado exitosamente', [
                'module_id' => $module->id,
                'new_total_duration' => $totalDuration
            ]);
            
            return response()->json([
                'message' => 'Módulo actualizado completamente con éxito.',
                'data' => [
                    'module_id' => $module->id,
                    'mini_course_id' => $miniCourse->id,
                    'new_total_duration' => $totalDuration
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ MANEJO ESPECÍFICO DE ERRORES DE VALIDACIÓN
            Log::error('Error de validación al actualizar módulo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'validation_errors' => $e->errors(),
                'input_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al actualizar módulo completo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el módulo.',
                'error' => config('app.debug') ? $th->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Agregar clases (videos/documentos) a un módulo específico
     */
    public function addClasses(Request $request, $miniCourseId, $moduleId)
    {
        $user = Auth::user();
        Log::info('Agregando clases a módulo', [
            'user_id' => $user->id,
            'mini_course_id' => $miniCourseId,
            'module_id' => $moduleId
        ]);

        try {
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado o sin permisos'], 404);
            }

            $module = MiniCourseModule::where('mini_course_id', $miniCourseId)
                                       ->where('id', $moduleId)
                                       ->first();

            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }

            $validated = $request->validate([
                // Datos del módulo (opcional para actualizar)
                'contenido' => 'nullable|string',
                'duracion' => 'nullable|integer|min:1',

                // Una sola clase por petición
                'video' => 'required|file|mimes:mp4,mov,avi,webm|max:512000',
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'duracion_clase' => 'required|integer|min:1',
                'orden' => 'required|integer|min:1',

                // Múltiples documentos opcionales para la clase
                'documentos' => 'nullable|array',
                'documentos.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
            ]);

            DB::beginTransaction();

            // Actualizar módulo si se proporcionan datos
            if ($request->filled('contenido')) {
                $module->content = $request->contenido;
            }
            if ($request->filled('duracion')) {
                $module->duration = $request->duracion;
            }
            $module->save();

            // Procesar la clase única
            Log::info('Procesando nueva clase', [
                'titulo' => $request->titulo,
                'module_id' => $moduleId
            ]);

            // Subir video de la clase
            $videoFile = $request->file('video');
            $videoPath = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/videos/';

            Log::info('Subiendo video de clase a S3', [
                'original_name' => $videoFile->getClientOriginalName(),
                'size_bytes' => $videoFile->getSize(),
                'target_path' => $videoPath
            ]);

            $uploadResult = $this->uploadVideoToS3($videoFile, $videoPath);

            if (!$uploadResult['success']) {
                throw new \Exception('Error subiendo video de clase: ' . $uploadResult['error']);
            }

            // Crear la clase
            $clase = MiniCourseClass::create([
                'mini_course_id' => $miniCourse->id,
                'mini_course_module_id' => $module->id,
                'video' => Storage::disk('s3')->url($uploadResult['path']),
                'title' => $request->titulo,
                'description' => $request->descripcion,
                'duration' => $request->duracion_clase,
                'order' => $request->orden,
            ]);

            Log::info('Clase creada exitosamente', [
                'clase_id' => $clase->id,
                'titulo' => $clase->title
            ]);

            // Procesar documentos de la clase (si existen)
            $documentosCreados = 0;
            if ($request->hasFile('documentos')) {
                $documentosCreados = $this->processDocumentsForClass($request->file('documentos'), $miniCourse, $user, $clase->id);

                Log::info('Documentos procesados para clase', [
                    'clase_id' => $clase->id,
                    'documentos_count' => $documentosCreados
                ]);
            }

            // Actualizar duración total del mini curso
            $totalDuration = $miniCourse->modules()->sum('duration');
            $miniCourse->update(['duration' => $totalDuration]);

            DB::commit();

            Log::info('Clase agregada exitosamente', [
                'module_id' => $module->id,
                'clase_id' => $clase->id,
                'documentos_agregados' => $documentosCreados,
                'nueva_duracion_total' => $totalDuration
            ]);

            return response()->json([
                'message' => 'Clase agregada al módulo con éxito.',
                'data' => [
                    'module_id' => $module->id,
                    'mini_course_id' => $miniCourse->id,
                    'nueva_duracion_total' => $totalDuration,
                    'clase' => [
                        'id' => $clase->id,
                        'titulo' => $clase->title,
                        'descripcion' => $clase->description,
                        'duracion' => $clase->duration,
                        'orden' => $clase->order,
                        'documentos_count' => $documentosCreados
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al agregar clases', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'validation_errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al agregar clases al módulo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al agregar las clases.',
                'error' => config('app.debug') ? $th->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Procesar documentos para una clase específica
     */
    private function processDocumentsForClass($documentos, $miniCourse, $user, $classId)
    {
        $documentosCreados = 0;
        
        foreach ($documentos as $docIndex => $documento) {
            if ($documento && $documento->isValid()) {
                $documentPath = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/classes/' . $classId . '/documents/';
                
                Log::info('Subiendo documento de clase a S3', [
                    'clase_id' => $classId,
                    'documento_index' => $docIndex,
                    'original_name' => $documento->getClientOriginalName(),
                    'size_bytes' => $documento->getSize(),
                    'target_path' => $documentPath
                ]);
                
                $uploadResult = $this->uploadFileToS3($documento, $documentPath);
                
                if ($uploadResult['success']) {
                    MiniCourseDocument::create([
                        'mini_course_id' => $miniCourse->id,
                        'mini_course_class_id' => $classId,
                        'document' => Storage::disk('s3')->url($uploadResult['path']),
                    ]);
                    
                    $documentosCreados++;
                    
                    Log::info('Documento de clase guardado exitosamente', [
                        'clase_id' => $classId,
                        'documento_path' => $uploadResult['path']
                    ]);
                } else {
                    throw new \Exception('Error subiendo documento de clase: ' . $uploadResult['error']);
                }
            }
        }
        
        return $documentosCreados;
    }

    /**
     * Eliminar un módulo específico
     */
    public function destroy($miniCourseId, $moduleId)
    {
        try {
            $miniCourse = MiniCourse::where('id', $miniCourseId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado o sin permisos'], 404);
            }
            
            $module = MiniCourseModule::where('mini_course_id', $miniCourseId)
                                       ->where('id', $moduleId)
                                       ->first();
            
            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }
            
            DB::beginTransaction();
            
            // Eliminar archivos de S3
            foreach ($module->classes as $video) {
                $this->deleteFileFromS3($video->video);
            }
            
            $classDocuments = MiniCourseDocument::whereIn('mini_course_class_id', 
                $module->classes->pluck('id'))->get();

            foreach ($classDocuments as $document) {
                $this->deleteFileFromS3($document->document);
            }
            
            // Eliminar el módulo
            $module->delete();
            
            // Actualizar duración total
            $totalDuration = $miniCourse->modules()->sum('duration');
            $miniCourse->update(['duration' => $totalDuration]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Módulo eliminado correctamente.',
                'data' => [
                    'mini_course_id' => $miniCourseId,
                    'new_total_duration' => $totalDuration
                ]
            ], 200);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al eliminar módulo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al eliminar el módulo.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar videos para un módulo específico
     */
    private function processVideosForModule($request, $module, $miniCourse, $user)
    {
        $videos = $request->file('videos');
        $videoTitles = $request->video_titles ?? [];
        $videoDescriptions = $request->video_descriptions ?? [];
        $videoDurations = $request->video_durations ?? [];
        $videoOrders = $request->video_orders ?? [];
        
        foreach ($videos as $videoIndex => $videoFile) {
            if ($videoFile && $videoFile->isValid()) {
                $videoPath = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/videos/';
                $uploadResult = $this->uploadVideoToS3($videoFile, $videoPath);
                
                if ($uploadResult['success']) {
                    MiniCourseClass::create([
                        'mini_course_id' => $miniCourse->id,
                        'mini_course_module_id' => $module->id,
                        'video' => Storage::disk('s3')->url($uploadResult['path']),
                        'title' => $videoTitles[$videoIndex] ?? 'Video ' . ($videoIndex + 1),
                        'description' => $videoDescriptions[$videoIndex] ?? null,
                        'duration' => $videoDurations[$videoIndex] ?? 1,
                        'order' => $videoOrders[$videoIndex] ?? ($videoIndex + 1),
                    ]);
                } else {
                    throw new \Exception('Error subiendo video: ' . $uploadResult['error']);
                }
            }
        }
    }

    /**
     * Procesar videos del módulo
     */
    private function processModuleVideos($request, $moduleIndex, $moduloData, $module, $miniCourse, $user)
    {
        $moduleVideoKey = "modulos.{$moduleIndex}.videos";
        if ($request->hasFile($moduleVideoKey)) {
            $moduleVideos = $request->file($moduleVideoKey);
            $videoTitles = $moduloData['video_titles'] ?? [];
            $videoDescriptions = $moduloData['video_descriptions'] ?? [];
            $videoDurations = $moduloData['video_durations'] ?? [];
            $videoOrders = $moduloData['video_orders'] ?? [];
            
            Log::info('Videos encontrados para módulo', [
                'module_index' => $moduleIndex,
                'module_id' => $module->id,
                'videos_count' => count($moduleVideos)
            ]);
            
            foreach ($moduleVideos as $videoIndex => $videoFile) {
                if ($videoFile && $videoFile->isValid()) {
                    $videoPath = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/videos/';
                    
                    Log::info('Intentando subir video a S3', [
                        'module_id' => $module->id,
                        'video_index' => $videoIndex,
                        'original_name' => $videoFile->getClientOriginalName(),
                        'size_bytes' => $videoFile->getSize(),
                        'target_path' => $videoPath
                    ]);
                    
                    $uploadResult = $this->uploadVideoToS3($videoFile, $videoPath);
                    
                    if ($uploadResult['success']) {
                        MiniCourseClass::create([
                            'mini_course_id' => $miniCourse->id,
                            'mini_course_module_id' => $module->id,
                            'video' => Storage::disk('s3')->url($uploadResult['path']),
                            'title' => $videoTitles[$videoIndex] ?? 'Video ' . ($videoIndex + 1),
                            'description' => $videoDescriptions[$videoIndex] ?? null,
                            'duration' => $videoDurations[$videoIndex] ?? 1,
                            'order' => $videoOrders[$videoIndex] ?? ($videoIndex + 1),
                        ]);
                        
                        Log::info('Video del módulo guardado en S3', [
                            'module_id' => $module->id,
                            'video_index' => $videoIndex,
                            'path' => $uploadResult['path']
                        ]);
                    } else {
                        throw new \Exception('Error subiendo video: ' . $uploadResult['error']);
                    }
                }
            }
        }
    }

    /**
     * Procesar documentos para un módulo específico
     */
    private function processDocumentsForModule($request, $miniCourse, $user, $classId)
    {
        $documentos = $request->file('documentos');

        foreach ($documentos as $docIndex => $document) {
            if ($document && $document->isValid()) {
                $path = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/classes/' . $classId . '/documents/';
                $uploadResult = $this->uploadFileToS3($document, $path);

                if ($uploadResult['success']) {
                    MiniCourseDocument::create([
                        'mini_course_id' => $miniCourse->id,
                        'mini_course_class_id' => $classId, // CAMBIO AQUÍ
                        'document' => Storage::disk('s3')->url($uploadResult['path']),
                    ]);
                } else {
                    throw new \Exception('Error subiendo documento: ' . $uploadResult['error']);
                }
            }
        }
    }
}