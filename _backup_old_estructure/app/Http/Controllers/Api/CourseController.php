<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Clas;
use App\Models\Exam;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Category;
use App\Helpers\ParseUrl;
use App\Models\CourseRate;
use App\Models\CourseUser;
use App\Models\AccountType;
use App\Models\Preferences;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use App\Models\LatestLessons;
use App\Traits\ResponseFormat;
use App\Models\PurchasedCourse;
use Illuminate\Support\Facades\DB;
use App\Models\CourseConfiguration;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\CartController;

class CourseController extends Controller
{
    use ResponseFormat;

    public function listCourses()
    {
        try {
            $userId = Auth::user()->id;
            $courses = Course::select('id', 'title')->where('status', 2)->where('user_id', $userId)->get();
            return response()->json($courses ? $courses : [], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list()
    {
        $courses = [];
        if ($lessons = $this->showLatestLesson()) {
            $courses[] = [
                "latest_lessons" => $lessons
            ];
        }
        if ($coursesRelated = $this->showRelated()) {
            $courses[] = [
                "courses_related" => $coursesRelated
            ];
        }
        if ($coursesPreferences = $this->showPreferences()) {
            $courses[] = [
                "courses_preferences" => $coursesPreferences
            ];
        }
        if ($lastCourses = $this->showLastCourses()) {
            $courses[] = [
                "last_courses" => $lastCourses
            ];
        }
        return $this->responseOk('', $courses);
    }

    public function listRandom()
    {
        $courses = Course::SltCourse()->inRandomOrder()->get()->take(6);
        return $this->responseOk('', $courses);
    }

    public function show($id)
    {
        $curso = Course::select('title')->find($id);
        if ($curso) {
            $modules = Module::select('id', 'name')->where('id_courses', '=', $id)->get();
            $lesson = [];
            $modulesJson = [];

            foreach ($modules as $mod) {
                // estado aprobado de la clase = 2
                $lesson = Clas::select('id', 'name', 'time', 'url', 'description')->where([
                    'id_modules' => $mod->id,
                    'status' => 2
                ])->get();
                $modulesJson[] = [
                    'name'       => $mod->name,
                    'lessons'    => $lesson
                ];
            }

            $courseJson = [
                'title'    => $curso->title,
                'modules'   => $modulesJson
            ];

            return $this->responseOk('', $courseJson);
        } else {
            return ['error' => 'El curso no existe'];
        }
    }

    public function finalExam($course_id)
    {
        $productor_id = Course::select('user_id')->where('id', $course_id)->get()->first()->user_id;
        $course_final_exam = Exam::where(['course_id' =>  $course_id, 'productor_id' => $productor_id, 'status' => 1])->exists();
        if ($course_final_exam) {
            $exam = Exam::select('id', 'title as name')->where(['course_id' =>  $course_id, 'productor_id' => $productor_id, 'status' => 1])->get();
            $data = [
                'name'       => 'Examen final',
                'lessons'    => $exam
            ];
        } else {
            $data = [];
        }
        return $data;
    }

    public function moduleExam($module_id, $course_id)
    {
        $productor_id = Course::select('user_id')->where('id', $course_id)->get()->first()->user_id;

        $course_final_exam = Exam::where(['module_id' =>  $module_id, 'productor_id' => $productor_id, 'status' => 1])->exists();
        if ($course_final_exam) {
            $data = Exam::select('id', 'title as name')->where(['module_id' =>  $module_id, 'productor_id' => $productor_id, 'status' => 1])->get()->first();
        } else {
            $data = [];
        }
        return $data;
    }

    public function listProducer($id)
    {
        $data = Course::select('courses.title', 'categories.name', 'courses.price', 'courses.status')
            ->join('categories', 'categories.id', '=', 'courses.id_categories')
            ->join('course_level', 'courses.course_level_id', '=', 'course_level.id')
            ->where('courses.user_id', '=', $id)
            ->get();
        if (count($data) <> 0) {
            return  $this->responseOk('', $data);
        } else {
            return ['error' => 'No existe el productor'];
        }
    }

    public function detailsCourse($id)
    {
        Log::info("📩 [detailsCourse] Request recibido con ID:", ['id' => $id]);
    
        $course = Course::select(
            'id',
            'product_type_id',
            'user_id',
            'id_categories',
            'title',
            'description',
            'price',
            'created_at',
            'url_portada',
            'path_url',
            'certificate',
            'course_about',
            'will_learn',
            'prev_knowledge',
            'course_for',
            'course_level_id'
        )->where('id', $id)->first();
        
        if (!$course) {
            Log::warning("⚠️ [detailsCourse] No se encontró un curso con ese ID", ['id' => $id]);
            return response()->json(['error' => 'Curso no encontrado', 'id_recibido' => $id], 404);
        }
    
        Log::info("✅ [detailsCourse] Curso encontrado", [
            'course_id' => $course->id,
            'product_type_id' => $course->product_type_id === "1" ? "Curso" : ($course->product_type_id === "2" ? "Libro" : "Desconocido"),
            'course_user_id' => $course->user_id,
            'certificate'=> $course->certificate,
            'auth_user_id' => auth()->id()
        ]);
    
        // Propiedad para saber si el curso pertenece al usuario logueado
        $course->owner = $course->user_id == auth()->id();
    
        // Agregar descuento al precio del curso
        $account_type = AccountType::find(auth()->user()->id_account_type);
    
        Log::info("💰 [detailsCourse] Tipo de cuenta", [
            'account_type_id' => auth()->user()->id_account_type,
            'discount' => $account_type ? $account_type->disc_purchases_course : null
        ]);
    
        $type_certificate = CourseConfiguration::where('course_id', $id)->first();
    
        if ($type_certificate) {
            Log::info("📜 [detailsCourse] Configuración del curso encontrada", [
                'type_certificate' => $type_certificate->type_certificate
            ]);
        } else {
            Log::warning("⚠️ [detailsCourse] No hay configuración de curso para este ID", ['course_id' => $id]);
        }
    
        $discount = ($course->price * $account_type->disc_purchases_course) / 100;
        $course->price_with_discount = round($course->price - $discount, 2);
    
        Log::info("💵 [detailsCourse] Precio calculado", [
            'original_price' => $course->price,
            'price_with_discount' => $course->price_with_discount
        ]);
    
        return $this->responseOk('Detalles del curso', $course);
    }

    public function recomendations($category)
    {
        $user_type = auth()->user()->id_account_type;
        if ($user_type == 5) {
            $courses = Course::inRandomOrder()->where(['id_categories', '=', $category], ['course_level_id', '=', 1])->get()->take(3);
        } else {
            $courses = Course::inRandomOrder()->where('id_categories', $category)->get()->take(3);
        }
        foreach ($courses as $c) {
            $json[] = array(
                'id'        => $c->id,
                'title'     => $c->title,
                'image'     => $c->image,
                'producer'  => ($c->user)->name
            );
        }
        return $this->responseOk('', $json);
    }

    public function addLatestLesson($id)
    {
        $query = LatestLessons::where('class_id', $id)->where('users_id', auth()->user()->id);
        if ($query->exists()) {
            $lesson = $query->get()[0];
            $lesson->updated_at = date('Y-m-d H:i:s');
            $lesson->save();
        } else {
            $lesson = new LatestLessons;
            $lesson->users_id = auth()->user()->id;
            $lesson->class_id = $id;
            $lesson->save();
        }
        if (LatestLessons::CountLesson() > 6) {
            $data = LatestLessons::LastLesson();
            $data->delete();
        }
        return $this->responseOk('', true);
    }

    public function showLatestLesson()
    {
        $query = LatestLessons::GetClass();
        if ($query->exists()) {
            $data = $query->with('class.module.course')->get();
            $lesson = [];
            foreach ($data as $obj) {
                $lesson[] = [
                    'id_class' => $obj->class->id,
                    'name_class' => $obj->class->name,
                    'img_course' => $obj->class->module->course->image,
                    'id_category' => $obj->class->module->course->id_categories
                ];
            }
            return $lesson;
        } else {
            return false;
        }
    }

    public function showRelated()
    {
        $query = CourseUser::Related();
        if ($query->exists()) {
            $data = $query->get();
            foreach ($data as $preferences) {
                $array[] = $preferences->id_categories;
            }
            $course = Course::select('id', 'title', 'description', 'image', 'price', 'user_id')->with('user')->whereIn('id_categories', $array)->get();
            return $course;
        } else {
            return false;
        }
    }

    public function showPreferences()
    {
        $query = Preferences::select('categories_id')->where('user_id', auth()->user()->id);
        if ($query->exists()) {
            $data = $query->get();
            foreach ($data as $cat) {
                $array[] = $cat->categories_id;
            }
            $course = Course::select('id', 'title', 'description', 'image', 'price', 'user_id')->with('user')->whereIn('id_categories', $array)->get();
            return $course;
        } else {
            return false;
        }
    }

    public function showLastCourses()
    {
        $query = Category::select('id', 'name')->orderBy('id');
        if ($query->exists()) {
            $data = $query->get();
            $courses = [];
            foreach ($data as $cat) {
                $courses[] = [
                    $cat->name => Course::select('id', 'title', 'description', 'image', 'price', 'updated_at', 'user_id')->with('user')->where('id_categories', $cat->id)->orderBy('updated_at', 'DESC')->get()->take(6)
                ];
            }
            return $courses;
        } else {
            return false;
        }
    }

    public function lastCoursesReprod()
    {
        $lastCoursesRep = Course::join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->join('categories', 'courses.id_categories', '=', 'categories.id')
            ->join('course_level', 'courses.course_level_id', '=', 'course_level.id')
            ->where('purchased_courses.user_id', '=', auth()->user()->id)
            ->orderBy('purchased_courses.updated_at', 'DESC')
            ->select('courses.*', 'purchased_courses.display_time', 'purchased_courses.updated_at', 'categories.name as category_name', 'purchased_courses.last_class_reprod', 'course_level.description as level')
            ->distinct('courses.id')
            ->take(4)
            ->get();

        return $this->responseOk('', $lastCoursesRep);
    }

    public function searchCourses($str)
    {
        $user_type = auth()->user()->id_account_type;
        if ($user_type == 5) {
            $cursos = Course::join('categories', 'categories.id', '=', 'id_categories')->where('course_level_id', '=', 1)->where('title', 'like', '%' . $str . '%')->where('status', '=', 2)->where('marketplace_listed', 1)->select('courses.id', 'courses.title', 'categories.name as category_name', 'courses.price', 'courses.course_level_id', 'courses.url_portada', 'courses.portada')->get();
            return $cursos;
        } else {
            $cursos = Course::join('categories', 'categories.id', '=', 'id_categories')->where('title', 'like', '%' . $str . '%')->where('status', '=', 2)->where('marketplace_listed', 1)->select('courses.id', 'courses.title', 'categories.name as category_name', 'courses.price', 'courses.course_level_id', 'courses.url_portada', 'courses.portada')->get();
            $cursos = $this->addDiscount($user_type, $cursos);
            return $cursos;
        }
    }

    public function recommendedCourses()
    {
        $user_type = auth()->user()->id_account_type;
        $gustosUsuarioPorPreferencias = array();
        
        // Si el usuario es Socio Fundador (5), no accede a cursos de nivel avanzado
        $course_level_permitido = $user_type == 5 ? [1, 2] : [1, 2, 3];

        $user_id = auth()->user()->id;
        $cursosComprados = PurchasedCourse::where('user_id', $user_id)
            ->select('course_id')
            ->get();

        // Obtener las categorías de interés del usuario según sus compras y preferencias
        $gustosUsuarioPorCompras = PurchasedCourse::join('courses', 'courses.id', '=', 'course_id')
            ->join('categories', 'categories.id', '=', 'courses.id_categories')
            ->where('purchased_courses.user_id', '=', $user_id)
            ->select('categories.id')
            ->distinct()
            ->get()
            ->toArray();

        $gustosUsuarioPorPreferencias = Preferences::where('user_id', '=', $user_id)
            ->select('categories_id')
            ->get()
            ->toArray();

        // Si el usuario tiene 5 o menos categorías de interés por compras, se priorizan esas categorías para las recomendaciones
        // Si no, se combinan ambas fuentes de interés (compras y preferencias)
        if (count($gustosUsuarioPorCompras) <= 5) {

            $cursosRelacionados = Course::join('users', 'users.id', '=', 'user_id')
                ->whereIn('id_categories', $gustosUsuarioPorPreferencias)
                ->whereIn('course_level_id', $course_level_permitido)
                ->whereNotIn('courses.id', $cursosComprados)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->where('courses.user_id', '!=', auth()->user()->id)
                ->select(
                    'courses.id',
                    'courses.product_type_id',
                    'courses.title',
                    'courses.slug',
                    'courses.description',
                    'courses.path_url',
                    'courses.url_portada',
                    'courses.price',
                    'courses.user_id',
                    'courses.id_categories',
                    'courses.course_level_id',
                    'courses.status',
                    'courses.marketplace_listed',
                    'courses.created_at',
                    'courses.updated_at',
                    'courses.course_about',
                    'courses.will_learn',
                    'courses.prev_knowledge',
                    'courses.course_for',
                    'users.name',
                    'users.last_name'
                )
                ->distinct()
                ->inRandomOrder()
                ->get();
        } else {
            $gustosGenerales = array_merge($gustosUsuarioPorCompras, $gustosUsuarioPorPreferencias);

            $cursosRelacionados = Course::join('users', 'users.id', '=', 'user_id')
                ->whereIn('id_categories', $gustosGenerales)
                ->whereIn('course_level_id', $course_level_permitido)
                ->whereNotIn('courses.id', $cursosComprados)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->where('courses.user_id', '!=', auth()->user()->id)
                ->select(
                    'courses.id',
                    'courses.product_type_id',
                    'courses.title',
                    'courses.slug',
                    'courses.description',
                    'courses.path_url',
                    'courses.url_portada',
                    'courses.price',
                    'courses.user_id',
                    'courses.id_categories',
                    'courses.course_level_id',
                    'courses.status',
                    'courses.marketplace_listed',
                    'courses.created_at',
                    'courses.updated_at',
                    'courses.course_about',
                    'courses.will_learn',
                    'courses.prev_knowledge',
                    'courses.course_for',
                    'users.name',
                    'users.last_name'
                )
                ->distinct()
                ->inRandomOrder()
                ->get();
        }

        // Si el resultado de cursos relacionados es menor o igual a 5, se amplía la búsqueda solo por nivel de curso permitido, sin filtrar por categorías de interés
        if (count($cursosRelacionados) <= 5) {
            $cursosRelacionados = Course::join('users', 'users.id', '=', 'user_id')
                ->whereIn('course_level_id', $course_level_permitido)
                ->whereNotIn('courses.id', $cursosComprados)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->where('courses.user_id', '!=', auth()->user()->id)
                ->select(
                    'courses.id',
                    'courses.product_type_id',
                    'courses.title',
                    'courses.slug',
                    'courses.description',
                    'courses.path_url',
                    'courses.url_portada',
                    'courses.price',
                    'courses.user_id',
                    'courses.id_categories',
                    'courses.course_level_id',
                    'courses.status',
                    'courses.marketplace_listed',
                    'courses.created_at',
                    'courses.updated_at',
                    'courses.course_about',
                    'courses.will_learn',
                    'courses.prev_knowledge',
                    'courses.course_for',
                    'users.name',
                    'users.last_name'
                )
                ->distinct()
                ->inRandomOrder()
                ->get();
        }

        // Agregar descuento al precio de los cursos
        $cursosRelacionados = $this->addDiscount($user_type, $cursosRelacionados);
        return $this->responseOk('', $cursosRelacionados);
    }

    public function listAvailableBooks()
    {
        $books = Course::select(
            'courses.id',
            'courses.product_type_id',
            'courses.title',
            'courses.slug',
            'courses.description',
            'courses.price',
            'courses.url_portada',
            'courses.course_about',
            'courses.will_learn',
            'courses.course_for',
        )
        ->where('courses.product_type_id', 2)
        ->where('courses.status', 2)
        ->where('courses.marketplace_listed', 1)
        ->get();

        $books = $this->addDiscount(auth()->user()->id_account_type, $books);

        return $this->responseOk('', $books);
    }

    public function interestingCourses()
    {
        $data = Course::join('categories', 'courses.id_categories', '=', 'categories.id')
            ->join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->join('course_families', 'courses.id', '=', 'course_families.course_id')
            ->join('families', 'course_families.family_id', '=', 'families.id')
            ->where('purchased_courses.user_id', '=', auth()->user()->id)
            ->where('courses.user_id', '!=', auth()->user()->id)
            ->select('categories.id as category_id', 'courses.id as course_id', 'families.id as family_id')
            ->get()
            ->toArray();

        $families_id = array_column($data, 'family_id');
        $categories_id = array_column($data, 'category_id');
        $courses_id = array_column($data, 'course_id');

        $interestingCourses = Course::join('categories', 'courses.id_categories', '=', 'categories.id')
            ->join('course_level', 'courses.course_level_id', '=', 'course_level.id')
            ->join('course_families', 'courses.id', '=', 'course_families.course_id')
            ->join('families', 'course_families.family_id', '=', 'families.id')
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->whereIn('families.id', $families_id)
            ->whereIn('categories.id', $categories_id)
            ->whereNotIn('course_families.course_id', $courses_id)
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1)
            ->select('courses.*', 'categories.name as category_name', 'course_level.description as level', 'users.name', 'users.last_name')
            ->distinct('courses.id')
            ->inRandomOrder()
            ->take(10)
            ->get();

        $interestingCourses = $this->addDiscount(auth()->user()->id_account_type, $interestingCourses);

        return $this->responseOk('', $interestingCourses);
    }

    public function releasedCourses()
    {
        $user_type = auth()->user()->id_account_type;

        $user_id = auth()->user()->id;
        $course_level_permitido = [1, 2, 3];
        $preferenciasUsuario = Preferences::select('categories_id')->where('user_id', $user_id)->get();
        $cursosComprados = PurchasedCourse::where('user_id', $user_id)
            ->select('course_id')
            ->get();

        $newCourses = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->select('courses.*', 'categories.name as category_name', 'users.name', 'users.last_name')
            ->whereNotIn('courses.id', $cursosComprados)
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1)
            ->where('courses.user_id', '!=', auth()->user()->id)
            ->whereIn('courses.course_level_id', $course_level_permitido)
            ->whereIn('categories.id', $preferenciasUsuario)
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();

        if (count($newCourses) <= 5) {
            $newCourses = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
                ->join('users', 'courses.user_id', '=', 'users.id')
                ->select('courses.*', 'categories.name as category_name', 'users.name', 'users.last_name')
                ->whereNotIn('courses.id', $cursosComprados)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->where('courses.user_id', '!=', auth()->user()->id)
                ->whereIn('courses.course_level_id', $course_level_permitido)
                ->orderBy('created_at', 'DESC')
                ->take(10)
                ->get();
        }

        $newCourses = $this->addDiscount($user_type, $newCourses);
        return $this->responseOk('', $newCourses);
    }

    public function purchasedCourses()
    {
        $courses = Course::join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->where('purchased_courses.user_id', auth()->user()->id)
            ->select('courses.id', 'courses.title', 'courses.url_portada', 'users.name', 'users.last_name', 'users.photo', 'courses.ranking_by_user')
            ->get();

        foreach ($courses as $course) {
            $course->photo = ParseUrl::contacAtrrS3($course->photo);
        }

        return $this->responseOk('', $courses);
    }

    public function setPurshaseCouse(Request $request)
    {
        try {
            Log::info('Solicitud de compra de curso recibida', [
                'course_id' => $request->id_course,
                'type_purchase' => $request->type_purchase,
                'user_id' => $request->user_id
            ]);

            // Validar que todos los campos necesarios estén presentes
            if (!$request->id_course || !$request->type_purchase || !$request->user_id) {
                throw new \Exception('Faltan datos requeridos para la compra');
            }

            $result = app(CartController::class)->buyCourse($request);

            // Agregar más logging para ver qué devuelve buyCourse
            Log::info('Resultado de buyCourse', ['result' => $result]);

            if (!$result) {
                throw new \Exception('No se obtuvo respuesta del proceso de compra');
            }

            // Si llegamos aquí, la compra fue exitosa
            return response()->json([
                'status' => 'ok',
                'message' => 'Compra realizada con éxito',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error en setPurshaseCouse', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storage_file($courses)
    {
        $path = public_path('storage');
        $storage_domain = config('global_variables.storage_domain');
        $environment = config('global_variables.environment');

        if ($environment == 'local') {
            foreach ($courses as $course) {
                $course->url_portada = str_replace('\\', '/', $path) . '/' . $course->url_portada;
                $course->photo = ParseUrl::contacAtrrS3($course->photo);
            }
        } else {
            foreach ($courses as $course) {
                $course->url_portada = $storage_domain . '/' . $course->url_portada;
                $course->photo = ParseUrl::contacAtrrS3($course->photo);
            }
        }
        return $courses;
    }

    // Return exam with questions
    public function exam(Request $request)
    {
        $actual_exam = Exam::where('id', $request->exam_id)->get()->first();
        $questions = ExamQuestion::where('exam_id', $actual_exam->id)->get();
        foreach ($questions as $question) {
            $json[] = array(
                'id'        => $question->id,
                'title'     => $question->title,
                'options'     => $question->options
            );
        }
        $exam = array(
            "exam" => $actual_exam,
            "questions" => $questions
        );
        return $this->responseOk('', $exam);
    }

    //subir comentario y rating de un Curso
    public function rateCourseStore(Request $request)
    {
        $user = Auth::user();

        $userset = CourseRate::where([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
        ])->first();

        if (!$userset) {
            try {
                DB::beginTransaction();
                $courseRate = new CourseRate();
                $courseRate->user_id = $user->id;
                $courseRate->course_id = $request->course_id;
                $courseRate->rate = $request->rate;
                $courseRate->commentary = $request->commentary;


                if ($courseRate->save()) {
                    $avg = CourseRate::where('course_id', '=', $request->course_id)->avg('rate');
                    $course = Course::findOrFail($request->course_id);
                    $course->ranking_by_user = $avg;
                    $course->save();
                    $response['status'] = 'ok';
                    //return 'se ha subido su comentario y valoracion del curso';
                } else {
                    $response['status']  = 'error';
                }
                echo json_encode($response);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        } else {
            try {
                DB::beginTransaction();

                $userset->rate = $request->rate;
                $userset->commentary = $request->commentary;

                if ($userset->save()) {
                    $avg = CourseRate::where('course_id', '=', $request->course_id)->avg('rate');
                    $course = Course::findOrFail($request->course_id);
                    $course->ranking_by_user = $avg;
                    $course->save();
                    $response['status'] = 'ok';
                    //return 'se ha actualizado su comentario y valoracion del curso';
                } else {
                    $response['status']  = 'error';
                }
                echo json_encode($response);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        }
    }

    //traer todos los comentarios y valoraciones de un curso
    public function rateCourseShow($id)
    {

        $rates = User::join('course_rates', 'course_rates.user_id', '=', 'users.id')
            ->join('courses', 'course_rates.course_id', '=', 'courses.id')
            ->where('course_id', $id)->select('users.id', 'users.name', 'users.photo', 'course_rates.rate', 'course_rates.commentary', 'course_rates.created_at')
            ->get();

        $course = Course::select('user_id', 'title', 'ranking_by_user', 'url_portada')->where('id', $id)->get()->first();
        $productor = User::select('id', 'name', 'last_name', 'photo')->where('id', $course->user_id)->get()->first();


        return array(
            'rates' => $rates,
            'course' => $course,
            'productor' => $productor
        );
    }

    public function addDiscount($id_account_type, $courses_list)
    {
        //agregar descuento al precio del curso
        $account_type = AccountType::find($id_account_type);
        foreach ($courses_list as $course) {
            $course->owner = true;

            $type_certificate = CourseConfiguration::where('course_id', $course->id)->get()->first();

            if (isset($type_certificate->type_certificate)) {

                if ($type_certificate->type_certificate == 1) {
                    $course->price_with_discount = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
                    $course->du = $account_type->disc_purchases_course;
                } else {

                    $discountsCertificate = round($type_certificate->data['certificate_price'] - ($type_certificate->data['certificate_price'] * $account_type->disc_purchases_certificates) / 100, 2);
                    $discountCourse = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
                    $course->price_with_discount = $discountsCertificate + $discountCourse;

                    $course->du = $account_type->disc_purchases_course;
                }
            } else {
                $course->price_with_discount = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
                $course->du = $account_type->disc_purchases_course;;
            }
        }
        return $courses_list;
    }

    public function listActiveCourses()
    {
        $user = auth()->user()->id;
        $user_type = auth()->user()->id_account_type;
        $courses = Course::where('user_id', $user)
            ->where('status', 2)
            ->get();
        $courses = $this->addDiscount($user_type, $courses);
        return $this->responseOk('', $courses);
    }

    public function expirationDate(Request $request)
    {
        $product = Course::findOrFail($request->course_id);
        $fecha = PurchasedCourse::where('course_id', $request->course_id)
            ->where('user_id', auth()->user()->id)
            ->select('created_at')
            ->first();

        $fechaParse = Carbon::parse($fecha->created_at);
        $current = Carbon::now();
        $fechaCompra = $fechaParse->addMonths($product->months);

        $fechaInicio = Carbon::parse($fecha->created_at)->toDateString();
        $daysUntil =  $fechaParse->diff($current)->days;
        $fechaVencimiento = $fechaCompra->toDateString();
        $data = [
            'fechaInicio' => $fechaInicio,
            'daysUntil' => $daysUntil,
            'fechaVencimiento' => $fechaVencimiento
        ];
        return $data;
    }
}