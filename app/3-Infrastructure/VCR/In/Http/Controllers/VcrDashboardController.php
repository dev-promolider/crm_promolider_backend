<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Models\User;
use App\Models\Badge;
use App\Models\BadgeDetail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class VcrDashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard/getattributes
     */
    public function getAttributes()
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
        }

        $totalPayments = (float) $user->paymentsSponsor()->sum('amount');
        $totalCourses = (int) $user->courses()->count();
        $accountType = $user->accountType ? $user->accountType->account : '';
        $totalClients = (int) User::myClients($user->id)->count();
        $roles = $user->getRoleNames();
        $role = isset($roles[0]) ? $roles[0] : '';

        $data = [
            'totalPayments' => $totalPayments,
            'totalCourses' => $totalCourses,
            'accountType' => $accountType,
            'totalClients' => $totalClients,
            'role' => $role,
        ];

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/dashboard/lastlessonseen
     */
    public function lastLessonSeen()
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json(null);
        }

        $lessons = $user->lessons;
        $lesson = $lessons ? $lessons->last() : null;
        if ($lesson) {
            $module = $lesson->module;
            if ($module) {
                $module->course;
            }
        }

        return response()->json($lesson);
    }

    /**
     * GET /api/v1/user/get-data-currentuser
     */
    public function getDataCurrentUser()
    {
        $id = auth()->user()->id;
        $data = User::where('users.id', $id)
            ->join('country', 'users.id_country', '=', 'country.id')
            ->select('users.*', 'country.name AS countryName')
            ->first();

        return response()->json($data, 200);
    }

    /**
     * GET /api/v1/classroom-points/ranking
     */
    public function ranking()
    {
        $ranking = DB::table('users')
            ->orderBy('user_classroom_points.total_points', 'DESC')
            ->join('user_classroom_points', 'users.id', '=', 'user_classroom_points.id_user')
            ->select('users.id', 'users.photo', 'users.name', 'user_classroom_points.total_points as total')
            ->take(10)
            ->get();

        return response()->json($ranking);
    }

    /**
     * GET /api/v1/badges/my-progress
     */
    public function myProgress()
    {
        $user_id = auth()->user()->id;
        $my_badges = BadgeDetail::where('user_id', $user_id)
            ->select('badge_id')
            ->get();

        $badges_remaining = Badge::select('id', 'name', 'description', 'icon', 'level')
            ->whereNotIn('id', $my_badges)
            ->get()
            ->toArray();

        $my_badges_list = Badge::join('badge_detail', 'badge_detail.badge_id', '=', 'badges.id')
            ->where('badge_detail.user_id', $user_id)
            ->select('badges.id', 'badges.name', 'badges.description', 'badges.icon', 'badges.level')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($badges_remaining); $i++) {
            $badges_remaining[$i]['obtained'] = false;
        }

        for ($i = 0; $i < count($my_badges_list); $i++) {
            $my_badges_list[$i]['obtained'] = true;
        }

        $badges = array_merge($my_badges_list, $badges_remaining);

        return response()->json($badges, 200);
    }
}
