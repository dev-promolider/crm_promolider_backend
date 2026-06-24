<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use App\Models\User;
use App\Models\Badge;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use App\Models\Course;
use App\Models\Module;
use App\Helpers\Helper;
use App\Http\Requests\Infoproduct\Book\StoreBookFileRequest;
use App\Models\Category;
use App\Models\BadgeDetail;
use App\Models\CourseLevel;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\PurchasedCourse;
use App\Models\CourseObservation;
use App\Models\UserConfiguration;
use App\Traits\CourseProcessTrait;
use Illuminate\Support\Facades\DB;
use App\Models\CourseConfiguration;
use App\Models\Infoproduct\Book\BookFile;
use App\Services\Infoproduct\Book\StoreBookFileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\PHPMailerService; // ✅ AGREGADO: PHPMailerService para emails


class CourseController extends Controller {

    public function __construct(private StoreBookFileService $storeBookFileService)
    {
        $this->middleware('can:courses.create')->only('create');
        $this->middleware('can:courses.index')->only('index', 'listCoursesProd');
        $this->middleware('can:courses.edit')->only('edit');
    }

    /**
     * Método legacy para compatibilidad con la ruta /store-course
     * Replica la lógica de creación de curso (store)
     */
    public function storeCourse(Request $request)
    {
        // --- Lógica idéntica al método store (creación de curso) ---
        try {
            DB::beginTransaction();
            // Log de datos del formulario
            Log::info('Datos del formulario recibidos:', [
                'title' => $request->title,
                'id_categories' => $request->id_categories,
                'id_level' => $request->id_level,
                'price' => $request->price,
                'price_base' => $request->price_base,
                'months' => $request->months,
                'certificate' => $request->certificate,
                'is_free' => $request->is_free ?? 'no definido',
                'product_type_id' => $request->product_type_id
            ]);

            $id = DB::select("SHOW TABLE STATUS LIKE 'courses'");
            $next_id = $id[0]->Auto_increment;
            $user = Auth::user();

            Log::info('Usuario y curso info:', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'next_course_id' => $next_id
            ]);

            // Validando datos que llegan del request
            // Validar que product_type_id esté presente y sea válido
            if (!$request->product_type_id) {
                Log::warning('No se recibió product_type_id');
                return response()->json([
                    'data' => ['status' => 'error'],
                    'message' => 'product_type_id es requerido'
                ], 422);
            }

            if ($request->product_type_id !== '1' && $request->product_type_id !== '2') {
                Log::warning('Valor de product_type_id no válido:', [
                    'received_value' => $request->product_type_id
                ]);
                return response()->json([
                    'data' => ['status' => 'error'],
                    'message' => 'Valor de product_type_id no válido'
                ], 422);
            }

            // Validar campos necesarios para cursos y para libros
            if (
                !$request->title ||
                !$request->description ||
                !$request->price_base ||
                !$request->price ||
                !$request->course_about ||
                !$request->will_learn ||
                !$request->prev_knowledge ||
                !$request->course_for ||
                !$request->months ||
                !$request->certificate
            ) {
                Log::warning('Faltan campos requeridos en el request', [
                    'title' => $request->title,
                    'description' => $request->description,
                    'price_base' => $request->price_base,
                    'price' => $request->price,
                    'course_about' => $request->course_about,
                    'will_learn' => $request->will_learn,
                    'prev_knowledge' => $request->prev_knowledge,
                    'course_for' => $request->course_for,
                    'months' => $request->months,
                    'certificate' => $request->certificate
                ]);
                return response()->json([
                    'data' => ['status' => 'error'],
                    'message' => 'Faltan campos requeridos'
                ], 422);
            }

            // Validar campos necesarios solo para cursos
            if ($request->product_type_id === '1') {
                if (!$request->id_categories || !$request->id_level) {
                    Log::warning('Faltan campos requeridos para curso', [
                        'id_categories' => $request->id_categories,
                        'id_level' => $request->id_level
                    ]);
                    return response()->json([
                        'data' => ['status' => 'error'],
                        'message' => 'Faltan campos requeridos para curso (id_categories, id_level)'
                    ], 422);
                }
            }

            $course = new Course();
            $course->user_id = $user->id;
            $course->product_type_id = $request->product_type_id;
            $course->id_categories = $request->product_type_id === '1' ? $request->id_categories : 8;
            $course->title = $request->title;
            $course->slug = Str::slug($request->title);
            $course->description = $request->description;
            $course->price_base = $request->price_base;
            $course->price = $request->price;
            $course->course_level_id = $request->product_type_id === '1' ? $request->id_level : null;
            $course->course_about = $request->course_about;
            $course->will_learn = $request->will_learn;
            $course->prev_knowledge = $request->prev_knowledge;
            $course->course_for = $request->course_for;
            $course->currency = 'soles';
            $course->months = $request->product_type_id === '1' ? $request->months : null;
            $course->certificate = $request->certificate === "true" ? 1 : 0;
            $course->status = 0;

            // Procesamiento de archivo de portada
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                try {
                    $portadaFilename = Helper::formatFilename($file->getClientOriginalName());
                    $portadaPath = 'courses/' . $user->id . '/' . $next_id . '/cover/';
                    Storage::disk('public')->put($portadaPath . $portadaFilename, file_get_contents($file));
                    $course->portada = $portadaFilename;
                    $course->url_portada = '/storage/' . $portadaPath . $portadaFilename;
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivo de portada:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // Procesamiento de archivo de video/imagen promocional
            if ($request->hasFile('file_video')) {
                $file = $request->file('file_video');
                try {
                    $promoFilename = Helper::formatFilename($file->getClientOriginalName());
                    $promoPath = 'courses/' . $user->id . '/' . $next_id . '/promo/';
                    Storage::disk('public')->put($promoPath . $promoFilename, file_get_contents($file));
                    $course->path_url = '/storage/' . $promoPath . $promoFilename;
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivo de video/imagen:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            if ($course->save()) {
                $response['status'] = 'ok';
            } else {
                Log::error('Error al guardar infoproducto en base de datos');
                $response['status'] = 'error';
            }

            DB::commit();

            // Enviar emails de notificación de nuevo curso
            try {
                $this->sendNewCourseNotification($course, $user);
            } catch (\Exception $e) {
                Log::error('Error enviando emails de nuevo curso: ' . $e->getMessage(), [
                    'course_id' => $course->id,
                    'course_title' => $course->title
                ]);
            }

            return response()->json([
                'data' => $response,
                'message' => 'Data recuperada con exito'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('=== ERROR EN STORE COURSE ===', [
                'error_message' => $th->getMessage(),
                'error_code' => $th->getCode(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['file', 'file_video']),
                'memory_usage' => memory_get_usage(true),
                'timestamp' => now()
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                Log::error('Información del archivo de portada en error:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'error' => $file->getError(),
                    'is_valid' => $file->isValid()
                ]);
            }

            if ($request->hasFile('file_video')) {
                $file = $request->file('file_video');
                Log::error('Información del archivo de video en error:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'error' => $file->getError(),
                    'is_valid' => $file->isValid()
                ]);
            }

            throw $th;
        }
    }

    public function bookContentView(Course $course)
    {
        // Validar que el infoproducto sea un libro
        if ($course->product_type_id !== 2) {
            abort(404);
        }

        $observations = $course->bookObservations()
            ->with('analyst')
            ->latest()
            ->get();

        return view('content.book.edit-book-content', compact('course', 'observations'));
    }

    public function storeBookFile(StoreBookFileRequest $request, $course_id)
    {
        try {
            $this->storeBookFileService->store(
                $request->file('file'),
                $course_id,
                Auth::id()
            );

            return response()->json([
                'status' => 'ok'
            ], 200);

        } catch (\InvalidArgumentException $e) {
            Log::warning('Se superó el límite de tamaño permitido', [
                'course_id' => $course_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Error en storeBookFile', [
                'course_id' => $course_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error'
            ], 500);
        }
    }

    public function getBookFiles(Course $course)
    {
        // Validar que el infoproducto sea un libro
        if ($course->product_type_id !== 2) {
            return response()->json([
                'data' => [],
                'error' => 'El infoproducto no es un libro'
            ], 400);
        }

        try {
            $bookFiles = BookFile::where('course_id', $course->id)
                ->select('id', 'course_id', 'file_type', 'file_name', 'file_path', 'size')
                ->get();

            return response()->json([
                //'data' => $bookFiles
                'data' => $bookFiles->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'course_id' => $file->course_id,
                        'file_type' => $file->file_type,
                        'file_name' => $file->file_name,
                        'url' => Storage::disk('public')->url($file->file_path),
                        'size' => $file->size
                    ];
                })
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error en getBookFiles', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'error' => 'Error al obtener los archivos del libro'
            ], 500);
        }
    }

