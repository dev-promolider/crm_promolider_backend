<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Traits\ResponseFormat;
use App\Models\PurchasedCourse;
use App\Models\BinaryCutHistory;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ReportsController extends Controller
{
    use ResponseFormat;

    public function lastSells(Request $request)
    {
        try {
            $n_sells = $request->n_sells;

            $lastSells = PurchasedCourse::join('users', 'purchased_courses.user_id', '=', 'users.id')
                ->join('courses', 'purchased_courses.course_id', '=', 'courses.id')
                ->where('purchased_courses.user_id', auth()->user()->id)
                ->select('users.id', 'users.photo', 'courses.title', 'courses.price')
                ->take($n_sells)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $lastSells,
                'message' => 'Data recuperada con exito'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error' . $th->getMessage(),
            ]);
        }
    }

    public function viewOption()
    {
        $user = auth()->user();
        $user_role = $user->getRoleNames()->first();
        $role = $user_role == 'Admin' ? 1 : 0;
        return view('content.reports.mywallet', compact('role'));
    }

    public function myPurchase()
    {
        $user = auth()->user();
        $user_role = $user->getRoleNames()->first();
        $role = $user_role == 'Admin' ? 1 : 0;
        return view('content.reports.mypurchase', compact('role'));
    }

    public function config()
    {
        $user = auth()->user();
        $user_role = $user->getRoleNames()->first();
        $role = $user_role == 'Admin' ? 1 : 0;
        return view('content.reports.config', compact('role'));
    }

    public function mySales()
    {
        $user = auth()->user();
        $user_role = $user->getRoleNames()->first();
        $role = $user_role == 'Admin' ? 1 : 0;
        return view('content.reports.mysales', compact('role'));
    }

    public function historial()
    {
        $user = auth()->user();
        $user_role = $user->getRoleNames()->first();
        $role = $user_role == 'Admin' ? 1 : 0;
        return view('content.reports.historial', compact('role'));
    }

    public function getBinaryHistory(Request $request)
    {
        $query = BinaryCutHistory::where('user_id', auth()->id())
            ->with(['rank']);

        // Búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('rank', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('created_at', 'like', "%{$search}%");
        }

        // Ordenamiento
        $sortKey = $request->input('sort_key', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Mapeo de claves de ordenamiento para relaciones
        $sortMap = [
            'rank.name' => 'rank_id',
            'created_at' => 'created_at'
        ];

        $query->orderBy(
            $sortMap[$sortKey] ?? $sortKey,
            $sortOrder
        );

        // Paginación
        $perPage = $request->input('per_page', 10);
        $histories = $query->paginate($perPage);

        return response()->json($histories);
    }

    // public function proyeccion()
    // {
    //     $user = auth()->user();
    //     $user_role = $user->roles->first();
    //     $role = $user_role == 'Admin' ? 1 : 0;
    //     return view('content.reports.proyeccion', compact('role'));
    // }

    public function getSales($id)
    {
        try {
            // Validación usando Policy
            $this->authorize('viewSales', [User::class, $id]);
        
            $wallet_id = Wallet::where('user_id', $id)->first();
            
            if (!$wallet_id) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No se encontró wallet para este usuario'
                ]);
            }
        
            $last_batch = Option::lastBatch()->value;
        
            $wallet_movements = DB::table('wallet_movements as wallet')
                ->join('users as us', 'wallet.user_purchase_id', '=', 'us.id')
                ->select('us.name', 'us.last_name', 'wallet.amount', 'wallet.reason', 'wallet.created_at', 'wallet.bonus_type_id')
                ->where('wallet.wallet_id', $wallet_id->id)
                ->whereIn('wallet.bonus_type_id', [2, 3])
                ->where('wallet.batch', $last_batch)
                ->get();
        
            return response()->json([
                'success' => true,
                'data' => $wallet_movements,
                'message' => 'Data recuperada con éxito'
            ]);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder a esta información'
            ], 403);
        }
    }
}
