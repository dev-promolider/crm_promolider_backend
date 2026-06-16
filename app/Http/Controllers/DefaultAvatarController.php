<?php

namespace App\Http\Controllers;

use App\Models\DefaultAvatar;
use App\Http\Requests\StoreDefaultAvatarRequest;
use App\Http\Requests\UpdateDefaultAvatarRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class DefaultAvatarController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $avatar = new DefaultAvatar();
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $portada = Helper::formatFilename($file->getClientOriginalName());
                $path = 'images/';
                Storage::disk('s3')->put($path . $portada, file_get_contents($file), 'public');
                $avatar->link = $portada;
            }

            if ($avatar->save()) {
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

    public function delete($id)
    {

        $avatar = DefaultAvatar::where('id', $id)->first();
        $portada = $avatar->link;
        $path = 'images/';
        try {
            DB::beginTransaction();
            if ($avatar->delete()) {
                Storage::disk('s3')->delete($path . $portada, 'public');
                $response['status'] = 'ok';
            } else {
                $response['status'] = 'error';
            }
            echo json_encode($response);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show()
    {
        $avatars = DefaultAvatar::get();
        return JsonResource::collection($avatars);
    }
}
