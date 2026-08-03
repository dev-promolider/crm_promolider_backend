<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Classified;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class VcrBinaryTreeController extends Controller
{
    /**
     * GET /api/v1/ramabinaria/listbinary
     */
    public function listbinary()
    {
        $userId = auth()->id();

        $classifieds = DB::table('classified')
            ->join('users', 'users.id', '=', 'classified.user_id')
            ->where('classified.id_user_sponsor', $userId)
            ->select(
                'classified.id',
                'users.id as user_id',
                'users.name',
                'users.last_name as lastname',
                'classified.position',
                'classified.user_above',
                'classified.id_user_sponsor as sponsor_id',
                'users.expiration_membership_date'
            )
            ->get();

        return response()->json($classifieds);
    }

    /**
     * GET /api/v1/ramabinaria/binaryTree/{id?}
     */
    public function binaryTree($id = null)
    {
        $rootUserId = $id ? (int)$id : auth()->id();

        $allUsers = DB::table('users as u')
            ->leftJoin('classified as c', 'u.id', '=', 'c.user_id')
            ->select([
                'u.id',
                'u.name',
                'u.last_name',
                'u.photo',
                'u.expiration_membership_date',
                'u.id_account_type',
                'c.id_user_sponsor'
            ])
            ->get();

        $allClassifiedData = DB::table('classified')
            ->select(['user_id', 'position', 'user_above'])
            ->whereNotNull('user_above')
            ->get();

        $usersMap = [];
        foreach ($allUsers as $user) {
            $usersMap[$user->id] = $user;
        }

        $childrenMap = [];
        foreach ($allClassifiedData as $row) {
            $parentId = (int) $row->user_above;
            if (!$parentId) continue;

            if (!isset($childrenMap[$parentId])) {
                $childrenMap[$parentId] = ['left' => [], 'right' => []];
            }

            if ($row->position == 0) {
                $childrenMap[$parentId]['left'][] = $row->user_id;
            } elseif ($row->position == 1) {
                $childrenMap[$parentId]['right'][] = $row->user_id;
            }
        }

        if (!isset($usersMap[$rootUserId])) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $tree = $this->buildTreeRecursive($rootUserId, $usersMap, $childrenMap, 0);

        return response()->json($tree);
    }

    private function buildTreeRecursive($userId, $usersMap, $childrenMap, $level = 0)
    {
        if ($level > 15 || !isset($usersMap[$userId])) {
            return null;
        }

        $user = $usersMap[$userId];

        $node = [
            'id' => $user->id,
            'name' => trim($user->name . ' ' . ($user->last_name ?? '')),
            'photo' => ParseUrl::contacAtrrS3($user->photo ?? ''),
            'expiration_membership_date' => $user->expiration_membership_date,
            'id_account_type' => $user->id_account_type,
            'id_user_sponsor' => $user->id_user_sponsor,
            'left' => [
                'id' => 0,
                'name' => 'Disponible',
                'photo' => 'https://cdn-icons-png.flaticon.com/512/1828/1828817.png',
                'children' => [],
            ],
            'right' => [
                'id' => 0,
                'name' => 'Disponible',
                'photo' => 'https://cdn-icons-png.flaticon.com/512/1828/1828817.png',
                'children' => [],
            ],
        ];

        if (isset($childrenMap[$userId])) {
            $children = $childrenMap[$userId];

            if (!empty($children['left'])) {
                $leftChildId = $children['left'][0];
                $leftSub = $this->buildTreeRecursive($leftChildId, $usersMap, $childrenMap, $level + 1);
                if ($leftSub) {
                    $node['left'] = $leftSub;
                }
            }

            if (!empty($children['right'])) {
                $rightChildId = $children['right'][0];
                $rightSub = $this->buildTreeRecursive($rightChildId, $usersMap, $childrenMap, $level + 1);
                if ($rightSub) {
                    $node['right'] = $rightSub;
                }
            }
        }

        return $node;
    }

    /**
     * GET /content/reports/starting_bonus
     */
    public function startingBonus()
    {
        $userId = auth()->id();
        $qualifieds = Classified::join('users', 'users.id', '=', 'classified.user_id')
            ->where('classified.id_user_sponsor', $userId)
            ->select('classified.*', 'users.name', 'users.last_name', 'users.email')
            ->get();

        return response()->json([
            'status' => 'ok',
            'report' => 'Bono de Inicio',
            'data' => $qualifieds,
        ]);
    }

    /**
     * GET /content/reports/growth_bonus
     */
    public function growthBonus()
    {
        $userId = auth()->id();
        $qualifieds = Classified::join('users', 'users.id', '=', 'classified.user_id')
            ->where('classified.id_user_sponsor', $userId)
            ->select('classified.*', 'users.name', 'users.last_name', 'users.email')
            ->get();

        return response()->json([
            'status' => 'ok',
            'report' => 'Bono de Crecimiento',
            'data' => $qualifieds,
        ]);
    }
}
