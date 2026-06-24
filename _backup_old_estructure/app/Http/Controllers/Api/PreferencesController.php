<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Preferences;
use App\Models\User;
use App\Traits\ResponseFormat;
use Illuminate\Http\Resources\Json\JsonResource;

class PreferencesController extends Controller
{
    use ResponseFormat;

    /**
     * Save user's preferences
     * @param $request {Request}
     *   ->categorys -> array of categories id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $array_preferences = $request->categorys;
        foreach ($array_preferences as $preferences) {

            $preference_repeated = Preferences::where('categories_id', $preferences)->where('user_id', $user_id)->get();
            if( count($preference_repeated) == 0 ){
                $preference = new Preferences();
                $preference->user_id = auth()->user()->id;
                $preference->categories_id = $preferences;
                $preference->save();
            }
        }
        $user = User::findOrFail(auth()->user()->id);
        $user->status_preference = 1;
        $user->save();

        return $this->responseOk('', "saved preferences");
    }
    public function deleteUserPreferences(Request $request){

        $user_id = auth()->user()->id;
        $array_preferences = $request->categorys;

        foreach ($array_preferences as $preferences) {
            $preference = Preferences::where('categories_id', $preferences)->where('user_id', $user_id)->get();
            if(count($preference)>1){// si existe mas de una
                foreach ($preference as $pref) {
                    $pref->delete();
                }
            }
            else{
                $preference = $preference[0];
                if ($preference) { // si existe la preferencia
                    $preference->delete();
                } else {
                    return $this->responseOk('', "preference does not exist");
                }
            }
        }
        return $this->responseOk('', "preferences deleted");
    }

    public function myPreferences()
    {
        $user_id = auth()->user()->id;
        $myPreferences = Preferences::join('categories', 'categories.id', '=', 'preferences.categories_id')
            ->where('preferences.user_id', $user_id)
            ->select('preferences.id', 'preferences.categories_id', 'categories.name', 'categories.icon')
            ->get();

        return JsonResource::collection($myPreferences);
    }

    public function updatePreference($id)
    {
        $user_id = auth()->user()->id;
        $preference = Preferences::where('categories_id', $id)->where('user_id', $user_id)->get()->first();
        if (!$preference) { // si no existe la preferencia
            $preference = new Preferences();
            $preference->user_id = $user_id;
            $preference->categories_id = $id;
            $preference->save();
        } else {
            return $this->responseOk('', "preference already exists");
        }

        return $this->responseOk('', "preference saved");
    }

    public function deletePreference($id)
    {
        $user_id = auth()->user()->id;
        $preference = Preferences::where('categories_id', $id)->where('user_id', $user_id)->get()->first();
        if ($preference) { // si existe la preferencia
            $preference->delete();
        } else {
            return $this->responseOk('', "preference does not exist");
        }
        return $this->responseOk('', "preference deleted");
    }
}
