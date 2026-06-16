<?php

namespace App\Traits;

use App\Models\AccountTypePointsMoney;
use App\Models\Classified;
use App\Models\Point;
use App\Models\User;
use Illuminate\Http\Request;

trait PointsDelivery{
    
    public function deliverPoints($atm, $classified_user, $fullName, $user, $id){
        $save_position_branch = $classified_user->position; //almacena la posicion del nodo anterior con respecto al actual

        $aux = false;
        $tmp_id = $classified_user->user_id;
        while ($aux == false) {
            $user_data = Classified::where('user_id', $tmp_id)->first();
            $aux = $user_data->user_above == null ? true : false;
            $user_status = User::find($tmp_id);
            if ($user_status->active && $user_status->membershipActive && $user_status->qualified) {
                Point::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $user_data->user_id,
                    'points' => $atm->points,
                    'side' => $save_position_branch,
                    'reason' => "Binary Team Points, ${fullName} Affiliation"
                ]);
            } elseif ($classified_user->id_user_sponsor == $user_data->user_id) {
                Point::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $classified_user->id_user_sponsor,
                    'points' => $atm->points,
                    'side' => $save_position_branch,
                    'reason' => "Binary Team Points, ${fullName} Affiliation"
                ]);
            }
            $save_position_branch = $user_data->position;
            $tmp_id = $user_data->user_above;
        }
    }
}