    public function deleteBookFile(BookFile $bookFile)
    {
        try {
            // Eliminar el directorio del archivo
            $directory = dirname($bookFile->file_path);
            Storage::disk('public')->deleteDirectory($directory);

            // Eliminar el registro de la base de datos
            $bookFile->delete();

            return response()->json([
                'status' => 'ok'
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error en deleteBookFile', [
                'book_file_id' => $bookFile->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error'
            ], 500);
        }
    }

    use CourseProcessTrait;

    public function index()
    {
        $user = User::find(auth()->user()->id);
        $permission = $user->hasPermissionTo('courses.create');
        return view('content.courses.index', compact('user', 'permission'));
    }

    public function create($product_type_id)
    {
        $categories = Category::all();
        $levels = CourseLevel::all();
        $title = $product_type_id == 1 ? 'Crear curso' : 'Crear Libro';
        return view('content.courses.create', compact('title', 'product_type_id', 'categories', 'levels'));
    }

    public function review($id)
    {
        $product_type_id = Course::find($id)->product_type_id;

        if ($product_type_id == 2) {
            $course = Course::where('courses.id', $id)
                    ->join('book_files', 'courses.id', '=', 'book_files.course_id')
                    ->join('users', 'courses.user_id', '=', 'users.id')
                    ->select('courses.*', 'book_files.file_path', 'users.name as author')
                    ->get()->first();
            return view('content.courses.verification.review-book', compact('course'));
        }

        $course = Course::where('courses.id', $id)
                    ->join('categories', 'courses.id_categories', '=', 'categories.id')
                    ->join('course_level', 'courses.course_level_id', '=', 'course_level.id')
                    ->select('courses.*', 'course_level.description as level', 'categories.name as category')
                    ->get()->first();
        $ready = $this->isReadyToPublish($course->id);
        return view('content.courses.verification.review', compact('course', 'ready'));
    }

    public function isReadyToPublish($id_course)
    {
        /**
         * Estados de la clase
         * 0 = clase no revisada
         * 1 clase desaprobada
         */
        // Get id of all modules
        $modules_ids = Course::find($id_course)->modules->pluck('id')->toArray();
        $lessons_status = [];
        foreach ($modules_ids as $module_id) {
            $status_of_lessons_from_module = Clas::where('id_modules', $module_id)->pluck('status')->toArray();
            array_push($lessons_status, $status_of_lessons_from_module);
        }
        $status_array = [];
        // Get status of all lessons in all modules
        foreach ($lessons_status as $status) {
            foreach ($status as $s) {
                array_push($status_array, $s);
            }
        }
        // si existe clase desaprobada o no ha sido revisada retorna false
        if (in_array(1, $status_array) || in_array(0, $status_array)) {
            return false;
        } else {
            return true;
        }
    }

