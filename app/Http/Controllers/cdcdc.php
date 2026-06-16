<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\MiniCourse;
use App\Models\MiniCourseImage;
use App\Models\MiniCourseDocument;
use App\Models\MiniCourseModule;
use App\Models\MiniCourseDistributor;
use App\Models\MiniCourseClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\MultipartUploadException;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper; // Asumiendo que tienes este helper

class MiniCourseController extends Controller
{

    private $s3Client;

    public function __construct()
    {
        $this->middleware('can:marketing.tools')->only(['create', 'store']);
        
        // Inicializar cliente S3
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'use_accelerate_endpoint' => true,
        ]);
    }

    /**
     * Subir video a S3 usando MultipartUploader
     */
    private function uploadVideoToS3($videoFile, $path)
    {
        try {
            $filename = Helper::formatFilenameSecond($videoFile->getClientOriginalName());
            $fullPath = $path . $filename;

            $uploader = new MultipartUploader($this->s3Client, $videoFile->getRealPath(), [
                'bucket' => env('AWS_BUCKET'),
                'key' => $fullPath,
                'ACL' => 'public-read',
            ]);

            $result = $uploader->upload();
            
            Log::info('Video subido a S3', ['path' => $fullPath, 'url' => $result['ObjectURL']]);
            
            return [
                'success' => true,
                'path' => $fullPath,
                'filename' => $filename,
                'url' => $result['ObjectURL']
            ];
        } catch (MultipartUploadException $e) {
            Log::error('Error subiendo video a S3', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Subir archivo genérico a S3
     */
    private function uploadFileToS3($file, $path)
    {
        try {
            $filename = Helper::formatFilenameSecond($file->getClientOriginalName());
            $fullPath = $path . $filename;

            $result = Storage::disk('s3')->put($fullPath, file_get_contents($file->getRealPath()), 'public');
            
            if ($result) {
                Log::info('Archivo subido a S3', ['path' => $fullPath]);
                return [
                    'success' => true,
                    'path' => $fullPath,
                    'filename' => $filename
                ];
            }
            
            return ['success' => false, 'error' => 'No se pudo subir el archivo'];
        } catch (\Exception $e) {
            Log::error('Error subiendo archivo a S3', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar archivo de S3
     */
    private function deleteFileFromS3($path)
    {
        try {
            Storage::disk('s3')->delete($path);
            Log::info('Archivo eliminado de S3', ['path' => $path]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error eliminando archivo de S3', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generar URL de S3
     */
    private function generateS3Url($path)
    {
        return env('STORAGE_DOMAIN') . '/' . $path;
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $categories = Category::all();
        return view('content.marketing.mini-course.create', compact('categories'));
    }

    /**
     * Mostrar formulario para agregar módulos a un mini curso existente
     */
    public function addModule($id)
    {
        try {
            $miniCourse = MiniCourse::with('category')->find($id);

            if (!$miniCourse) {
                return redirect()->back()->withErrors('Mini curso no encontrado.');
            }

            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return redirect()->back()->withErrors('No tienes permisos para agregar módulos a este mini curso.');
            }

            return view('content.marketing.mini-course.add-modules', compact('miniCourse'));

        } catch (\Throwable $th) {
            \Log::error('Error al cargar formulario de agregar módulo:', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return redirect()->back()->withErrors('Error al cargar el formulario.');
        }
    }

    /**
     * Crear un módulo básico (solo título)
     */
    public function storeBasicModule(Request $request, $id)
    {
        $user = Auth::user();
        \Log::info('Creando módulo básico', [
            'user_id' => $user->id,
            'mini_course_id' => $id
        ]);
        
        try {
            $miniCourse = MiniCourse::find($id);
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado'], 404);
            }
            
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para agregar módulos a este mini curso'
                ], 403);
            }
            
            $validated = $request->validate([
                'titulo' => 'required|string|max:255'
            ]);
            
            DB::beginTransaction();
            
            $module = MiniCourseModule::create([
                'mini_course_id' => $miniCourse->id,
                'title' => $request->titulo,
                'content' => 'Contenido por definir', // Contenido por defecto
                'duration' => 1, // Duración por defecto
            ]);
            
            \Log::info('Módulo básico creado', ['module_id' => $module->id]);
            
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
            \Log::error('Error al crear módulo básico', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al crear el módulo.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Agregar clases (videos/documentos) a un módulo específico
     */
    public function addClassesToModule(Request $request, $miniCourseId, $moduleId)
    {
        $user = Auth::user();
        \Log::info('Agregando clases a módulo', [
            'user_id' => $user->id,
            'mini_course_id' => $miniCourseId,
            'module_id' => $moduleId
        ]);
        
        try {
            $miniCourse = MiniCourse::find($miniCourseId);
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado'], 404);
            }
            
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para modificar este mini curso'
                ], 403);
            }
            
            $module = MiniCourseModule::where('mini_course_id', $miniCourseId)
                                       ->where('id', $moduleId)
                                       ->first();
            
            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }
            
            $validated = $request->validate([
                // Actualizar información del módulo
                'contenido' => 'nullable|string',
                'duracion' => 'nullable|integer|min:1',
                
                // Videos de la clase
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
                
                // Documentos de la clase
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
            
            // Procesar videos si existen - SUBIRLOS A S3
            if ($request->hasFile('videos')) {
                $videos = $request->file('videos');
                $videoTitles = $request->video_titles ?? [];
                $videoDescriptions = $request->video_descriptions ?? [];
                $videoDurations = $request->video_durations ?? [];
                $videoOrders = $request->video_orders ?? [];
                
                foreach ($videos as $videoIndex => $videoFile) {
                    if ($videoFile && $videoFile->isValid()) {
                        $videoPath = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/videos/';

                        \Log::info('Intentando subir video a S3', [
                            'module_id' => $module->id,
                            'video_index' => $videoIndex,
                            'original_name' => $videoFile->getClientOriginalName(),
                            'size_bytes' => $videoFile->getSize(),
                            'target_path' => $videoPath
                        ]);
                        
                        $uploadResult = $this->uploadVideoToS3($videoFile, $videoPath);
                        
                        \Log::info('Resultado subida video S3', [
                            'success' => $uploadResult['success'] ?? false,
                            'path' => $uploadResult['path'] ?? null,
                            'error' => $uploadResult['error'] ?? null
                        ]);

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
                            
                            \Log::info('Video agregado al módulo en S3', [
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
            
            // Procesar documentos si existen - SUBIRLOS A S3
            if ($request->hasFile('documentos')) {
                $documentos = $request->file('documentos');
                
                foreach ($documentos as $docIndex => $document) {
                    if ($document && $document->isValid()) {
                        $path = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/documents/';
                        
                        $uploadResult = $this->uploadFileToS3($document, $path);
                        
                        if ($uploadResult['success']) {
                            MiniCourseDocument::create([
                                'mini_course_id' => $miniCourse->id,
                                'mini_course_module_id' => $module->id,
                                'document' => Storage::disk('s3')->url($uploadResult['path']), // URL completa
                            ]);
                            
                            \Log::info('Documento agregado al módulo en S3', [
                                'module_id' => $module->id,
                                'document_index' => $docIndex,
                                'path' => $uploadResult['path']
                            ]);
                        } else {
                            throw new \Exception('Error subiendo documento: ' . $uploadResult['error']);
                        }
                    }
                }
            }
            
            // Actualizar la duración total del mini curso
            $totalDuration = $miniCourse->modules()->sum('duration');
            $miniCourse->update(['duration' => $totalDuration]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Clases agregadas al módulo con éxito.',
                'data' => [
                    'module_id' => $module->id,
                    'mini_course_id' => $miniCourse->id,
                    'new_total_duration' => $totalDuration
                ]
            ], 200);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al agregar clases al módulo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al agregar las clases.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un módulo específico
     */
    public function updateModule(Request $request, $miniCourseId, $moduleId)
    {
        try {
            $miniCourse = MiniCourse::find($miniCourseId);
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado'], 404);
            }
            
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para modificar este mini curso'
                ], 403);
            }
            
            $module = MiniCourseModule::where('mini_course_id', $miniCourseId)
                                       ->where('id', $moduleId)
                                       ->first();
            
            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }
            
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'contenido' => 'required|string',
                'duracion' => 'required|integer|min:1',
            ]);
            
            $module->update([
                'title' => $request->titulo,
                'content' => $request->contenido,
                'duration' => $request->duracion,
            ]);
            
            // Actualizar la duración total del mini curso
            $totalDuration = $miniCourse->modules()->sum('duration');
            $miniCourse->update(['duration' => $totalDuration]);
            
            return response()->json([
                'message' => 'Módulo actualizado con éxito.',
                'data' => [
                    'module' => $module,
                    'new_total_duration' => $totalDuration
                ]
            ], 200);
            
        } catch (\Throwable $th) {
            \Log::error('Error al actualizar módulo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el módulo.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar mini curso con URLs de S3
     */
    public function show($id)
    {
        try {
            $miniCourse = MiniCourse::with('images', 'documents', 'modules.videos', 'videos')
                ->join('categories', 'mini_courses.category_id', '=', 'categories.id')
                ->select('mini_courses.*', 'categories.name as category_name')
                ->where('mini_courses.id', $id)
                ->first();
                
            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado'
                ], 404);
            }
        
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para ver este mini curso'
                ], 403);
            }
        
            // Formatear URLs de S3 para imágenes y documentos
            $miniCourse->images->each(function ($image) {
                $image->image = $this->generateS3Url($image->image);
            });
            $miniCourse->documents->each(function ($document) {
                $document->document = $this->generateS3Url($document->document);
            });
            
            // Formatear URLs de S3 para videos generales
            $miniCourse->videos->each(function ($video) {
                $video->video = $this->generateS3Url($video->video);
            });

            // Formatear URLs de S3 para videos por módulo
            $miniCourse->modules->each(function ($module) {
                if ($module->videos) {
                    $module->videos->each(function ($video) {
                        $video->video = $this->generateS3Url($video->video);
                    });
                }
            });
        
            return response()->json([
                'data' => $miniCourse,
                'message' => 'Mini curso obtenido correctamente'
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Error al obtener mini curso:', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        
            return response()->json([
                'message' => 'Error al obtener el mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Almacenar un nuevo mini curso
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        \Log::info('Iniciando registro de mini curso básico', ['user_id' => $user->id]);
        
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'duracion' => 'required|integer|min:1',
                'nivel' => 'required|in:principiante,intermedio,avanzado',
                'categoria' => 'required|exists:categories,id',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Falló validación al registrar mini curso', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }
    
        try {
            DB::beginTransaction();
            \Log::info('Creando instancia de MiniCourse');
        
            $miniCourse = new MiniCourse();
            $miniCourse->user_id = $user->id;
            $miniCourse->category_id = $request->categoria;
            $miniCourse->title = $request->titulo;
            $miniCourse->description = $request->descripcion;
            $miniCourse->duration = $request->duracion;
            $miniCourse->level = $request->nivel;
            $miniCourse->status = 0; // 0 = En desarrollo, 1 = Publicado
        
            if ($miniCourse->save()) {
                \Log::info('Mini curso básico guardado con ID ' . $miniCourse->id);
            
                // Procesar imagen si existe
                if ($request->hasFile('imagen')) {
                    $image = $request->file('imagen');
                    $path = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/images/';
                    $storedPath = $image->store($path, 'public');
                
                    MiniCourseImage::create([
                        'mini_course_id' => $miniCourse->id,
                        'image' => 'storage/' . $storedPath,
                    ]);
                    \Log::info('Imagen guardada', ['path' => $storedPath]);
                }
            
                DB::commit();
                \Log::info('Registro de mini curso básico finalizado con éxito');
            
                return response()->json([
                    'message' => 'Mini curso creado con éxito. Ahora puedes agregar módulos.',
                    'data' => [
                        'mini_course' => $miniCourse,
                        'redirect_url' => route('marketing.mini-course.add-module', $miniCourse->id)
                    ],
                ], 201);
            }
        
            throw new \Exception('No se pudo guardar el mini curso.');
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al guardar el mini curso', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ]);
        
            return response()->json([
                'message' => 'Ocurrió un error al registrar el mini curso.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Almacenar nuevos módulos para un mini curso existente
     */
    public function storeModules(Request $request, $id)
    {
        $user = Auth::user();
        \Log::info('Iniciando agregado de módulos', [
            'user_id' => $user->id,
            'mini_course_id' => $id
        ]);
        
        try {
            $miniCourse = MiniCourse::find($id);
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado'], 404);
            }
            
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para agregar módulos a este mini curso'
                ], 403);
            }
            
            $validated = $request->validate([
                'modulos' => 'required|array|min:1',
                'modulos.*.titulo' => 'required|string|max:255',
                'modulos.*.contenido' => 'required|string',
                'modulos.*.duracion' => 'required|integer|min:1',
                
                // Documentos opcionales por módulo
                'modulos.*.documentos' => 'nullable|array',
                'modulos.*.documentos.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
                
                // Videos opcionales por módulo
                'modulos.*.videos' => 'nullable|array',
                'modulos.*.videos.*' => 'nullable|file|mimes:mp4,mov,avi,webm|max:512000',
                'modulos.*.video_titles' => 'nullable|array',
                'modulos.*.video_titles.*' => 'nullable|string|max:255',
                'modulos.*.video_descriptions' => 'nullable|array',
                'modulos.*.video_descriptions.*' => 'nullable|string',
                'modulos.*.video_durations' => 'nullable|array',
                'modulos.*.video_durations.*' => 'nullable|integer|min:1',
                'modulos.*.video_orders' => 'nullable|array',
                'modulos.*.video_orders.*' => 'nullable|integer|min:1',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Falló validación al agregar módulos', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $modulosData = $request->input('modulos', []);
            $modulosCreados = [];
            
            foreach ($modulosData as $moduleIndex => $moduloData) {
                \Log::info('Procesando nuevo módulo', [
                    'index' => $moduleIndex,
                    'titulo' => $moduloData['titulo'] ?? 'Sin título',
                    'mini_course_id' => $id
                ]);
                
                $module = MiniCourseModule::create([
                    'mini_course_id' => $miniCourse->id,
                    'title' => $moduloData['titulo'],
                    'content' => $moduloData['contenido'],
                    'duration' => $moduloData['duracion'],
                ]);
                
                \Log::info('Módulo creado', ['module_id' => $module->id]);
                $modulosCreados[] = $module;
                
                // Procesar documentos del módulo
                $moduleDocumentKey = "modulos.{$moduleIndex}.documentos";
                if ($request->hasFile($moduleDocumentKey)) {
                    $moduleDocuments = $request->file($moduleDocumentKey);
                    \Log::info('Documentos encontrados para módulo', [
                        'module_index' => $moduleIndex,
                        'module_id' => $module->id,
                        'documents_count' => count($moduleDocuments)
                    ]);
                    
                    foreach ($moduleDocuments as $docIndex => $document) {
                        if ($document && $document->isValid()) {
                            $path = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/documents/';
                            $storedPath = $document->store($path, 'public');
                        
                            $documentRecord = MiniCourseDocument::create([
                                'mini_course_id' => $miniCourse->id,
                                'mini_course_module_id' => $module->id,
                                'document' => 'storage/' . $storedPath,
                            ]);
                            
                            \Log::info('Documento del módulo guardado', [
                                'module_id' => $module->id,
                                'document_id' => $documentRecord->id,
                                'path' => $storedPath
                            ]);
                        }
                    }
                }
                
                // Procesar videos del módulo
                $moduleVideoKey = "modulos.{$moduleIndex}.videos";
                if ($request->hasFile($moduleVideoKey)) {
                    $moduleVideos = $request->file($moduleVideoKey);
                    $videoTitles = $moduloData['video_titles'] ?? [];
                    $videoDescriptions = $moduloData['video_descriptions'] ?? [];
                    $videoDurations = $moduloData['video_durations'] ?? [];
                    $videoOrders = $moduloData['video_orders'] ?? [];
                    
                    \Log::info('Videos encontrados para módulo', [
                        'module_index' => $moduleIndex,
                        'module_id' => $module->id,
                        'videos_count' => count($moduleVideos)
                    ]);
                    
                    foreach ($moduleVideos as $videoIndex => $videoFile) {
                        if ($videoFile && $videoFile->isValid()) {
                            $videoPath = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/modules/' . $module->id . '/videos/';
                            $storedVideoPath = $videoFile->store($videoPath, 'public');
                        
                            $videoRecord = MiniCourseClass::create([
                                'mini_course_id' => $miniCourse->id,
                                'mini_course_module_id' => $module->id,
                                'video' => 'storage/' . $storedVideoPath,
                                'title' => $videoTitles[$videoIndex] ?? 'Video ' . ($videoIndex + 1),
                                'description' => $videoDescriptions[$videoIndex] ?? null,
                                'duration' => $videoDurations[$videoIndex] ?? 1,
                                'order' => $videoOrders[$videoIndex] ?? ($videoIndex + 1),
                            ]);
                            
                            \Log::info('Video del módulo guardado', [
                                'module_id' => $module->id,
                                'video_id' => $videoRecord->id,
                                'video_index' => $videoIndex,
                                'path' => $storedVideoPath,
                                'title' => $videoTitles[$videoIndex] ?? 'Video ' . ($videoIndex + 1)
                            ]);
                        }
                    }
                }
            }
            
            // Actualizar la duración total del mini curso (opcional)
            $totalDuration = $miniCourse->modules()->sum('duration');
            $miniCourse->update(['duration' => $totalDuration]);
            
            DB::commit();
            \Log::info('Agregado de módulos finalizado con éxito', [
                'mini_course_id' => $id,
                'modules_added' => count($modulosCreados)
            ]);
            
            return response()->json([
                'message' => 'Módulos agregados con éxito.',
                'data' => [
                    'mini_course_id' => $miniCourse->id,
                    'modules_added' => count($modulosCreados),
                    'new_total_duration' => $totalDuration
                ],
            ], 200);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al agregar módulos', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al agregar los módulos.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener solo la lista de mini cursos con resumen de módulos
     */
    public function getMiniCoursesWithModulesSummary()
    {
        try {
            $user = Auth::user();

            $miniCourses = MiniCourse::withCount('modules')
                ->with('category')
                ->where('user_id', $user->id)
                ->get();

            if ($miniCourses->isEmpty()) {
                return response()->json([
                    'message' => 'No tienes mini cursos creados',
                    'data' => []
                ], 200);
            }

            $result = $miniCourses->map(function ($miniCourse) {
                return [
                    'mini_course_id' => $miniCourse->id,
                    'title' => $miniCourse->title,
                    'description' => $miniCourse->description,
                    'level' => $miniCourse->level,
                    'duration' => $miniCourse->duration,
                    'status' => $miniCourse->status,
                    'category' => $miniCourse->category ? $miniCourse->category->name : null,
                    'modules_count' => $miniCourse->modules_count,
                    'created_at' => $miniCourse->created_at,
                    'updated_at' => $miniCourse->updated_at
                ];
            });

            return response()->json([
                'message' => 'Mini cursos obtenidos correctamente',
                'data' => $result,
                'total_courses' => $miniCourses->count()
            ], 200);

        } catch (\Throwable $th) {
            \Log::error('Error al obtener resumen de mini cursos', [
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return response()->json([
                'message' => 'Error al obtener los mini cursos',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener módulos de un mini curso específico del usuario
     */
    public function getModulesByMiniCourse($miniCourseId)
    {
        try {
            $user = Auth::user();
            
            $miniCourse = MiniCourse::with([
                'modules.videos',
                'modules.documents',
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
                        'videos_count' => $module->videos->count(),
                        'documents_count' => $module->documents->count(),
                        'videos' => $module->videos->sortBy('order')->map(function ($video) {
                            return [
                                'video_id' => $video->id,
                                'title' => $video->title,
                                'description' => $video->description,
                                'duration' => $video->duration,
                                'order' => $video->order,
                                'video_url' => asset($video->video),
                                'created_at' => $video->created_at
                            ];
                        })->values(),
                        'documents' => $module->documents->map(function ($document) {
                            return [
                                'document_id' => $document->id,
                                'document_url' => asset($document->document),
                                'created_at' => $document->created_at
                            ];
                        })
                    ];
                })
            ];
        
            return response()->json([
                'message' => 'Módulos del mini curso obtenidos correctamente',
                'data' => $result
            ], 200);
        
        } catch (\Throwable $th) {
            \Log::error('Error al obtener módulos del mini curso específico', [
                'user_id' => Auth::id(),
                'mini_course_id' => $miniCourseId,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        
            return response()->json([
                'message' => 'Error al obtener los módulos del mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Eliminar un módulo específico y sus archivos de S3
     */
    public function deleteModule($miniCourseId, $moduleId)
    {
        try {
            $miniCourse = MiniCourse::find($miniCourseId);
            
            if (!$miniCourse) {
                return response()->json(['message' => 'Mini curso no encontrado'], 404);
            }
            
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para eliminar módulos de este mini curso'
                ], 403);
            }
            
            $module = MiniCourseModule::where('mini_course_id', $miniCourseId)
                                       ->where('id', $moduleId)
                                       ->first();
            
            if (!$module) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }
            
            DB::beginTransaction();
            
            // Eliminar archivos de S3
            foreach ($module->videos as $video) {
                $this->deleteFileFromS3($video->video);
            }
            
            foreach ($module->documents as $document) {
                $this->deleteFileFromS3($document->document);
            }
            
            // Eliminar el módulo (esto también eliminará las relaciones por cascada)
            $module->delete();
            
            // Actualizar duración total del mini curso
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
            \Log::error('Error al eliminar módulo', [
                'mini_course_id' => $miniCourseId,
                'module_id' => $moduleId,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al eliminar el módulo.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un mini curso
     */
    public function update(Request $request, $id)
    {
        $miniCourse = MiniCourse::with('images', 'documents', 'modules.videos', 'videos')->find($id);

        if (!$miniCourse) {
            return response()->json(['message' => 'Mini curso no encontrado'], 404);
        }

        // Verificar que el usuario sea el propietario
        if ($miniCourse->user_id !== Auth::id()) {
            return response()->json(['message' => 'No tienes permisos para editar este mini curso'], 403);
        }

        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'duracion' => 'required|integer|min:1',
                'nivel' => 'required|in:principiante,intermedio,avanzado',
                'categoria' => 'required|exists:categories,id',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'modulos' => 'required|array|min:1',
                'modulos.*.titulo' => 'required|string|max:255',
                'modulos.*.contenido' => 'required|string',
                'modulos.*.duracion' => 'required|integer|min:1',
                'modulos.*.documentos' => 'nullable|array',
                'modulos.*.documentos.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
                // NUEVA: Validación para videos por módulo en actualización
                'modulos.*.videos' => 'nullable|array',
                'modulos.*.videos.*' => 'nullable|file|mimes:mp4,mov,avi,webm|max:512000',
                'modulos.*.video_titles' => 'nullable|array',
                'modulos.*.video_descriptions' => 'nullable|array',
                'modulos.*.video_durations' => 'nullable|array',
                'modulos.*.video_orders' => 'nullable|array',
                'documentos.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Falló validación al actualizar mini curso', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $miniCourse->update([
                'title' => $request->titulo,
                'description' => $request->descripcion,
                'duration' => $request->duracion,
                'level' => $request->nivel,
                'category_id' => $request->categoria,
            ]);

            // Reemplazar imagen
            if ($request->hasFile('imagen')) {
                foreach ($miniCourse->images as $image) {
                    $imagePath = str_replace('storage/', '', $image->image);
                    if (Storage::disk('public')->exists($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                    $image->delete();
                }

                $imageFile = $request->file('imagen');
                $path = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id . '/images/';
                $storedPath = $imageFile->store($path, 'public');
                $miniCourse->images()->create(['image' => 'storage/' . $storedPath]);
            }

            // Reemplazar documentos globales
            if ($request->hasFile('documentos')) {
                foreach ($miniCourse->documents()->whereNull('mini_course_module_id')->get() as $document) {
                    $docPath = str_replace('storage/', '', $document->document);
                    if (Storage::disk('public')->exists($docPath)) {
                        Storage::disk('public')->delete($docPath);
                    }
                    $document->delete();
                }

                foreach ($request->file('documentos') as $document) {
                    $path = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id . '/documents/';
                    $storedPath = $document->store($path, 'public');
                    $miniCourse->documents()->create(['document' => 'storage/' . $storedPath]);
                }
            }

            // Reemplazar videos globales
            if ($request->hasFile('videos')) {
                foreach ($miniCourse->videos()->whereNull('mini_course_module_id')->get() as $video) {
                    $videoPath = str_replace('storage/', '', $video->video);
                    if (Storage::disk('public')->exists($videoPath)) {
                        Storage::disk('public')->delete($videoPath);
                    }
                    $video->delete();
                }
            
                foreach ($request->file('videos') as $index => $videoFile) {
                    $videoPath = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id . '/videos/';
                    $storedVideo = $videoFile->store($videoPath, 'public');
                
                    MiniCourseClass::create([
                        'mini_course_id' => $miniCourse->id,
                        'mini_course_module_id' => null, // Video global
                        'video' => 'storage/' . $storedVideo,
                        'title' => $request->video_titles[$index] ?? 'Video ' . ($index + 1),
                        'description' => $request->video_descriptions[$index] ?? null,
                        'duration' => $request->video_durations[$index] ?? 1,
                        'order' => $request->video_orders[$index] ?? ($index + 1),
                    ]);
                }
            }

            // Eliminar módulos existentes y sus archivos asociados
            foreach ($miniCourse->modules as $module) {
                // Eliminar videos del módulo
                foreach ($module->videos as $video) {
                    $videoPath = str_replace('storage/', '', $video->video);
                    if (Storage::disk('public')->exists($videoPath)) {
                        Storage::disk('public')->delete($videoPath);
                    }
                }
                
                // Eliminar documentos del módulo
                foreach ($module->documents as $document) {
                    $docPath = str_replace('storage/', '', $document->document);
                    if (Storage::disk('public')->exists($docPath)) {
                        Storage::disk('public')->delete($docPath);
                    }
                }
            }
            
            // Eliminar todos los módulos y sus relaciones
            $miniCourse->modules()->delete();

            // Crear nuevos módulos con sus archivos
            foreach ($request->modulos as $moduleIndex => $moduloData) {
                $module = MiniCourseModule::create([
                    'mini_course_id' => $miniCourse->id,
                    'title' => $moduloData['titulo'],
                    'content' => $moduloData['contenido'],
                    'duration' => $moduloData['duracion'],
                ]);

                // Procesar documentos del módulo si existen
                if (isset($moduloData['documentos']) && is_array($moduloData['documentos'])) {
                    foreach ($moduloData['documentos'] as $document) {
                        if ($document && is_file($document)) {
                            $path = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id . '/modules/' . $module->id . '/documents/';
                            $storedPath = $document->store($path, 'public');
                            
                            MiniCourseDocument::create([
                                'mini_course_id' => $miniCourse->id,
                                'mini_course_module_id' => $module->id,
                                'document' => 'storage/' . $storedPath,
                            ]);
                        }
                    }
                }

                // NUEVA: Procesar videos del módulo si existen
                if (isset($moduloData['videos']) && is_array($moduloData['videos'])) {
                    $videoTitles = $moduloData['video_titles'] ?? [];
                    $videoDescriptions = $moduloData['video_descriptions'] ?? [];
                    $videoDurations = $moduloData['video_durations'] ?? [];
                    $videoOrders = $moduloData['video_orders'] ?? [];
                    
                    foreach ($moduloData['videos'] as $videoIndex => $videoFile) {
                        if ($videoFile && is_file($videoFile)) {
                            $videoPath = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id . '/modules/' . $module->id . '/videos/';
                            $storedVideoPath = $videoFile->store($videoPath, 'public');
                            
                            MiniCourseClass::create([
                                'mini_course_id' => $miniCourse->id,
                                'mini_course_module_id' => $module->id,
                                'video' => 'storage/' . $storedVideoPath,
                                'title' => $videoTitles[$videoIndex] ?? 'Video ' . ($videoIndex + 1),
                                'description' => $videoDescriptions[$videoIndex] ?? null,
                                'duration' => $videoDurations[$videoIndex] ?? 1,
                                'order' => $videoOrders[$videoIndex] ?? ($videoIndex + 1),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Cargar datos actualizados con las relaciones
            $updated = $miniCourse->load('images', 'documents', 'modules.videos', 'modules.documents', 'videos');
            $updated->images->each(fn($img) => $img->image = asset($img->image));
            $updated->documents->each(fn($doc) => $doc->document = asset($doc->document));
            $updated->videos->each(fn($vid) => $vid->video = asset($vid->video));
            
            // Formatear URLs de archivos de módulos
            $updated->modules->each(function ($module) {
                if ($module->videos) {
                    $module->videos->each(fn($vid) => $vid->video = asset($vid->video));
                }
                if ($module->documents) {
                    $module->documents->each(fn($doc) => $doc->document = asset($doc->document));
                }
            });

            return response()->json([
                'message' => 'Mini curso actualizado correctamente',
                'data' => $updated
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al actualizar el mini curso', [
                'id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al actualizar el mini curso.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Crear enlace de invitación
     */
    public function createInvitationLink($id)
    {
        $user = Auth::user();
        $random = Str::random(10);
        $code = $user->id . $random;

        $invitation = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->first();

        if ($invitation) {
            $invitation->update([
                'code' => $code,
                'expires_at' => now()->addDays(7),
            ]);
        }

        return response()->json([
            'link' => url("/mini-course/register?invitation_code={$code}"),
        ]);
    }

    /**
     * Verificar invitación existente
     */
    public function checkInvitation($id)
    {
        $user = Auth::user();

        $existInvitation = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->where('code', '!=', 0)
            ->exists();

        $data = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->where('code', '!=', 0)
            ->first();

        $invitationLink = $data 
            ? url("/mini-course/register?invitation_code={$data->code}")
            : null;

        return response()->json([
            'existInvitation' => $existInvitation,
            'invitationLink' => $invitationLink
        ]);
    }

    /**
     * Comprar mini curso
     */
    public function purchase($id)
    {
        $user = auth()->user();
        $mini_course = MiniCourse::find($id);
        
        if (!$mini_course) {
            return response()->json(['message' => 'Mini Curso no encontrado'], 404);
        }

        $alreadyPurchased = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->exists();

        if ($alreadyPurchased) {
            return response()->json([
                'message' => 'Ya has comprado este mini curso',
                'isPurchased' => true
            ], 200);
        }

        MiniCourseDistributor::create([
            'user_id' => $user->id,
            'mini_course_id' => $id,
            'code' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Mini Curso comprado exitosamente',
            'isPurchased' => true
        ], 200);
    }

    /**
     * Verificar compra
     */
    public function checkPurchase($id)
    {
        $user = auth()->user();
        $mini_course = MiniCourse::find($id);
        
        if (!$mini_course) {
            return response()->json(['message' => 'Mini Curso no encontrado'], 404);
        }

        $hasPurchased = MiniCourseDistributor::where('user_id', $user->id)
            ->where('mini_course_id', $id)
            ->exists();

        return response()->json([
            'isPurchased' => $hasPurchased
        ]);
    }

    /**
     * Eliminar un mini curso
     */
    public function destroy($id)
    {
        try {
            $miniCourse = MiniCourse::with(['images', 'documents', 'modules.videos', 'modules.documents', 'videos'])->find($id);

            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado'
                ], 404);
            }

            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para eliminar este mini curso'
                ], 403);
            }

            DB::beginTransaction();

            // Eliminar archivos físicos de imágenes
            foreach ($miniCourse->images as $image) {
                $imagePath = str_replace('storage/', '', $image->image);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                    \Log::info('Imagen eliminada', ['path' => $imagePath]);
                }
            }

            // Eliminar archivos físicos de documentos globales
            foreach ($miniCourse->documents()->whereNull('mini_course_module_id')->get() as $document) {
                $docPath = str_replace('storage/', '', $document->document);
                if (Storage::disk('public')->exists($docPath)) {
                    Storage::disk('public')->delete($docPath);
                    \Log::info('Documento global eliminado', ['path' => $docPath]);
                }
            }

            // Eliminar archivos físicos de videos globales
            foreach ($miniCourse->videos()->whereNull('mini_course_module_id')->get() as $video) {
                $videoPath = str_replace('storage/', '', $video->video);
                if (Storage::disk('public')->exists($videoPath)) {
                    Storage::disk('public')->delete($videoPath);
                    \Log::info('Video global eliminado', ['path' => $videoPath]);
                }
            }

            // Eliminar archivos físicos de módulos (documentos y videos)
            foreach ($miniCourse->modules as $module) {
                // Eliminar documentos del módulo
                foreach ($module->documents as $document) {
                    $docPath = str_replace('storage/', '', $document->document);
                    if (Storage::disk('public')->exists($docPath)) {
                        Storage::disk('public')->delete($docPath);
                        \Log::info('Documento del módulo eliminado', ['module_id' => $module->id, 'path' => $docPath]);
                    }
                }
                
                // Eliminar videos del módulo
                foreach ($module->videos as $video) {
                    $videoPath = str_replace('storage/', '', $video->video);
                    if (Storage::disk('public')->exists($videoPath)) {
                        Storage::disk('public')->delete($videoPath);
                        \Log::info('Video del módulo eliminado', ['module_id' => $module->id, 'path' => $videoPath]);
                    }
                }
            }

            // Eliminar carpeta completa del mini curso si existe
            $courseFolder = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id;
            if (Storage::disk('public')->exists($courseFolder)) {
                Storage::disk('public')->deleteDirectory($courseFolder);
                \Log::info('Carpeta del mini curso eliminada', ['path' => $courseFolder]);
            }

            // Eliminar registros de la base de datos
            $miniCourse->images()->delete();
            $miniCourse->documents()->delete();
            $miniCourse->videos()->delete(); // Elimina tanto videos globales como de módulos
            $miniCourse->modules()->delete(); // Esto también eliminará las relaciones en cascada

            // Eliminar distribuidores/invitaciones relacionadas
            MiniCourseDistributor::where('mini_course_id', $miniCourse->id)->delete();

            // Finalmente eliminar el mini curso
            $miniCourse->delete();

            DB::commit();

            \Log::info('Mini curso eliminado correctamente', ['mini_course_id' => $id]);

            return response()->json([
                'message' => 'Mini curso eliminado correctamente'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            \Log::error('Error al eliminar mini curso', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return response()->json([
                'message' => 'Error al eliminar el mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar todos los módulos, documentos, imágenes y videos del mini curso
     */
    public function viewModules($id)
    {
        \Log::info("ID recibido: $id");
        try {
            $miniCourse = MiniCourse::with([
                'modules',
                'images',
                'documents',
                'videos'
            ])->where('id', $id)->first();

            if (!$miniCourse) {
                return redirect()->back()->withErrors('Mini curso no encontrado.');
            }

            // Formatear rutas
            $miniCourse->images->each(fn($img) => $img->image = asset($img->image));
            $miniCourse->documents->each(fn($doc) => $doc->document = asset($doc->document));
            $miniCourse->videos->each(fn($vid) => $vid->video = asset($vid->video));

            return view('content.marketing.mini-course.modules', compact('miniCourse'));
        } catch (\Throwable $th) {
            \Log::error('Error al cargar módulos del mini curso', [
                'mini_course_id' => $id,
                'error' => $th->getMessage()
            ]);

            return redirect()->back()->withErrors('Error al cargar los módulos del mini curso.');
        }
    }

        /**
     * Mostrar todos los módulos, documentos, imágenes y videos del mini curso
     */
    public function viewMiniCourse($id)
    {
        \Log::info("ID recibido: $id");
        try {
            $miniCourse = MiniCourse::with([
                'modules',
                'images',
                'documents',
                'videos'
            ])->where('id', $id)->first();

            if (!$miniCourse) {
                return redirect()->back()->withErrors('Mini curso no encontrado.');
            }

            // Formatear rutas
            $miniCourse->images->each(fn($img) => $img->image = asset($img->image));
            $miniCourse->documents->each(fn($doc) => $doc->document = asset($doc->document));
            $miniCourse->videos->each(fn($vid) => $vid->video = asset($vid->video));

            return view('content.marketing.mini-course.module', compact('miniCourse'));
        } catch (\Throwable $th) {
            \Log::error('Error al cargar módulos del mini curso', [
                'mini_course_id' => $id,
                'error' => $th->getMessage()
            ]);

            return redirect()->back()->withErrors('Error al cargar los módulos del mini curso.');
        }
    }   
}