<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserConfiguration;

class SettingsController extends Controller
{
    public function index()
    {
        return view('content.config.settings');
    }
    public function getPoints(){
        $user = auth()->user();

        $userConfiguration = UserConfiguration::select('id','value','configuration_id')
        ->where('user_id','=',$user->id)
        ->where('configuration_id','>=',3)
        ->where('configuration_id','<=',6)
        ->get();

        return response($userConfiguration , 200);
    }
    public function savePoints(Request $request){
        $user = auth()->user();
        UserConfiguration::updateOrCreate(
            ['user_id' => $user->id, 'configuration_id' => 3],
            [
                'value' => $request->passed_exam,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['user_id' => $user->id, 'configuration_id' => 4],
            [
                'value' => $request->exam_timer,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['user_id' => $user->id, 'configuration_id' => 5],
            [
                'value' => $request->dynamics_1,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['user_id' => $user->id, 'configuration_id' => 6],
            [
                'value' => $request->dynamics_2,
            ]
        );
        
        return response('ok' , 200);
    }
    
}
