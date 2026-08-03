<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Course;
use App\Models\Ebook;
use App\Models\EbookUser;
use App\Models\Masterclass;
use App\Models\MasterclassUser;
use App\Models\Minicourse;
use App\Models\MiniCourseUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrInfoproductController extends Controller
{
    /**
     * GET /api/v1/me/infoproducts
     */
    public function myInfoproducts(Request $request)
    {
        $userId = auth()->id();
        $type = $request->get('type');
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);

        $query = Course::where('user_id', $userId)->where('status', 2);

        if ($type === 'course') {
            $query->where('product_type_id', 1);
        } elseif ($type === 'book') {
            $query->where('product_type_id', 2);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $infoproducts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        foreach ($infoproducts as $item) {
            $item->url_portada = ParseUrl::contacAtrrS3($item->url_portada);
        }

        return response()->json($infoproducts);
    }

    /**
     * GET /api/v1/marketing/tools
     */
    public function marketingTools()
    {
        $userId = auth()->id();

        $masterclasses = Masterclass::where('user_id', $userId)->get()->map(function ($mc) {
            return [
                'id' => $mc->id,
                'type' => 'masterclass',
                'title' => $mc->title,
                'fecha' => $mc->date_event ?? $mc->created_at,
                'distribuidores' => MasterclassUser::where('masterclass_id', $mc->id)->whereNotNull('distributor_id')->count(),
                'participantes' => MasterclassUser::where('masterclass_id', $mc->id)->count(),
            ];
        });

        $minicourses = Minicourse::where('user_id', $userId)->get()->map(function ($mc) {
            return [
                'id' => $mc->id,
                'type' => 'minicourse',
                'title' => $mc->title,
                'fecha' => $mc->created_at,
                'distribuidores' => MiniCourseUser::where('mini_course_id', $mc->id)->whereNotNull('distributor_id')->count(),
                'participantes' => MiniCourseUser::where('mini_course_id', $mc->id)->count(),
            ];
        });

        $ebooks = Ebook::where('user_id', $userId)->get()->map(function ($eb) {
            return [
                'id' => $eb->id,
                'type' => 'ebook',
                'title' => $eb->title,
                'fecha' => $eb->created_at,
                'distribuidores' => EbookUser::where('ebook_id', $eb->id)->whereNotNull('distributor_id')->count(),
                'participantes' => EbookUser::where('ebook_id', $eb->id)->count(),
            ];
        });

        $tools = $masterclasses->merge($minicourses)->merge($ebooks)->sortByDesc('fecha')->values();

        return response()->json([
            'data' => $tools,
            'message' => 'Listado de herramientas de marketing',
        ]);
    }

    /**
     * GET /api/v1/marketing/marketplace/masterclass/list
     */
    public function masterclassList()
    {
        $masterclasses = Masterclass::join('users', 'masterclasses.user_id', '=', 'users.id')
            ->select('masterclasses.*', 'users.name as producer_name', 'users.last_name as producer_lastname')
            ->get();

        return response()->json($masterclasses);
    }

    /**
     * GET /api/v1/marketing/marketplace/ebooks/list
     */
    public function ebooksList()
    {
        $ebooks = Ebook::join('users', 'ebooks.user_id', '=', 'users.id')
            ->select('ebooks.*', 'users.name as producer_name', 'users.last_name as producer_lastname')
            ->get();

        return response()->json($ebooks);
    }

    /**
     * GET /api/v1/marketing/marketplace/minicourses/list
     */
    public function miniCoursesList()
    {
        $minicourses = Minicourse::join('users', 'mini_courses.user_id', '=', 'users.id')
            ->select('mini_courses.*', 'users.name as producer_name', 'users.last_name as producer_lastname')
            ->get();

        return response()->json($minicourses);
    }

    /**
     * GET /api/v1/marketing/{id}/list-students
     */
    public function listStudentsMasterclass($id)
    {
        $students = MasterclassUser::join('users', 'masterclass_user.user_id', '=', 'users.id')
            ->leftJoin('users as distributor', 'masterclass_user.distributor_id', '=', 'distributor.id')
            ->where('masterclass_user.masterclass_id', $id)
            ->select(
                'users.id',
                'users.name',
                'users.last_name as lastname',
                'users.email',
                'users.phone',
                'masterclass_user.status',
                'distributor.name as distributor_name',
                'masterclass_user.created_at'
            )
            ->get();

        return response()->json($students);
    }

    /**
     * GET /api/v1/marketing/{id}/list-students/minicourse
     */
    public function listStudentsMinicourse($id)
    {
        $students = MiniCourseUser::join('users', 'mini_course_user.user_id', '=', 'users.id')
            ->leftJoin('users as distributor', 'mini_course_user.distributor_id', '=', 'distributor.id')
            ->where('mini_course_user.mini_course_id', $id)
            ->select(
                'users.id',
                'users.name',
                'users.last_name as lastname',
                'users.email',
                'users.phone',
                'mini_course_user.status',
                'distributor.name as distributor_name',
                'mini_course_user.created_at'
            )
            ->get();

        return response()->json($students);
    }

    /**
     * GET /api/v1/marketing/{id}/list-students/ebook
     */
    public function listStudentsEbook($id)
    {
        $students = EbookUser::join('users', 'ebook_user.user_id', '=', 'users.id')
            ->leftJoin('users as distributor', 'ebook_user.distributor_id', '=', 'distributor.id')
            ->where('ebook_user.ebook_id', $id)
            ->select(
                'users.id',
                'users.name',
                'users.last_name as lastname',
                'users.email',
                'users.phone',
                'ebook_user.status',
                'distributor.name as distributor_name',
                'ebook_user.created_at'
            )
            ->get();

        return response()->json($students);
    }

    /**
     * POST /api/v1/masterclass/register-masterclass/{id}
     */
    public function registerMasterclass($id)
    {
        $userId = auth()->id();

        MasterclassUser::firstOrCreate([
            'masterclass_id' => $id,
            'user_id' => $userId,
        ], [
            'status' => 'CONFIRMADO',
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Registrado en la masterclass con éxito',
        ]);
    }

    /**
     * POST /api/v1/masterclass/create-invitation/{id}
     */
    public function createInvitationMasterclass($id)
    {
        $userId = auth()->id();
        $code = 'DIST-' . $userId . '-' . $id;

        return response()->json([
            'status' => 'ok',
            'invitation_link' => url("/register-masterclass?ref={$code}&id={$id}"),
        ]);
    }

    /**
     * POST /api/v1/marketing/mini-course/invitation-link/{id}
     */
    public function createInvitationMinicourse($id)
    {
        $userId = auth()->id();
        $code = 'DIST-' . $userId . '-' . $id;

        return response()->json([
            'status' => 'ok',
            'invitation_link' => url("/minicourse/invite?ref={$code}&id={$id}"),
        ]);
    }

    /**
     * POST /api/v1/marketing/ebook/invitation-link/{id}
     */
    public function createInvitationEbook($id)
    {
        $userId = auth()->id();
        $code = 'DIST-' . $userId . '-' . $id;

        return response()->json([
            'status' => 'ok',
            'invitation_link' => url("/ebook/invite?ref={$code}&id={$id}"),
        ]);
    }
}
