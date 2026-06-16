<?php

namespace App\Http\Controllers\MC;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\MasterClassParticipant;
use App\Models\MasterClassVideo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['filter', 'upcoming']);
    }

    public function indicators()
    {
        $userId = auth()->user()->id;
        $masterClassesIds = MasterClassVideo::where('user_id', $userId)->pluck('id');

        $currentMasterClass = MasterClassVideo::where('user_id', $userId)
            ->whereMonth('start_at', now())
            ->count();
        $currentSubscribers = MasterClassParticipant::whereIn('master_class_id', $masterClassesIds)
            ->whereMonth('created_at', now())
            ->count();

        $data = [
            'labels' => ['Master Class', 'Suscriptores'],
            'numbers' => [$currentMasterClass, $currentSubscribers],
        ];
        return response()->json(['list' => $data], 200);
    }

    public function store(Request $request)
    {
        $userId = auth()->user()->id;
        $uuid = Str::uuid();
        // status 0 = draft, 1 = published, 2 = deleted
        try {
            $mcVideo = new MasterClassVideo();
            $mcVideo->course_id = $request->course_id;
            $mcVideo->user_id = $userId;
            $mcVideo->title = $request->title;
            $mcVideo->description = $request->description;

            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $portada = Helper::formatFilename($file->getClientOriginalName());
                $path = "master_class/$userId/$uuid/";
                Storage::disk('s3')->put($path . $portada, file_get_contents($file), 'public');
                $mcVideo->banner = $path . $portada;
                # left save resource name
            }
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $video = Helper::formatFilename($file->getClientOriginalName());
                $path = "master_class/$userId/$uuid/";
                Storage::disk('s3')->put($path . $video, file_get_contents($file), 'public');
                $mcVideo->url_video = $path . $video;
                $mcVideo->duration = $request->duration ?? 0;

                # left save resource name
            }
            $mcVideo->zoom_link = $request->zoom_link ??  '';
            $mcVideo->start_at = $request->start_at;
            $mcVideo->invitation_link = $this->generateLink($userId, $request->title);
            $mcVideo->save();
            return response()->json(['message' => 'Master Class creado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function list()
    {
        $userId = auth()->user()->id;
        $masterClass = MasterClassVideo::select('id', 'course_id', 'title', 'created_at', 'start_at', 'invitation_link')
            ->with(['course:id,title'])
            ->where('user_id', $userId)->get();
        return response()->json(["list" => $masterClass], 200);
    }

    public function upcoming()
    {
        $upcoming = MasterClassVideo::select('id', 'course_id', 'title', 'created_at', 'start_at', 'banner', 'invitation_link')
            ->with(['course:id,title'])
            ->where('start_at', '>', now())
            ->orderBy('start_at', 'asc')
            ->take(4)
            ->get();
        return response()->json(["list" => $upcoming], 200);
    }

    /**
     * Filter master class by month
     */
    public function filter(Request $request)
    {
        $month = $request->month;
        if ($month) {
            $masterclasses = MasterClassVideo::select('id', 'course_id', 'title', 'created_at', 'start_at', 'banner')
                ->with(['course:id,title'])
                ->whereMonth('start_at', $month)
                ->orderBy('start_at', 'desc')
                ->get();
        } else {
            $masterclasses = MasterClassVideo::select('id', 'course_id', 'title', 'created_at', 'start_at', 'banner')
                ->with(['course:id,title'])
                ->whereMonth('start_at', now()->month)
                ->orderBy('start_at', 'desc')
                ->get();
        }
        return response()->json(["list" => $masterclasses], 200);
    }

    public function delete($id)
    {
        $userId = auth()->user()->id;
        $exists = MasterClassVideo::where('user_id', $userId)->where('id', $id)->exists();
        if ($exists) {
            $masterClass = MasterClassVideo::where('user_id', $userId)->where('id', $id)->first();
            Storage::disk('s3')->delete($masterClass->banner);
            Storage::disk('s3')->delete($masterClass->url_video);
            MasterClassVideo::where('user_id', $userId)->where('id', $id)->delete();
            return response()->json(["message" => "Master Class eliminado correctamente"], 200);
        }
        return response()->json(["message" => "Master Class no encontrado"], 404);
    }

    public  function generateLink($userId, $title)
    {
        $mc_url = env('MC_APP_URL') . 'invitation';
        $uuid = $this->generateUUID(6);
        $user = User::find($userId);
        $pretty_name =  Str::slug($user->username, '-');
        $pretty_title = Str::slug($title, '-');
        return strtolower("$mc_url/$pretty_name/$pretty_title-$uuid");
    }

    public function generateUUID($length)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }
        return $random;
    }

    public function show(Request $request)
    {
        $mc = MasterClassVideo::where('invitation_link', $request->url)->first();
        $productor = User::find($mc->user_id);

        return response()->json([
            'productor' => $productor,
            'mc' => $mc,
        ], 200);
    }
}
