<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\AccountType;
use App\Models\Course;
use App\Models\CourseConfiguration;
use App\Models\Preferences;
use App\Models\PurchasedCourse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrCourseController extends Controller
{
    /**
     * GET /api/v1/course/purchased-courses
     */
    public function purchasedCourses()
    {
        $courses = Course::join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->where('purchased_courses.user_id', auth()->user()->id)
            ->select(
                'courses.id',
                'courses.title',
                'courses.url_portada',
                'users.name',
                'users.last_name',
                'users.photo',
                'courses.ranking_by_user'
            )
            ->get();

        foreach ($courses as $course) {
            $course->photo = ParseUrl::contacAtrrS3($course->photo);
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $courses,
        ]);
    }

    /**
     * GET /api/v1/course/last-courses-rep
     */
    public function lastCoursesRep()
    {
        $courses = Course::join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->join('categories', 'courses.id_categories', '=', 'categories.id')
            ->leftJoin('course_level', 'courses.course_level_id', '=', 'course_level.id')
            ->where('purchased_courses.user_id', auth()->user()->id)
            ->select(
                'courses.id',
                'courses.title',
                'purchased_courses.display_time',
                'purchased_courses.updated_at',
                'categories.name as category_name',
                'purchased_courses.last_class_reprod',
                'course_level.name as level'
            )
            ->orderBy('purchased_courses.updated_at', 'DESC')
            ->take(4)
            ->get();

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $courses,
        ]);
    }

    /**
     * GET /api/v1/course/related-courses
     */
    public function relatedCourses()
    {
        $user_type = auth()->user()->id_account_type;
        $user_id = auth()->user()->id;
        $course_level_permitido = $user_type == 5 ? [1, 2] : [1, 2, 3];

        $cursosComprados = PurchasedCourse::where('user_id', $user_id)
            ->select('course_id')
            ->get();

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

        $cursosRelacionados = $this->addDiscount($user_type, $cursosRelacionados);

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $cursosRelacionados,
        ]);
    }

    /**
     * GET /api/v1/course/released-courses
     */
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

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $newCourses,
        ]);
    }

    /**
     * GET /api/v1/course/interesting-courses
     */
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

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $interestingCourses,
        ]);
    }

    /**
     * Auxiliar para calcular precio con descuento segun el tipo de cuenta.
     */
    protected function addDiscount($id_account_type, $courses_list)
    {
        $account_type = AccountType::find($id_account_type);
        if (!$account_type) {
            return $courses_list;
        }

        foreach ($courses_list as $course) {
            $course->owner = true;
            $type_certificate = CourseConfiguration::where('course_id', $course->id)->first();

            if (isset($type_certificate->type_certificate)) {
                if ($type_certificate->type_certificate == 1) {
                    $course->price_with_discount = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
                    $course->du = $account_type->disc_purchases_course;
                } else {
                    $certPrice = isset($type_certificate->data['certificate_price']) ? (float) $type_certificate->data['certificate_price'] : 0;
                    $discountsCertificate = round($certPrice - ($certPrice * $account_type->disc_purchases_certificates) / 100, 2);
                    $discountCourse = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
                    $course->price_with_discount = $discountsCertificate + $discountCourse;
                    $course->du = $account_type->disc_purchases_course;
                }
            } else {
                $course->price_with_discount = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
                $course->du = $account_type->disc_purchases_course;
            }
        }
        return $courses_list;
    }

    /**
     * GET /api/v1/reports/last-sells
     */
    public function lastSells(Request $request)
    {
        $n_sells = (int) $request->get('n_sells', 3);

        $lastSells = PurchasedCourse::join('users', 'purchased_courses.user_id', '=', 'users.id')
            ->join('courses', 'purchased_courses.course_id', '=', 'courses.id')
            ->where('purchased_courses.user_id', auth()->user()->id)
            ->select(
                'users.id',
                'users.photo',
                'users.name as client',
                'users.last_name as client_last_name',
                'courses.title',
                'courses.price',
                'purchased_courses.created_at'
            )
            ->orderBy('purchased_courses.created_at', 'DESC')
            ->take($n_sells)
            ->get();

        foreach ($lastSells as $item) {
            $item->photo = ParseUrl::contacAtrrS3($item->photo);
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $lastSells,
        ]);
    }

    /**
     * GET /api/v1/course/details/{id}
     */
    public function detailsCourse($id)
    {
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
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        $course->owner = $course->user_id == auth()->id();
        $account_type = AccountType::find(auth()->user()->id_account_type);

        if ($account_type) {
            $discount = ($course->price * $account_type->disc_purchases_course) / 100;
            $course->price_with_discount = round($course->price - $discount, 2);
        } else {
            $course->price_with_discount = $course->price;
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Detalles del curso',
            'data' => $course,
        ]);
    }

    /**
     * GET /api/v1/course/list
     */
    public function listCourses()
    {
        $courses = Course::where('status', 2)
            ->where('marketplace_listed', 1)
            ->take(12)
            ->get();

        $courses = $this->addDiscount(auth()->user()->id_account_type, $courses);

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $courses,
        ]);
    }

    /**
     * GET /api/v1/course/list/random
     */
    public function listRandom()
    {
        $courses = Course::where('status', 2)
            ->where('marketplace_listed', 1)
            ->inRandomOrder()
            ->take(6)
            ->get();

        $courses = $this->addDiscount(auth()->user()->id_account_type, $courses);

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $courses,
        ]);
    }

    /**
     * GET /api/v1/course/search-courses/{str}
     */
    public function searchCourses($str)
    {
        $userType = auth()->user()->id_account_type;
        $query = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
            ->where('courses.title', 'like', '%' . $str . '%')
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1);

        if ($userType == 5) {
            $query->where('courses.course_level_id', 1);
        }

        $courses = $query->select('courses.id', 'courses.title', 'categories.name as category_name', 'courses.price', 'courses.course_level_id', 'courses.url_portada')->get();
        $courses = $this->addDiscount($userType, $courses);

        return response()->json($courses);
    }

    /**
     * GET /api/v1/course/list-available-books
     */
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
            'courses.course_for'
        )
            ->where('courses.product_type_id', 2)
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1)
            ->get();

        $books = $this->addDiscount(auth()->user()->id_account_type, $books);

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $books,
        ]);
    }
}