    public function approved($course_id)
    {
        /**
         * estados del curso 
         * 0 -> creado
         * 1 -> enviado a revision
         * 2 -> aprobado
         * 3 -> curso con observaciones
         */
        try {
            DB::beginTransaction();
            $course = Course::findOrFail($course_id);
            $courseVal = $course;
            $course->status = 2;
            $course->update();

            $title = 'Infoproducto aprobado';
            $body = "$course->title fue aprobado!";
            $this->notification($course->user_id, $title, $body);

            // Enviar email de curso aprobado al usuario (igual que sendRequest)
            $user = User::find($course->user_id);
            $phpMailerService = null;
                try {
                $phpMailerService = new PHPMailerService();
            } catch (\Exception $e) {
                Log::error('No se pudo configurar el envio de email de infoproducto aprobado: ' . $e->getMessage(), [
                    'course_id' => $course->id
                ]);
            }
            $category = \App\Models\Category::find($course->id_categories);
            $level = \App\Models\CourseLevel::find($course->course_level_id);
            $courseData = [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'currency' => $course->currency ?? 'soles',
                'is_free' => $course->price <= 0,
                'category' => $category ? $category->name : 'Sin categoría',
                'level' => $level ? $level->name : 'Sin nivel',
                'months' => $course->months,
                'course_time' => $course->course_time ?? 0,
                'certificate' => $course->certificate == 1,
                'course_about' => $course->course_about ?? '',
                'will_learn' => $course->will_learn ?? '',
                'prev_knowledge' => $course->prev_knowledge ?? '',
                'course_for' => $course->course_for ?? '',
                'cover_image_url' => $course->url_portada ?? null
            ];
            $instructorData = [
                'name' => $user ? ($user->name ?? $user->username) : '',
                'email' => $user ? $user->email : '',
                'phone' => $user && isset($user->phone) ? $user->phone : 'No especificado'
            ];
            $templateData = [
                'course' => $courseData,
                'instructor' => $instructorData,
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'admin_url' => url('/admin/courses/' . $course->id)
            ];
            $template = 'emails.course-status-approved';
            $asunto = '¡Tu infoproducto ha sido aprobado!';
            if ($phpMailerService && $user) {
            try {
                    $phpMailerService->sendEmailWithTemplate(
                    $user->email,
                    $asunto,
                    $template,
                    $templateData,
                    'Promolíder'
                );
                    Log::info('Email de infoproducto aprobado enviado', [
                    'to' => $user->email,
                    'course_id' => $course->id
                ]);
                } catch (\Exception $e) {
                    Log::error('Error enviando email de infoproducto aprobado: ' . $e->getMessage(), [
                    'to' => $user->email,
                    'course_id' => $course->id
                ]);
                }
            }

            $productor_id = $course->user_id;
            $badge_level_one_id = 10;
            $userHasBadge1 = $this->validateIfUserHasBadge($badge_level_one_id, $productor_id);
            if ($userHasBadge1 == false) {
                $goal = Badge::where('id', $badge_level_one_id)->get()->first()->condition;
                $course_count = Course::where(['user_id' => $productor_id, 'status' => 2])->count();
                if ($course_count >= $goal) {
                    $badge = new BadgeDetail();
                    $badge->user_id = $productor_id;
                    $badge->badge_id = $badge_level_one_id;
                    $badge->save();
                    $title = 'Logro desbloqueado';
                    $body = 'Ha conseguido el logro por crear un curso';
                    $this->notification($course->user_id, $title, $body);
                }
            }
            $badge_level_two_id = 11;
            $userHasBadge2 = $this->validateIfUserHasBadge($badge_level_two_id, $productor_id);
            if ($userHasBadge2 == false) {
                $goal = Badge::where('id', $badge_level_two_id)->get()->first()->condition;
                $course_count = Course::where(['user_id' => $productor_id, 'status' => 2])->count();
                if ($course_count >= $goal) {
                    $badge = new BadgeDetail();
                    $badge->user_id = $productor_id;
                    $badge->badge_id = $badge_level_two_id;
                    $badge->save();
                    $title = 'Logro desbloqueado';
                    $body = "Ha conseguido el logro por crear $goal cursos";
                    $this->notification($course->user_id, $title, $body);
                }
            }
            $badge_level_three_id = 12;
            $userHasBadge3 = $this->validateIfUserHasBadge($badge_level_three_id, $productor_id);
            if ($userHasBadge3 == false) {
                $goal = Badge::where('id', $badge_level_three_id)->get()->first()->condition;
                $course_count = Course::where(['user_id' => $productor_id, 'status' => 2])->count();
                if ($course_count >= $goal) {
                    $badge = new BadgeDetail();
                    $badge->user_id = $productor_id;
                    $badge->badge_id = $badge_level_three_id;
                    $badge->save();
                    $title = 'Logro desbloqueado';
                    $body = "Ha conseguido el logro por crear $goal cursos";
                    $this->notification($course->user_id, $title, $body);
                }
            }
            DB::commit();

            return response()->json([
                'status' => 'ok'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function disapproveCourse(Course $course)
    {
        $course->update([
            'status' => 3
        ]);
    }

    public function validateIfUserHasBadge($badge_id, $user_id)
    {
        $bool = BadgeDetail::where(['user_id' => $user_id, 'badge_id' => $badge_id])->exists();
        return $bool;
    }

    public function notification($id_user, $title, $body)
    {
        try {
            DB::beginTransaction();
            $notification = new Notifications();
            $notification->id_generator = $id_user;
            $notification->id_receiver = $id_user;
            $notification->title = $title;
            $notification->body = $body;
            $notification->type = 3; # Compra de cursos
            $notification->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // cambiamos los estados de las observaciones de un curso para que pueda visualizarlas el productor
    // cambiar el estado del curso a curso con observaciones
    public function sendObservations($course_id)
    {
        try {
            DB::beginTransaction();
            $request = request();
            $observationText = $request->input('observation', null);
            $course = Course::findOrFail($course_id);
            $course->status = 3;
            $course->update();

            $title = 'Curso con observaciones';
            $body = "$course->title tiene observaciones.";
            $this->notification($course->user_id, $title, $body);

            // Validar usuario autenticado antes de guardar observación
            $analystId = auth()->id();
            if (!$analystId) {
                DB::rollBack();
                return response()->json(['error' => 'No autenticado. Inicie sesión para enviar observaciones.'], 401);
            }

            // Guardar la observación general del curso si se envía
            if ($observationText) {
                // Buscar el primer módulo del curso usando la columna correcta
                $firstModule = \App\Models\Module::where('id_courses', $course_id)->first();
                $idClass = null;
                if ($firstModule) {
                    // Buscar la primera clase de ese módulo
                    $firstClass = \App\Models\Clas::where('id_modules', $firstModule->id)->first();
                    if ($firstClass) {
                        $idClass = $firstClass->id;
                    }
                }
                if (!$idClass) {
                    DB::rollBack();
                    return response()->json(['error' => 'No se encontró ninguna clase asociada al curso para guardar la observación.'], 422);
                }
                $generalObs = new \App\Models\CourseObservation();
                $generalObs->id_course = $course_id;
                $generalObs->id_class = $idClass;
                $generalObs->description = $observationText;
                $generalObs->status = 1;
                $generalObs->id_analyst = $analystId; // Siempre se guarda el analista
                $generalObs->id_productor = $course->user_id; // Se asigna el productor correctamente
                $generalObs->save();
            }

            // Enviar email de observaciones al usuario (estructura similar a approved)
            $user = User::find($course->user_id);
            $phpMailerService = null;
            try {
                $phpMailerService = new PHPMailerService();
            } catch (\Exception $e) {
                Log::error('No se pudo configurar el envio de email de observaciones: ' . $e->getMessage(), [
                    'course_id' => $course->id
                ]);
            }
            $category = \App\Models\Category::find($course->id_categories);
            $level = \App\Models\CourseLevel::find($course->course_level_id);
            $courseData = [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'currency' => $course->currency ?? 'soles',
                'is_free' => $course->price <= 0,
                'category' => $category ? $category->name : 'Sin categoría',
                'level' => $level ? $level->name : 'Sin nivel',
                'months' => $course->months,
                'course_time' => $course->course_time ?? 0,
                'certificate' => $course->certificate == 1,
                'course_about' => $course->course_about ?? '',
                'will_learn' => $course->will_learn ?? '',
                'prev_knowledge' => $course->prev_knowledge ?? '',
                'course_for' => $course->course_for ?? '',
                'cover_image_url' => $course->url_portada ?? null
            ];
            $instructorData = [
                'name' => $user ? ($user->name ?? $user->username) : '',
                'email' => $user ? $user->email : '',
                'phone' => $user && isset($user->phone) ? $user->phone : 'No especificado'
            ];
            $templateData = [
                'course' => $courseData,
                'instructor' => $instructorData,
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'admin_url' => url('/admin/courses/' . $course->id),
                'observaciones' => !empty($observationText) ? $observationText : 'No se registraron observaciones para este curso.'
            ];
            // Usar solo la plantilla de observaciones para el correo
            $template = 'emails.course-status-observations';
            $asunto = 'Observaciones sobre tu curso';
            if ($phpMailerService && $user) {
                try {
                $phpMailerService->sendEmailWithTemplate(
                    $user->email,
                    $asunto,
                    $template,
                    $templateData,
                    'Promolíder'
                );
                Log::info('Email de observaciones enviado', [
                    'to' => $user->email,
                    'course_id' => $course->id
                ]);
            } catch (\Exception $e) {
                Log::error('Error enviando email de observaciones: ' . $e->getMessage(), [
                    'to' => $user->email,
                    'course_id' => $course->id
                ]);
                }
            }
            DB::commit();

            // Si la petición es AJAX o espera JSON, responde con JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'ok', 'message' => 'Observaciones enviadas correctamente.']);
            }
            // Si no, redirige como antes
            return redirect()->back()->with('success', 'Observaciones enviadas correctamente.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error en sendObservations: ' . $th->getMessage());
            $request = request();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Error al enviar observaciones.'], 500);
            }
            return redirect()->back()->with('error', 'Error al enviar observaciones.');
        }

        // El resto de tu código permanece igual...

        // Log de datos del formulario
        Log::info('Datos del formulario recibidos:', [
            'title' => $request->title,
            'id_categories' => $request->id_categories,
            'id_level' => $request->id_level,
            'price' => $request->price,
            'price_base' => $request->price_base,
            'months' => $request->months,
            'certificate' => $request->certificate,
            'is_free' => $request->is_free ?? 'no definido'
        ]);

        $id = DB::select("SHOW TABLE STATUS LIKE 'courses'");
        $next_id = $id[0]->Auto_increment;
        $user = Auth::user();

        Log::info('Usuario y curso info:', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'next_course_id' => $next_id
        ]);

        try {
            DB::beginTransaction();
            Log::info('Transacción iniciada');

            $course = new Course();
            $course->user_id = $user->id;
            $course->id_categories = $request->id_categories;
            $course->title = $request->title;
            $course->slug = Str::slug($request->title);
            $course->description = $request->description;
            $course->price_base = $request->price_base;
            $course->price = $request->price;
            $course->course_level_id = $request->id_level;
            $course->course_about = $request->course_about;
            $course->will_learn = $request->will_learn;
            $course->prev_knowledge = $request->prev_knowledge;
            $course->course_for = $request->course_for;
            $course->currency = 'soles';
            $course->months = $request->months;
            $course->certificate = $request->certificate === "true" ? 1 : 0;
            $course->status = 0;

            Log::info('Modelo Course creado y datos asignados');

            // Procesamiento de archivo de portada
            if ($request->hasFile('file')) {
                $file = $request->file('file');
            
                Log::info('Procesando archivo de portada:', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'size_mb' => round($file->getSize() / 1024 / 1024, 2),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError()
                ]);
            
                try {
                    $portadaFilename = Helper::formatFilename($file->getClientOriginalName());
                    $portadaPath = 'courses/' . $user->id . '/' . $next_id . '/cover/'; // ✅ Carpeta específica para portada
                
                    Log::info('Subiendo portada a almacenamiento local (temporalmente):', [
                        'filename' => $portadaFilename,
                        'path' => $portadaPath,
                        'full_path' => $portadaPath . $portadaFilename
                    ]);
                
                    // ✅ CAMBIO TEMPORAL: Usar almacenamiento local en lugar de S3
                    $uploadResult = Storage::disk('public')->put($portadaPath . $portadaFilename, file_get_contents($file));
                
                    Log::info('Resultado de subida local (portada):', [
                        'success' => $uploadResult,
                        'path' => $portadaPath . $portadaFilename
                    ]);
                
                    $course->portada = $portadaFilename;
                    $course->url_portada = '/storage/' . $portadaPath . $portadaFilename; // URL local
                
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivo de portada:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                Log::warning('No se recibió archivo de portada');
            }
            
            // Procesamiento de archivo de video/imagen promocional
            if ($request->hasFile('file_video')) {
                $file = $request->file('file_video');
            
                Log::info('Procesando archivo de video/imagen:', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'size_mb' => round($file->getSize() / 1024 / 1024, 2),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError()
                ]);
            
                try {
                    $promoFilename = Helper::formatFilename($file->getClientOriginalName());
                    $promoPath = 'courses/' . $user->id . '/' . $next_id . '/promo/'; // ✅ Carpeta específica para video/imagen promocional
                
                    Log::info('Subiendo video/imagen a almacenamiento local (temporalmente):', [
                        'filename' => $promoFilename,
                        'path' => $promoPath,
                        'full_path' => $promoPath . $promoFilename
                    ]);
                
                    // ✅ CAMBIO TEMPORAL: Usar almacenamiento local en lugar de S3
                    $uploadResult = Storage::disk('public')->put($promoPath . $promoFilename, file_get_contents($file));
                
                    Log::info('Resultado de subida local (video/imagen):', [
                        'success' => $uploadResult,
                        'path' => $promoPath . $promoFilename
                    ]);
                
                    $course->path_url = '/storage/' . $promoPath . $promoFilename; // URL local
                
                } catch (\Exception $e) {
                    Log::error('Error al procesar archivo de video/imagen:', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                Log::warning('No se recibió archivo de video/imagen');
            }

            Log::info('Intentando guardar curso en base de datos');

            if ($course->save()) {
                Log::info('Curso guardado exitosamente:', [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug
                ]);
                $response['status'] = 'ok';
            } else {
                Log::error('Error al guardar curso en base de datos');
                $response['status'] = 'error';
            }

            DB::commit();
            Log::info('Transacción confirmada (commit)');

            // ✅ NUEVO: Enviar emails de notificación de nuevo curso
            try {
                $this->sendNewCourseNotification($course, $user);
                Log::info('Emails de nuevo curso enviados exitosamente', [
                    'course_id' => $course->id,
                    'course_title' => $course->title
                ]);
            } catch (\Exception $e) {
                // No interrumpir el flujo principal si falla el correo
                Log::error('Error enviando emails de nuevo curso: ' . $e->getMessage(), [
                    'course_id' => $course->id,
                    'course_title' => $course->title
                ]);
            }

            Log::info('=== FIN STORE COURSE EXITOSO ===', [
                'final_memory_usage' => memory_get_usage(true),
                'execution_time' => microtime(true) - LARAVEL_START,
                'course_id' => $course->id ?? 'no_id'
            ]);

            return response()->json([
                'data' => $response,
                'message' => 'Data recuperada con exito'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('=== ERROR EN STORE COURSE ===', [
                'error_message' => $th->getMessage(),
                'error_code' => $th->getCode(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['file', 'file_video']), // Excluir archivos del log
                'memory_usage' => memory_get_usage(true),
                'timestamp' => now()
            ]);

            // Log específico para errores de archivos
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                Log::error('Información del archivo de portada en error:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'error' => $file->getError(),
                    'is_valid' => $file->isValid()
                ]);
            }

            if ($request->hasFile('file_video')) {
                $file = $request->file('file_video');
                Log::error('Información del archivo de video en error:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'error' => $file->getError(),
                    'is_valid' => $file->isValid()
                ]);
            }

            throw $th;
        }
    }

    public function courseList($id)
    {

        $requestedUser = User::where('id', $id)->firstOrFail();

        // --- ¡AQUÍ ESTÁ LA LÓGICA DE AUTORIZACIÓN! ---
        // Verifica si el ID del usuario autenticado es el mismo que el ID del usuario solicitado.
        if (Auth::user()->id !== $requestedUser->id) {
            // Si no coinciden, significa que un usuario está intentando ver el perfil de otro.
            // Se deniega el acceso con un error 403 Forbidden (Prohibido).
            abort(403, 'Acción no autorizada.');
        }

        //$courses = Course::where('user_id', $id)->join('course_level', 'courses.course_level_id', '=', 'course_level.id')->select('courses.*', 'course_level.description as level')->orderBy('id', 'DESC')->get();
        $courses = Course::where('user_id', $id)->join('product_types', 'courses.product_type_id', '=', 'product_types.id')->select('courses.*', 'product_types.name as infoproduct')->orderBy('id', 'DESC')->get();

        return response()->json([
            'data' => $courses,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function courseListVerification()
    {
        $courses = Course::where('status', 1)
                    ->join('users', 'courses.user_id', '=', 'users.id')
                    ->select('courses.*', 'users.name')->get();

        return response()->json([
            'data' => $courses,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function edit($id)
    {
        $course = Course::where('id', $id)->join('course_level', 'courses.course_level_id', '=', 'course_level.id')->select('courses.*', 'course_level.description as level')->get()->first();
        echo json_encode($course);
    }

    public function update($id, Request $request)
    {
        // 1. Busca el curso que se quiere actualizar por su ID de la URL
        $course = Course::findOrFail($id);

        // 2. ¡AQUÍ ESTÁ LA AUTORIZACIÓN!
        //    Verifica si el curso pertenece al usuario que está haciendo la petición.
        if (auth()->id() !== $course->user_id) {
            abort(403, 'Acción no autorizada. No eres el propietario de este curso.');
        }

        try {
            DB::beginTransaction();

            // 3. Actualiza los campos. No es necesario volver a asignar user_id.
            $course->id_categories = $request->id_categories;
            $course->title = $request->title;
            $course->slug = Str::slug($request->title);
            $course->description = $request->description;
            $course->price_base = $request->price_base;
            $course->price = $request->price;
            $course->course_level_id = $request->id_level;
            $course->course_about = $request->course_about;
            $course->will_learn = $request->will_learn;
            $course->prev_knowledge = $request->prev_knowledge;
            $course->course_for = $request->course_for;
            $course->months = $request->months;
            $course->certificate = $request->certificate === "true" ? 1 : 0;
            // No es necesario actualizar 'currency' si siempre es la misma.

            if ($request->hasFile('file')) {
                $newfile = $request->file('file');
                $portada = Helper::formatFilename($newfile->getClientOriginalName());
                // Usamos auth()->id() para mayor seguridad y consistencia
                $path = 'courses/' . auth()->id() . '/' . $id . '/portada/';
                if ($course->url_portada) {
                    Storage::disk('s3')->delete($course->url_portada);
                }
                Storage::disk('s3')->put($path . $portada, file_get_contents($newfile), 'public');
                $course->portada = $portada;
                $course->url_portada = $path . $portada;
            }

            // El bloque de file_video parece tener un error, sobreescribe la portada.
            // Asumo que querías actualizar el video del curso.
            if ($request->hasFile('file_video')) {
                $videoFile = $request->file('file_video');
                $videoName = Helper::formatFilename($videoFile->getClientOriginalName());
                $videoPath = 'courses/' . auth()->id() . '/' . $id . '/video_preview/';
                if ($course->path_url) { // Asumiendo que path_url es para el video
                    Storage::disk('s3')->delete($course->path_url);
                }
                Storage::disk('s3')->put($videoPath . $videoName, file_get_contents($videoFile), 'public');
                $course->path_url = $videoPath . $videoName;
            }

            $course->save(); // Usa save() en lugar de update() cuando modificas el objeto directamente.

            DB::commit();

            // 4. Retorna una respuesta JSON estándar
            return response()->json(['status' => 'ok', 'message' => 'Curso actualizado correctamente']);

        } catch (\Throwable $th) {
            DB::rollBack();
            // Lanza el error para que Laravel lo maneje y lo registre.
            throw $th;
        }
    }

    public function listCoursesProd()
    {
        $user = User::find(auth()->user()->id);
        $data['course'] = $user->MyCourses()->get();
        $data['latests'] = Course::where('user_id', $user->id)->latest()->first();
        if (count($data['course']) == 0) {
            return ['error' => 'user without courses'];
        } else {
            return $data;
        }
    }

    public function delete($id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        // Verifica que el usuario autenticado sea el dueño del curso.
        if (auth()->id() !== $course->user_id) {
            abort(403, 'Acción no autorizada.');
        }

        $hasSubscribers = PurchasedCourse::where('course_id', $id)->exists();

        try {
            DB::beginTransaction();

            if ($hasSubscribers) {
                $course->status = 0;
                $course->save();

                $response = [
                    'status' => 'inactive',
                    'message' => 'El curso tiene estudiantes inscritos. Se ha cambiado a estado inactivo para mantener el acceso de los estudiantes.',
                    'courses' => Course::where('user_id', $user->id)->get()
                ];

            } else {
                // Eliminar observaciones del curso
                $observations = \App\Models\CourseObservation::where('id_course', $course->id)->get();
                foreach ($observations as $obs) {
                    $obs->delete();
                }

                // Eliminar módulos y sus clases
                $modules = $course->modules;
                if ($modules && count($modules)) {
                    foreach ($modules as $module) {
                        // Eliminar clases del módulo
                        $clases = $module->clases ?? [];
                        foreach ($clases as $clase) {
                            $clase->delete();
                        }
                        $module->delete();
                    }
                }

                $course->delete();

                $response = [
                    'status' => 'ok',
                    'message' => 'Curso eliminado correctamente',
                    'courses' => Course::where('user_id', $user->id)->get()
                ];
            }

            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changeState($id)
    {
        try {
            DB::beginTransaction();
            $course = Course::findOrFail($id);
            $course->status = ($course->status === 0) ? 1 : 0;

            if ($course->update()) {
                // Log para depuración de envío de correo
                \Log::info('Cambio de estado de curso', [
                    'curso_id' => $course->id,
                    'nuevo_estado' => $course->status,
                    'usuario_email' => User::find($course->user_id)->email
                ]);

                $owner = User::find($course->user_id);
                $phpMailerService = new \App\Services\PHPMailerService();
                $template = '';
                $asunto = '';
                switch ($course->status) {
                    case 1:
                        $template = 'emails.course-status-approved';
                        $asunto = 'Tu curso ha sido aprobado';
                        break;
                    case 0:
                        $template = 'emails.course-status-inactive';
                        $asunto = 'Tu curso ha sido marcado como inactivo';
                        break;
                    case 2:
                        $template = 'emails.course-status-pending';
                        $asunto = 'Tu curso está pendiente de revisión';
                        break;
                    case 3:
                        $template = 'emails.course-status-observations';
                        $asunto = 'Tu curso tiene observaciones';
                        break;
                    case 4:
                        $template = 'emails.course-status-revision';
                        $asunto = 'Tu curso está en revisión';
                        break;
                    default:
                        $template = 'emails.course-status-pending';
                        $asunto = 'Actualización de estado de tu curso';
                        break;
                }
                \Log::info('Enviando correo de estado de curso', [
                    'template' => $template,
                    'asunto' => $asunto,
                    'email' => $owner->email
                ]);
                try {
                    $phpMailerService->sendEmailWithTemplate(
                        $owner->email,
                        $asunto,
                        $template,
                        [
                            'course' => $course,
                            'user' => $owner
                        ]
                    );
                    \Log::info('Correo enviado correctamente', [
                        'email' => $owner->email,
                        'curso_id' => $course->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error enviando correo de estado de curso', [
                        'error' => $e->getMessage(),
                        'curso_id' => $course->id,
                        'email' => $owner->email
                    ]);
                }
                $user = User::find(auth()->user()->id);
                $response['courses'] = $user->MyCourses()->get();
                $response['status'] = 'ok';
            } else {
                $response['status'] = 'error';
            }
            echo json_encode($response);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function userCourses($user_id)
    {
        $courses = Course::where('user_id', $user_id)->join('course_level', 'courses.course_level_id', '=', 'course_level.id')->select('courses.*', 'course_level.description as level')->get();
        return $courses;
    }

    public function modulesList($course_id)
    {
        $modules = Course::find($course_id)->modules;

        return response()->json([
            'data' => $modules,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function verifyIsHasModules($course_id)
    {
        $modules = Module::where('id_courses', $course_id)->get();
        if (count($modules) > 0) {
            $first_class = Clas::where('id_modules', $modules[0]->id)->get();
            if (count($first_class) > 0) {
                return true;
            }
        }
        return false;
    }

    public function prueba()
    {
        $signatureConfig = UserConfiguration::where('user_id', 2)
            ->where('configuration_id', 2)->exists();
        $templateConfig = UserConfiguration::where('user_id', 5)
            ->where('configuration_id', 1)->exists();
        $config = $signatureConfig && $templateConfig ? true : false;
        return $templateConfig;
    }

    public function sendRequest($id)
    {
        try {
            DB::beginTransaction();
            $course = Course::findOrFail($id);

            // Verificar si el infoproducto revisado es un libro
            $isBook = $course->product_type_id == 2;

            if ($isBook) {
                // Obtener los archivos asociados al libro
                $bookFiles = $course->files()->get();

                if ($bookFiles->isEmpty()) {
                    DB::rollBack();
                    return response()->json([
                        'data' => 'empty_files',
                        'message' => 'El libro debe tener al menos un archivo asociado para ser enviado a revisión.',
                    ], 422);
                }

                // Cambiar status a pendiente de revisión y enviar notificación al admin
                $response = $this->processCourse($course);

                DB::commit();

                return response()->json([
                    'data' => $response,
                    'message' => 'Data recuperada con exito',
                ], 200);
            }

            $hasModules = $this->verifyIsHasModules($id);

            if (!$hasModules) {
                $response = 'empty';
            } else {
                $response = $this->processCourse($course);

                if ($response === 'ok') {
                    try {
                // Enviar correo de estado pendiente a soporte y usuario creador
                $phpMailerService = new \App\Services\PHPMailerService();
                $category = \App\Models\Category::find($course->id_categories);
                $level = \App\Models\CourseLevel::find($course->course_level_id);
                $user = \App\Models\User::find($course->user_id);

                $courseData = [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'currency' => $course->currency ?? 'soles',
                    'is_free' => $course->price <= 0,
                    'category' => $category->name ?? 'Sin categoría',
                    'level' => $level->name ?? 'Sin nivel',
                    'months' => $course->months,
                    'course_time' => $course->course_time ?? 0,
                    'certificate' => $course->certificate == 1,
                    'course_about' => $course->course_about ?? '',
                    'will_learn' => $course->will_learn ?? '',
                    'prev_knowledge' => $course->prev_knowledge ?? '',
                    'course_for' => $course->course_for ?? '',
                    'cover_image_url' => $course->url_portada ?? null
                ];

                $instructorData = [
                    'name' => $user->name ?? $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'No especificado'
                ];

                $templateData = [
                    'course' => $courseData,
                    'instructor' => $instructorData,
                    'timestamp' => now()->format('d/m/Y H:i:s'),
                    'admin_url' => url('/admin/courses/' . $course->id)
                ];

                try {
                    $phpMailerService->sendEmailWithTemplate(
                        'soporte@promolider.info',
                        '🕒 Curso pendiente de revisión: ' . $courseData['title'],
                        'emails.course-status-pending',
                        $templateData,
                        'Promolíder - Estado de Curso'
                    );
                } catch (\Exception $e) {
                    \Log::error('Error enviando email de estado pendiente a soporte: ' . $e->getMessage(), [
                        'course_id' => $course->id
                    ]);
                }

                try {
                    $phpMailerService->sendEmailWithTemplate(
                        $user->email,
                        'Tu curso está pendiente de revisión: ' . $courseData['title'],
                        'emails.course-status-pending',
                        $templateData,
                        'Promolíder - Estado de Curso'
                    );
                } catch (\Exception $e) {
                    \Log::error('Error enviando email de estado pendiente al usuario: ' . $e->getMessage(), [
                        'course_id' => $course->id,
                        'user_email' => $user->email
                    ]);
                }
                    } catch (\Throwable $mailError) {
                        \Log::error('No se pudo preparar el envio de correo de revision: ' . $mailError->getMessage(), [
                            'course_id' => $course->id,
                            'error_class' => get_class($mailError)
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'data' => $response,
                'message' => 'Data recuperada con exito',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function categoriesList()
    {
        $categories = Category::all();
        return $categories;
    }

    public function showCourseForCertificate($id)
    {
        $course = Course::findOrFail($id);

        if (auth()->id() !== $course->user_id) {
            abort(403, 'Accion no autorizada.');
        }

        return response()->json([
            'id' => $course->id,
            'title' => $course->title,
            'instructor_signature_path' => $course->instructor_signature_path,
            'instructor_signature_url' => $course->instructor_signature_path
                ? Storage::disk('public')->url($course->instructor_signature_path)
                : null,
        ]);
    }

    public function levelsList()
    {
        $levels = CourseLevel::all();
        return $levels;
    }

    public function subscribers()
    {
        $id = auth()->user()->id;
        $courses = Course::where('user_id', $id)->select('id', 'id_categories', 'title', 'description', 'url_portada', 'portada')->get();
        $path = 'https://promolider-storage-user.s3-accelerate.amazonaws.com/';

        $shareComponent = \Share::page('http://promolider.xyz/login')->facebook()
            ->twitter()
            ->linkedin()
            ->telegram()
            ->whatsapp()
            ->reddit();

        return view('content.courses.subscriber.index', compact('courses', 'path', 'shareComponent'));
    }

    public function subscribersList($course_id)
    {
        $users = [];
        $subscriber = PurchasedCourse::where('course_id', $course_id)->get();
        foreach ($subscriber as $index => $value) {
            $user = User::where('id', $value->user_id)->get()->first();
            $user->completed_course = $value->completed_course;
            $user->certificate_delivered = $value->certificate_delivered;
            $user->purchased_course_id = $value->id;
            array_push($users, $user);
        }

        return response()->json([
            'data' => $users,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function verification()
    {
        $user = User::find(auth()->user()->id);
        return view('content.courses.verification.index', compact('user'));
    }

    public function configureCertificate($course_id)
    {
        $course = Course::select('id', 'title')->where('id', $course_id)->get()->first();
        $courseConfig = CourseConfiguration::where('id', $course_id)->get()->first();
        return view('content.courses.certificate.index', compact('course'));
    }

    public function getConfigureCertificate($course_id)
    {
        $courseConfig = CourseConfiguration::where('course_id', $course_id)->get()->first();
        return response()->json([
            'status' => true,
            'data' => $courseConfig,
            'message' => 'Data recuperada con exito'
        ], 200);
    }

    public function getOrders($id)
    {
        $data = collect();
        $modules = Course::join('modules', 'courses.id', '=', 'modules.id_courses')
            ->where('courses.id', $id)
            ->orderBy('modules.order', 'asc')
            ->select('modules.id', 'modules.name')
            ->get();
        foreach ($modules as $module) {
            $module->type = "module";
            $data->push($module);
            $classes = Clas::where('id_modules', $module->id)
                ->select('id', 'name', 'id_modules')
                ->orderBy('order', 'asc')
                ->get();
            if (!$classes->isEmpty()) {
                foreach ($classes as $class) {
                    $class->type = "class";
                    $videos = Video::where('class_id', $class->id)->get();
                    $class->videos = $videos;
                    $data->push($class);
                }
            }
        }
        return $data;
    }

    public function changeOrder(Request $request)
    {
        $items = json_decode($request->order, true);
        $i = 1;
        $item_id = 0;
        foreach ($items as $item) {
            $type = $item['type'];
            if ($type == "module") {
                $item_id = $item['id'];
            } else {
                $item_id = $item['id'];
                $class = Clas::findOrFail($item_id);
                $class->order = $i;
                $class->update();
            }
            $i++;
        }
        return $items;
    }

    public function changeOrderModule(Request $request)
    {
        $items = json_decode($request->order, true);
        $i = 1;
        $item_id = 0;
        foreach ($items as $item) {
            $item_id = $item['id'];
            $module = Module::findOrFail($item_id);
            $module->order = $i;
            $module->update();

            $i++;
        }
        return $items;
    }

    /**
     * Envía notificaciones por email cuando se crea un nuevo curso
     * Se envía tanto a Promolíder como al usuario que creó el curso
     */
    private function sendNewCourseNotification($course, $user)
    {
        $phpMailerService = new PHPMailerService();
        
        // Obtener información adicional del curso
        $category = Category::find($course->id_categories);
        $level = CourseLevel::find($course->course_level_id);
        
        // Datos del curso para las plantillas
        $courseData = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'price' => $course->price,
            'currency' => $course->currency ?? 'soles',
            'is_free' => $course->price <= 0,
            'category' => $category->name ?? 'Sin categoría',
            'level' => $level->name ?? 'Sin nivel',
            'months' => $course->months,
            'course_time' => $course->course_time ?? 0,
            'certificate' => $course->certificate == 1,
            'course_about' => $course->course_about,
            'will_learn' => $course->will_learn,
            'prev_knowledge' => $course->prev_knowledge,
            'course_for' => $course->course_for,
            'cover_image_url' => $course->url_portada ?? null
        ];
        
        // Datos del instructor/usuario
        $instructorData = [
            'name' => $user->name ?? $user->username,
            'email' => $user->email,
            'phone' => $user->phone ?? 'No especificado'
        ];
        
        // Datos comunes para ambos emails
        $templateData = [
            'course' => $courseData,
            'instructor' => $instructorData,
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'admin_url' => url('/admin/courses/' . $course->id)
        ];
        
        // 1. Enviar email a Promolíder (administradores)
        try {
            $phpMailerService->sendEmailWithTemplate(
                'soporte@promolider.info',
                '🎓 Nuevo Curso Creado: ' . $courseData['title'],
                'emails.new-course-notification',
                $templateData,
                'Promolíder - Notificación de Curso'
            );
            
            Log::info('Email de nuevo curso enviado a Promolíder', [
                'course_id' => $course->id,
                'course_title' => $courseData['title'],
                'to' => 'soporte@promolider.info'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando email a Promolíder: ' . $e->getMessage(), [
                'course_id' => $course->id
            ]);
        }
        
        // 2. Enviar email al usuario que creó el curso
        try {
            $phpMailerService->sendEmailWithTemplate(
                $user->email,
                'Curso Creado Exitosamente: ' . $courseData['title'],
                'emails.new-course-notification',
                $templateData,
                'Promolíder - Confirmación de Curso'
            );
            
            Log::info('Email de confirmación enviado al usuario', [
                'course_id' => $course->id,
                'course_title' => $courseData['title'],
                'to' => $user->email,
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando email al usuario: ' . $e->getMessage(), [
                'course_id' => $course->id,
                'user_email' => $user->email
            ]);
        }
    }
}
