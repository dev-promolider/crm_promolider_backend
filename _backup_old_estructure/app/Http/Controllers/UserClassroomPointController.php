<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\UserClassroomPoint;
use App\Models\User;
use App\Http\Requests\StoreUserClassroomPointRequest;
use App\Http\Requests\UpdateUserClassroomPointRequest;

class UserClassroomPointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserClassroomPointRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserClassroomPointRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserClassroomPoint  $userClassroomPoint
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        #$userClassroomPoint = User::orderBy('user_classroom_points.total_points', 'DESC')->join('user_classroom_points', 'users.id', '=', 'user_classroom_points.id_user')->select('users.id','users.photo','users.name', 'user_classroom_points.total_points as total')->get(10);
        $userClassroomPoint = DB::table('users')->orderBy('user_classroom_points.total_points', 'DESC')->join('user_classroom_points', 'users.id', '=', 'user_classroom_points.id_user')->select('users.id','users.photo','users.name', 'user_classroom_points.total_points as total')->get(10);

        return $userClassroomPoint;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserClassroomPoint  $userClassroomPoint
     * @return \Illuminate\Http\Response
     */
    public function edit(UserClassroomPoint $userClassroomPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserClassroomPointRequest  $request
     * @param  \App\Models\UserClassroomPoint  $userClassroomPoint
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserClassroomPointRequest $request, UserClassroomPoint $userClassroomPoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserClassroomPoint  $userClassroomPoint
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserClassroomPoint $userClassroomPoint)
    {
        //
    }

    public function getPosicionRanking()
    {
        $user_id = auth()->user()->id;
        $ranking =  UserClassroomPoint::orderBy('total_points', 'DESC')->get()->toArray();
        $position = array_search($user_id, array_column($ranking, 'id_user'));
        return response()->json($position+1, 200);
    }
}
