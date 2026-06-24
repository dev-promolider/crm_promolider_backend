<?php

namespace App\Http\Controllers;

use App\Models\PointDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserClassroomPoint;

class ClassroomPointDetailController extends Controller
{

    public function insert(Request $request)
    {
        $classroompointdetail= DB::table('classroom_point_details')->insert([
            'id_user_classroom_points' => $request->id_user_classroom_points,
            'increment_points' => $request->increment_points,
            'description' => $request->description
        ]);

        return[$classroompointdetail,$this->insertTotal($request->id_user_classroom_points,$request->increment_points)];
        //return $classroompointdetail;
        //return $this->insertTotal($request->id_user,$request->increment_points);
    }

    public function insertTotal($id,$increment)
    {
        $myPoints = UserClassroomPoint::where('user_classroom_points.id',$id)->select('user_classroom_points.total_points as total')->get()->first();

        $newtotal=$myPoints->total + $increment;

        $totalPoints= UserClassroomPoint::where('user_classroom_points.id',$id)->update(['total_points' => $newtotal]);
        return $totalPoints;
    }

 
  
}
