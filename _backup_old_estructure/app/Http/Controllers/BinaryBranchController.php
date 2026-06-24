<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class BinaryBranchController extends Controller
{
    public function __construct(){
        $this->middleware('can:binary-branch');
    }
    public function binary_branch()
    {
        return view('content.binary-branch.index');
    }

    // Metodo para listar los usuarios
    public function getListUsersMembreship(): JsonResponse
    {
        $list_user_membreship = User::with(['accountType', 'documentType'])
            ->join('classified', 'users.id', '=', 'classified.user_id')
            ->where('id_referrer_sponsor', '=', auth()->user()->id)
            ->orderBy('users.created_at', 'asc')
            ->get();

        return response()->json($list_user_membreship);
    }

    public function getMyDirects(){
        $lits_directs = User::where('id_referrer_sponsor', auth()->user()->id)
            ->get();

        return response()->json($lits_directs);
    }
}