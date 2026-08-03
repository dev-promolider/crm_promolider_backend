<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Badge;
use App\Models\BadgeDetail;
use App\Models\ClassroomPointDetail;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserClassroomPoint;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class VcrRewardsController extends Controller
{
    /**
     * GET /api/v1/badges/my-progress
     */
    public function myProgress()
    {
        $userId = auth()->id();
        $myBadges = BadgeDetail::where('user_id', $userId)->pluck('badge_id')->toArray();

        $badgesRemaining = Badge::select('id', 'name', 'description', 'icon', 'level')
            ->whereNotIn('id', $myBadges)
            ->get()
            ->toArray();

        $myBadgesList = Badge::join('badge_detail', 'badge_detail.badge_id', '=', 'badges.id')
            ->where('badge_detail.user_id', $userId)
            ->select('badges.id', 'badges.name', 'badges.description', 'badges.icon', 'badges.level')
            ->get()
            ->toArray();

        for ($i = 0; $i < count($badgesRemaining); $i++) {
            $badgesRemaining[$i]['obtained'] = false;
            $badgesRemaining[$i]['icon'] = ParseUrl::contacAtrrS3($badgesRemaining[$i]['icon']);
        }

        for ($i = 0; $i < count($myBadgesList); $i++) {
            $myBadgesList[$i]['obtained'] = true;
            $myBadgesList[$i]['icon'] = ParseUrl::contacAtrrS3($myBadgesList[$i]['icon']);
        }

        $badges = array_merge($myBadgesList, $badgesRemaining);

        return response()->json($badges);
    }

    /**
     * GET /api/v1/badges/my-badges
     */
    public function myBadges()
    {
        $userId = auth()->id();
        $badges = Badge::join('badge_detail', 'badge_detail.badge_id', '=', 'badges.id')
            ->where('badge_detail.user_id', $userId)
            ->select('badges.id', 'badges.name', 'badges.description', 'badges.icon', 'badges.level')
            ->get();

        foreach ($badges as $b) {
            $b->icon = ParseUrl::contacAtrrS3($b->icon);
        }

        return response()->json($badges);
    }

    /**
     * GET /api/v1/badges/list
     */
    public function listBadges()
    {
        $badges = Badge::all();
        foreach ($badges as $b) {
            $b->icon = ParseUrl::contacAtrrS3($b->icon);
        }

        return response()->json($badges);
    }

    /**
     * GET /api/v1/classroom-points/ranking
     */
    public function ranking()
    {
        $ranking = DB::table('users')
            ->join('user_classroom_points', 'users.id', '=', 'user_classroom_points.id_user')
            ->orderBy('user_classroom_points.total_points', 'DESC')
            ->select('users.id', 'users.photo', 'users.name', 'user_classroom_points.total_points as total')
            ->take(10)
            ->get();

        foreach ($ranking as $r) {
            $r->photo = ParseUrl::contacAtrrS3($r->photo);
        }

        return response()->json($ranking);
    }

    /**
     * POST /api/v1/classroom-points/insert-user-points
     */
    public function insertUserPoints(Request $request)
    {
        $user = auth()->user();
        // CRM-06: Restringir la inserción manual de puntos a administradores/profesores
        $isAdminOrTeacher = $user && (
            (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Teacher')))
            || ($user->id_account_type ?? null) == 1
        );

        if (!$isAdminOrTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorizacion para otorgar puntos directamente.',
            ], 403);
        }

        $userId = auth()->id();
        $incrementPoints = (int) $request->input('increment_points', 0);
        $description = $request->input('description', 'Puntos ganados');

        $userPoints = UserClassroomPoint::firstOrCreate(
            ['id_user' => $userId],
            ['total_points' => 0]
        );

        ClassroomPointDetail::create([
            'id_user_classroom_points' => $userPoints->id,
            'increment_points' => $incrementPoints,
            'description' => $description,
        ]);

        $userPoints->total_points += $incrementPoints;
        $userPoints->save();

        return response()->json([
            'success' => true,
            'total_points' => $userPoints->total_points,
        ]);
    }

    /**
     * GET /api/v1/profile/points/{id}
     */
    public function profilePoints($id)
    {
        $totalPoints = UserClassroomPoint::where('id_user', $id)->value('total_points') ?? 0;

        return response()->json([
            'status' => 'ok',
            'points' => (int) $totalPoints,
        ]);
    }

    /**
     * GET /api/v1/rewards
     */
    public function rewardsList()
    {
        $rewards = Reward::where('active', 1)
            ->where('stock', '>', 0)
            ->orderBy('cost', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * GET /api/v1/rewards/credits
     */
    public function rewardsCredits()
    {
        $user = auth()->user();
        $credits = $user->credits ?? 0;

        return response()->json([
            'success' => true,
            'credits' => (int) $credits,
        ]);
    }

    /**
     * POST /api/v1/rewards/redeem
     */
    public function redeemReward(Request $request)
    {
        $userId = auth()->id();
        $rewardId = $request->input('reward_id');

        $reward = Reward::find($rewardId);
        if (!$reward || !$reward->active || $reward->stock <= 0) {
            return response()->json(['success' => false, 'message' => 'Premio no disponible o sin stock'], 400);
        }

        $user = User::find($userId);
        if (($user->credits ?? 0) < $reward->cost) {
            return response()->json(['success' => false, 'message' => 'Créditos insuficientes'], 400);
        }

        $user->credits -= $reward->cost;
        $user->save();

        $reward->decrement('stock');

        $redemption = RewardRedemption::create([
            'user_id' => $userId,
            'reward_id' => $rewardId,
            'cost' => $reward->cost,
            'status' => 'processed',
            'notes' => 'Canje procesado exitosamente',
        ]);

        if ($rewardId == 1) {
            $user->id_account_type = 2; // School Membership
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Premio canjeado con éxito',
            'data' => $redemption,
        ]);
    }

    /**
     * GET /api/v1/rewards/my-redemptions
     */
    public function myRedemptions()
    {
        $userId = auth()->id();
        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $redemptions,
        ]);
    }
}
