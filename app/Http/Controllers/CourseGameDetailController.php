<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\CourseGameDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseGameDetailController extends Controller
{
    public function storeDetail(Request $request)
    {
        try {
            DB::beginTransaction();
            $detail = CourseGameDetail::firstOrNew(['game_id' =>   $request->game_id]);
            $data = $this->buildArray($request->description,  $request->word)[0];
            $detail->data = $data;
            $detail->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function buildArray($description, $word)
    {
        $json[] = array(
            'description'        => $description,
            'word'     => $word,
        );
        return ($json);
    }

    public function storeOwlQuestion(Request $request){
        if(CourseGameDetail::where('game_id', $request->game_id)->exists()){
            $game_detail = CourseGameDetail::where('game_id', $request->game_id)
                ->first();
            // $array_data = json_decode($request->data, true);
            $array_data = $game_detail->data;
            array_push($array_data, $request->data);
            $game_detail->data = $array_data;
            $game_detail->update();
        }else{
            $array_data = [];
            array_push($array_data, $request->data);
            $game_detail = new CourseGameDetail();
            $game_detail->game_id = $request->game_id;
            $game_detail->data = $array_data;
            $game_detail->save();
        }
    }

    public function updateOwlQuestion(Request $request){
        $game_detail = CourseGameDetail::where('game_id', $request->game_id)->first();
        $array_data = $game_detail->data;
        $array_data[$request->position] = $request->data;
        $game_detail->data = $array_data;
        $game_detail->update();
    }

    public function storeWheelQuestion(Request $request){
        if(CourseGameDetail::where('game_id', $request->game_id)->exists()){
            $game_detail = CourseGameDetail::where('game_id', $request->game_id)
                ->first();
            // $array_data = json_decode($request->data, true);
            $array_data = $game_detail->data;
            array_push($array_data, $request->data);
            $game_detail->data = $array_data;
            $game_detail->update();
        }else{
            $array_data = [];
            array_push($array_data, $request->data);
            $game_detail = new CourseGameDetail();
            $game_detail->game_id = $request->game_id;
            $game_detail->data = $array_data;
            $game_detail->save();
        }
    }

    public function updateWheelQuestion(Request $request){
        $game_detail = CourseGameDetail::where('game_id', $request->game_id)->first();
        $array_data = $game_detail->data;
        $array_data[$request->position] = $request->data;
        $game_detail->data = $array_data;
        $game_detail->update();
    }

    public function storeCompleteText(Request $request){
        $array_data = [
            'text' => $request->text,
            'data' => json_decode($request->data, true)
        ];
        if(CourseGameDetail::where('game_id', $request->game_id)->exists()){
            $game_detail = CourseGameDetail::where('game_id', $request->game_id)
                ->first();
            $game_detail->data = $array_data;
            $game_detail->game_id = $request->game_id;
            $game_detail->update();
        }else{
            $game_detail = new CourseGameDetail();
            $game_detail->game_id = $request->game_id;
            $game_detail->data = $array_data;
            $game_detail->save();
        }
    }

    public function storeOrderWords(Request $request){
        if(CourseGameDetail::where('game_id', $request->game_id)->exists()){
            $game_detail = CourseGameDetail::where('game_id', $request->game_id)
                ->first();
            $array_data = $game_detail->data;
            array_push($array_data, $request->data);
            $game_detail->data = $array_data; //modificar para que reciba todo el array
            $game_detail->game_id = $request->game_id;
            $game_detail->update();
        }else{
            $array_data = [];
            array_push($array_data, $request->data);
            $game_detail = new CourseGameDetail();
            $game_detail->game_id = $request->game_id;
            $game_detail->data = $array_data;
            $game_detail->save();
        }
    }

    public function updateOrderWords(Request $request){
        $game_detail = CourseGameDetail::where('game_id', $request->game_id)
            ->first();
        $array_data = $game_detail->data;
        $array_data[$request->position] = $request->data;
        $game_detail->data = $array_data;
        $game_detail->update();
    }

    public function storeItem(Request $request)
    {
        try {
            DB::beginTransaction();

            $detail = CourseGameDetail::where('game_id', $request->game_id)->get();

            if (count($detail) == 0) {
                $detail = new CourseGameDetail();
                if ($request->hasFile('file')) {
                    $item = $this->buildItem($request->name,  $request->file);
                    $detail->game_id = $request->game_id;
                    $detail->data = $item;
                    $detail->save();
                }
            } else {
                // si hay un detalle agregamos el nuevo item al json
                $detail = CourseGameDetail::where('game_id', $request->game_id)->get()->first();
                $data_array = $detail->data;
                $new_item = $this->buildItem($request->name,  $request->file)[0];
                array_push($data_array, $new_item);
                $detail->data = $data_array;
                $detail->update();
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $detail,
                'message' => 'Registro exitoso'
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error' .$th->getMessage()
            ]);
        }
    }

    public function buildItem($name, $file)
    {
        $user_id = auth()->user()->id;
        $img = Helper::formatFilename($file->getClientOriginalName());
        $path = "games/$user_id/cards/";
        Storage::disk('s3')->put($path . $img, file_get_contents($file), 'public');
        $item[] = array(
            'name' => $name,
            'img' =>  $path . $img
        );
        return $item;
    }

    public function listItem($game_id)
    {
        try {
            $detail = CourseGameDetail::select('data')->where('game_id', $game_id)->first();

            return response()->json([
                'success' => true,
                'data' => $detail,
                'message' => 'Data recuperada con exito'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error' .$th->getMessage()
            ]);
        }
    }

    /**
     * Delete item by json index item
     */
    public function deleteItem($game_id, $item_index)
    {
        try {
            DB::beginTransaction();
            $detail = CourseGameDetail::where('game_id', $game_id)->get()->first();
            $items_array = $detail->data;
            unset($items_array[$item_index]);
            # reindex the array
            $array = array_values($items_array);
            $detail->data = $array;
            $detail->update();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
