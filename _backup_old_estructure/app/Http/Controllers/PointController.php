<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointController extends Controller
{
    public function getPointsForUser(Request $request)
    {
        $user =  User::find( auth()->user()->id ); 
        if($request->side != null){
            $points = $user->points()->where('side',$request->side)->where('status',1)->get();
        }else{
            $points = $user->points()->where('status',1)->get();
        }
        return JsonResource::collection($points);
    }
}
