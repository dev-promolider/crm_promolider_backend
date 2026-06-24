<?php

namespace App\Http\Controllers;

use App\Models\ClassroomPointConfig;
use App\Http\Requests\StoreClassroomPointConfigRequest;
use App\Http\Requests\UpdateClassroomPointConfigRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassroomPointConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('can:classroom-point-config.create')->only('create');
        $this->middleware('can:classroom-point-config.index')->only('index', 'listClassroomPointConfig');
        $this->middleware('can:classroom-point-config.edit')->only('edit');
    }

    public function index()
    {

        //return view('content.config.classroom-point');
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
     * @param  \App\Http\Requests\StoreClassroomPointConfigRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClassroomPointConfigRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClassroomPointConfig  $classroomPointConfig
     * @return \Illuminate\Http\Response
     */
    public function show(ClassroomPointConfig $classroomPointConfig)
    {
        $classroompointconfigs= DB::table('classroom_point_configs')->get();
        return view('content.config.classroom-point-config')->with('classroompointconfigs',$classroompointconfigs);

    }

    public function values()
    {
        $classroompointconfigs= DB::table('classroom_point_configs')->get();
        return $classroompointconfigs;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClassroomPointConfig  $classroomPointConfig
     * @return \Illuminate\Http\Response
     */
    public function edit(ClassroomPointConfig $classroomPointConfig)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClassroomPointRequest  $request
     * @param  \App\Models\ClassroomPoint  $classroomPointConfig
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
      
        $course = ClassroomPointConfig::where('id', $request->id)->get()->first();

        try {
            DB::beginTransaction();
            $course = ClassroomPointConfig::where('id', $id)->get()->first();
            $course->id = $request->id;
            $course->passed_course = $request->passed_course;
            $course->daily_question = $request->daily_question;
            $course->achievement = $request->achievement;

            
            if ($course->update()) {
                $response['status'] = 'ok';
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClassroomPointConfig  $classroomPointConfig
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClassroomPointConfig $classroomPointConfig)
    {
        //
    }
}
