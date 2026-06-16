<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLevelRequest;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\UserLevelService;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;
use App\Traits\ResponseFormat;

class UserLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('content.gamification.levels.index');
    }

    public function validateRepeatedLevel($request){

        $userLevelCoincidence = UserLevel::select('id')
            ->where( 'description','=',$request->description )
            ->get();

        if(count( $userLevelCoincidence ) != 0){
            if( $userLevelCoincidence[0]->id != $request->id ){
                return 'El Nombre del nivel ya ha sido registrada';
            }
        }
        $userLevelCoincidence = UserLevel::select('id')
            ->where( 'experience_required','=',$request->experience_required )
            ->get();

        if(count( $userLevelCoincidence ) != 0){
            if( $userLevelCoincidence[0]->id != $request->id ){
                return 'La Experiencia Requerida ya ha sido registrada';
            }
        }
        return 'ok';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createUpdate(Request $request)
    {
        
        $id = $request->id;
        if (is_null($id)) {
            if($request->file('file')){

                $validateRepeatedLevel = $this->validateRepeatedLevel($request);
                if( $validateRepeatedLevel == 'ok'){

                    $file = $request->file('file');
                    $portada = Helper::formatFilename($file->getClientOriginalName());

                    $userLevelCoincidence = UserLevel::select('id')
                        ->where( 'url_icon','=',$portada )
                        ->get();

                    if(count( $userLevelCoincidence ) == 0){

                        $level = new UserLevel();
                        $level->description = $request->description;
                        $level->experience_required = $request->experience_required;

                        $path = 'images/levels/';
                        Storage::disk('s3')->put($path . $portada, file_get_contents($file), 'public');

                        $level->url_icon = $portada;

                        if ($level->save()) {
                            return response('ok', 200);
                        } else {
                            return response('error', 200);
                        }

                    }
                    else{
                        return response('El nombre de la imagen seleccionada ya ha sido registrada', 200);
                    }
                }
                else{
                    return response( $validateRepeatedLevel , 200);
                }
                
            }
            else{
                return response('error_imagen' , 200);
            }
        }else{

            $validateRepeatedLevel = $this->validateRepeatedLevel($request);
            if($validateRepeatedLevel == 'ok'){
                
                $level = UserLevel::find($request->id);
                $level->description = $request->description;
                $level->experience_required = $request->experience_required;

                if($request->file('file')){

                    $file = $request->file('file');
                    $portada = Helper::formatFilename($file->getClientOriginalName());

                    $userLevelCoincidence = UserLevel::select('id')
                        ->where( 'url_icon','=',$portada )
                        ->get();

                    if(count( $userLevelCoincidence ) == 0){
                        
                        $path = 'images/levels/';
                        Storage::disk('s3')->delete($path . $level->url_icon,'public');
                        Storage::disk('s3')->put($path . $portada, file_get_contents($file), 'public');

                        $level->url_icon = $portada;
                    }
                    else{
                        return response('El nombre de la imagen seleccionada ya ha sido registrada', 200);
                    }
                }

                if ($level->save()) {
                    return response('ok' , 200);
                    
                } else {
                    return response('error' , 200);
                }

            }
            else{
                return response( $validateRepeatedLevel , 200);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showAll()
    {
        $userLevel = UserLevel::get();
        return JsonResource::collection($userLevel);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getInfoUser()
    {
        $user = auth()->user();
       $service = new UserLevelService();
       $myPoints = $service->myPoints();
       $myLevel = $service->getLevel(); 
       $myPorcentaje = $service->porcentaje();
       $nextLevel = $service->nextLevel();
       
        $data = array("user"=>$user,"points" => $myPoints, "level" => $myLevel, "porcentaje" => $myPorcentaje, "nextLevel" => $nextLevel);
       return response()->json($data, 200);

    }
}
