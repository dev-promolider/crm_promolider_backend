<?php

namespace App\Http\Controllers;

use App\Models\CourseRate;
use App\Http\Requests\StoreCourseRateRequest;
use App\Http\Requests\UpdateCourseRateRequest;
use AWS\CRT\HTTP\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class CourseRateController extends Controller
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
     * @param  \App\Http\Requests\StoreCourseRateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /* $user = Auth::user();

        $userset = CourseRate::where([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
        ])->first();

        if(!$userset){
            try {
                DB::beginTransaction();
                $courseRate = new CourseRate();
                $courseRate->user_id = $user->id;
                $courseRate->course_id = $request->course_id;
                $courseRate->rate = $request->rate;
                $courseRate->commentary = $request->commentary;

                $course = Course::findOrFail($request->course_id);
                $course->ranking_by_user = ($course->ranking_by_user + $request->rate)/2;
    
                if ($courseRate->save() && $course->save()) {
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
        }
        else{
            try {
                DB::beginTransaction();
                

                $course = Course::findOrFail($request->course_id);
                $course->ranking_by_user = (((2 * $course->ranking_by_user) - $userset->rate) + $request->rate)/2;

                $userset->rate = $request->rate;
                $userset->commentary = $request->commentary;
    
                if ($userset->save() && $course->save()) {
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
        } */
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CourseRate  $courseRate
     * @return \Illuminate\Http\Response
     */
    public function show(CourseRate $courseRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CourseRate  $courseRate
     * @return \Illuminate\Http\Response
     */
    public function edit(CourseRate $courseRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCourseRateRequest  $request
     * @param  \App\Models\CourseRate  $courseRate
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCourseRateRequest $request, CourseRate $courseRate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CourseRate  $courseRate
     * @return \Illuminate\Http\Response
     */
    public function destroy(CourseRate $courseRate)
    {
        //
    }
}
