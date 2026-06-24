<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\MiniCourse;
use App\Models\MiniCourseImage;
use App\Helpers\CreateNotification;
use App\Models\MiniCourseDistributor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Traits\S3FileTrait;

class MiniCourseController extends Controller
{
    use S3FileTrait;

    public function __construct()
    {
        $this->middleware('can:marketing.tools')->only(['create', 'store']);
        $this->initializeS3Client();
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
     * Almacenar un nuevo mini curso
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        Log::info('Iniciando registro de mini curso básico', ['user_id' => $user->id]);
        
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
            Log::warning('Falló validación al registrar mini curso', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
        
            $miniCourse = MiniCourse::create([
                'user_id' => $user->id,
                'category_id' => $request->categoria,
                'title' => $request->titulo,
                'description' => $request->descripcion,
                'duration' => $request->duracion,
                'level' => $request->nivel,
                'status' => 0, // En desarrollo
            ]);

            Log::info('Mini curso básico guardado con ID ' . $miniCourse->id);
            
            // Procesar imagen si existe
            if ($request->hasFile('imagen')) {
                $image = $request->file('imagen');
                $path = 'mini-courses/' . $user->id . '/' . $miniCourse->id . '/images/';
                
                $uploadResult = $this->uploadFileToS3($image, $path);
                
                if ($uploadResult['success']) {
                    MiniCourseImage::create([
                        'mini_course_id' => $miniCourse->id,
                        'image' => Storage::disk('s3')->url($uploadResult['path']),
                    ]);
                    Log::info('Imagen guardada en S3', ['path' => $uploadResult['path']]);
                }
            }
            
            DB::commit();
            Log::info('Registro de mini curso básico finalizado con éxito');

            CreateNotification::saveNotificationDistributors($user->id, $request->title, 'minicourse');
            
            return response()->json([
                'message' => 'Mini curso creado con éxito. Ahora puedes agregar módulos.',
                'data' => [
                    'mini_course' => $miniCourse,
                    'redirect_url' => route('marketing.tools')
                ],
            ], 201);
        
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al guardar el mini curso', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        
            return response()->json([
                'message' => 'Ocurrió un error al registrar el mini curso.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar mini curso específico
     */
    public function show($id)
    {
        try {
            $miniCourse = MiniCourse::with(['images', 'category', 'modules'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
                
            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado o no tienes permisos para verlo'
                ], 404);
            }

            // Formatear URLs de S3
            $miniCourse->images->each(function ($image) {
                $image->image = $this->generateS3Url($image->image);
            });

            return response()->json([
                'data' => $miniCourse,
                'message' => 'Mini curso obtenido correctamente'
            ], 200);
            
        } catch (\Throwable $th) {
            Log::error('Error al obtener mini curso:', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
            ]);
        
            return response()->json([
                'message' => 'Error al obtener el mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $miniCourse = MiniCourse::with(['images', 'category'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$miniCourse) {
            return redirect()->back()->withErrors('Mini curso no encontrado.');
        }

        $categories = Category::all();
        return view('content.marketing.mini-course.edit', compact('miniCourse', 'categories'));
    }

    /**
     * Actualizar mini curso básico (sin módulos)
     */
    public function update(Request $request, $id)
    {
        $miniCourse = MiniCourse::with('images')->find($id);

        if (!$miniCourse || $miniCourse->user_id !== Auth::id()) {
            return response()->json(['message' => 'Mini curso no encontrado o sin permisos'], 404);
        }

        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'duracion' => 'required|integer|min:1',
                'nivel' => 'required|in:principiante,intermedio,avanzado',
                'categoria' => 'required|exists:categories,id',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            DB::beginTransaction();

            $miniCourse->update([
                'title' => $request->titulo,
                'description' => $request->descripcion,
                'duration' => $request->duracion,
                'level' => $request->nivel,
                'category_id' => $request->categoria,
            ]);

            // Actualizar imagen si se proporciona
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior
                foreach ($miniCourse->images as $image) {
                    $this->deleteFileFromS3($image->image);
                    $image->delete();
                }

                // Subir nueva imagen
                $imageFile = $request->file('imagen');
                $path = 'mini-courses/' . $miniCourse->user_id . '/' . $miniCourse->id . '/images/';
                $uploadResult = $this->uploadFileToS3($imageFile, $path);
                
                if ($uploadResult['success']) {
                    $miniCourse->images()->create([
                        'image' => Storage::disk('s3')->url($uploadResult['path'])
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Mini curso actualizado correctamente',
                'data' => $miniCourse->load('images', 'category')
            ], 200);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al actualizar el mini curso', [
                'id' => $id,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al actualizar el mini curso.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de mini cursos con conteo de módulos
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
            Log::error('Error al obtener resumen de mini cursos', [
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al obtener los mini cursos',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar mini curso completo
     */
    public function destroy($id)
    {
        try {
            $miniCourse = MiniCourse::with(['images', 'documents', 'modules.videos', 'modules.documents', 'videos'])->find($id);

            if (!$miniCourse || $miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Mini curso no encontrado o sin permisos'
                ], 404);
            }

            DB::beginTransaction();

            // Eliminar archivos de S3
            foreach ($miniCourse->images as $image) {
                $this->deleteFileFromS3($image->image);
            }

            foreach ($miniCourse->documents()->whereNull('mini_course_module_id')->get() as $document) {
                $this->deleteFileFromS3($document->document);
            }

            foreach ($miniCourse->videos()->whereNull('mini_course_module_id')->get() as $video) {
                $this->deleteFileFromS3($video->video);
            }

            // Eliminar archivos de módulos
            foreach ($miniCourse->modules as $module) {
                foreach ($module->documents as $document) {
                    $this->deleteFileFromS3($document->document);
                }
                
                foreach ($module->videos as $video) {
                    $this->deleteFileFromS3($video->video);
                }
            }

            // Eliminar registros
            $miniCourse->images()->delete();
            $miniCourse->documents()->delete();
            $miniCourse->videos()->delete();
            $miniCourse->modules()->delete();
            MiniCourseDistributor::where('mini_course_id', $miniCourse->id)->delete();
            $miniCourse->delete();

            DB::commit();

            Log::info('Mini curso eliminado correctamente', ['mini_course_id' => $id]);

            return response()->json([
                'message' => 'Mini curso eliminado correctamente'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error al eliminar mini curso', [
                'mini_course_id' => $id,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al eliminar el mini curso',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar vista de módulos
     */
    public function viewModules($id)
    {
        try {
            $miniCourse = MiniCourse::with(['modules', 'images'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$miniCourse) {
                return redirect()->back()->withErrors('Mini curso no encontrado.');
            }

            // Formatear URLs
            $miniCourse->images->each(fn($img) => $img->image = $this->generateS3Url($img->image));

            return view('content.marketing.mini-course.modules', compact('miniCourse'));
        } catch (\Throwable $th) {
            Log::error('Error al cargar módulos del mini curso', [
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
                'classes.documents' // Agregar esta línea
            ])->where('id', $id)->first();
            
            if (!$miniCourse) {
                return redirect()->back()->withErrors('Mini curso no encontrado.');
            }
        
            // Formatear rutas
            $miniCourse->images->each(fn($img) => $img->image = asset($img->image));
            
            // Ahora los documentos están en las clases
            $miniCourse->classes->each(function($class) {
                $class->video = asset($class->video);
                $class->documents->each(fn($doc) => $doc->document = asset($doc->document));
            });
        
            return view('content.marketing.mini-course.module', compact('miniCourse'));
        } catch (\Throwable $th) {
            \Log::error('Error al cargar módulos del mini curso', [
                'mini_course_id' => $id,
                'error' => $th->getMessage()
            ]);
        
            return redirect()->back()->withErrors('Error al cargar los módulos del mini curso.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            // Validar el estado recibido (ajusta los valores según tu flujo)
            $validated = $request->validate([
                'status' => 'required|in:0,1,2', 
                // 0 = En desarrollo, 1 = Publicado, 2 = Archivado (por ejemplo)
            ]);
        
            $miniCourse = MiniCourse::find($id);
        
            if (!$miniCourse) {
                return response()->json([
                    'message' => 'Mini curso no encontrado'
                ], 404);
            }
        
            // Verificar que el usuario sea el propietario
            if ($miniCourse->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para actualizar el estado de este mini curso'
                ], 403);
            }
        
            // Actualizar solo el estado
            $miniCourse->update([
                'status' => $validated['status']
            ]);
        
            return response()->json([
                'message' => 'Estado del mini curso actualizado correctamente',
                'data' => [
                    'id' => $miniCourse->id,
                    'status' => $miniCourse->status
                ]
            ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            \Log::error('Error al actualizar el estado del mini curso:', [
                'mini_course_id' => $id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
        
            return response()->json([
                'message' => 'Error al actualizar el estado del mini curso',
                'error' => $th->getMessage()
            ], 500);
        }
    }

}