<?php

namespace App\Http\Controllers;

use App\Models\UserDailyQuizz;
use App\Http\Requests\StoreUserDailyQuizzRequest;
use App\Http\Requests\UpdateUserDailyQuizzRequest;

class UserDailyQuizzController extends Controller
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
     * @param  \App\Http\Requests\StoreUserDailyQuizzRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserDailyQuizzRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserDailyQuizz  $userDailyQuizz
     * @return \Illuminate\Http\Response
     */
    public function show(UserDailyQuizz $userDailyQuizz)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserDailyQuizz  $userDailyQuizz
     * @return \Illuminate\Http\Response
     */
    public function edit(UserDailyQuizz $userDailyQuizz)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserDailyQuizzRequest  $request
     * @param  \App\Models\UserDailyQuizz  $userDailyQuizz
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserDailyQuizzRequest $request, UserDailyQuizz $userDailyQuizz)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserDailyQuizz  $userDailyQuizz
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserDailyQuizz $userDailyQuizz)
    {
        //
    }
}
