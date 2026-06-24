<?php

namespace App\Http\Controllers;

use App\Http\Requests\BadgeRequest;
use App\Models\Badge;
use App\Models\BadgeDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\PseudoTypes\False_;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;
use App\Models\UserCertificate;
use App\Models\Notifications;

class BadgeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $badges = Badge::all();
        return JsonResource::collection($badges);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('content.gamification.badges.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('file')) {

                $badge = new Badge();

                $file = $request->file('file');
                $portada = Helper::formatFilename($file->getClientOriginalName());
                $path = 'images/badges/';
                Storage::disk('s3')->put($path . $portada, file_get_contents($file), 'public');

                $badge->name = $request->name;
                $badge->description = $request->description;
                $badge->level = $request->level;
                $badge->condition = $request->condition;

                // 👈 AGREGAMOS CREDITS
                $badge->credits = $request->credits ?? 0;

                $badge->icon = $portada;

                if ($badge->save()) {
                    $response['status'] = 'ok';
                } else {
                    $response['status']  = 'error';
                }

            } else {
                $response['status']  = 'error_imagen';
            }
            echo json_encode($response);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
    public function update(Request $request)
    {
        $badge = Badge::where('id', $request->id)->first();

        try {
            DB::beginTransaction();

            $badge->name = $request->name;
            $badge->description = $request->description;
            $badge->level = $request->level;
            $badge->condition = $request->condition;

            // 👈 AGREGAMOS credits
            $badge->credits = $request->credits ?? $badge->credits;

            if ($request->hasFile('file')) {

                $file = $request->file('file');
                $portada = Helper::formatFilename($file->getClientOriginalName());
                $path = 'images/badges/';
                Storage::disk('s3')->delete($path . $badge->icon,'public');
                Storage::disk('s3')->put($path . $portada, file_get_contents($file), 'public');
                $badge->icon = $portada;
            }

            if ($badge->save()) {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function myBadges()
    {
        return view('content.gamification.badges.user-view');
    }

    public function getMyBadges()
    {
        $user_id = auth()->user()->id;
        //$this->validateBadgesCertificates($user_id);
        $badges = BadgeDetail::join('badges', 'badges.id', '=', 'badge_detail.badge_id')
            ->where('user_id', $user_id)
            ->select('badges.name', 'badges.description', 'badges.level','badges.icon', 'badges.condition')
            ->get();

        return response()->json($badges, 200);
    }

    public function getBadges()
    {
        $user_id = auth()->user()->id;
        $my_badges  = BadgeDetail::where('user_id', $user_id)
            ->select('badge_id')
            ->get();
        $badges_remaining = Badge::select('id','name','description','icon','level')
        ->whereNotIn('id',$my_badges)->get()->toArray();

        $my_badges_list  = Badge::join('badge_detail', 'badge_detail.badge_id', '=', 'badges.id')
        ->where('badge_detail.user_id', $user_id)
        ->select('badges.id','badges.name', 'badges.description','badges.icon', 'badges.level')
        ->get()->toArray();

        for ($i = 0; $i < sizeof($badges_remaining); $i++) {
            $badges_remaining[$i]["obtained"] = False;
        }

        for ($i = 0; $i < sizeof($my_badges_list); $i++) {
            $my_badges_list[$i]["obtained"] = True;
        }

        $badges = array_merge($my_badges_list,$badges_remaining);

        return response()->json($badges, 200);
    }

    public function validateBadgesCertificates($user_id)
    {        
        $badges_details = BadgeDetail::select('id','user_id','badge_id')
            ->where('user_id', $user_id)
            ->whereBetween('badge_id', [1,3])
            ->get();
    
        if (count($badges_details) == 3) {
            return;
        }        
    
        $certificates = UserCertificate::select('id_user', 'id_course')
            ->where('id_user', $user_id)
            ->get();
    
        if (count($certificates) > 0) {
        
            $badges = Badge::select('id','name','description','level','condition','icon','credits') // 👈 AQUI SE AGREGA CREDITS
                ->take(3)
                ->get();
            
            for ($i = 0; $i < count($badges); $i++) { 
            
                $badge = $badges[$i];
            
                if (count($certificates) >= $badge->condition) {
                
                    $badges_details = BadgeDetail::where([
                        'user_id'=> $user_id,
                        'badge_id'=> $badge->id
                    ])->get();
                    
                    if (count($badges_details) == 0) {
                    
                        $badge_detail = new BadgeDetail();
                        $badge_detail->user_id = $user_id;
                        $badge_detail->badge_id = $badge->id;
                    
                        if ($badge_detail->save()) {
                        
                            // ============================================
                            // 💥 NUEVO: Asignar créditos al usuario
                            // ============================================
                            $user = \App\Models\User::find($user_id);
                        
                            if ($badge->credits > 0) {
                                $user->addCredits(
                                    $badge->credits,
                                    "Créditos por logro desbloqueado",
                                    [
                                        'badge_id' => $badge->id,
                                        'badge_name' => $badge->name
                                    ]
                                );
                            }
                            // ============================================
                        
                            // Notificación existente
                            $notification = new Notifications();
                            $notification->id_generator = 1;
                            $notification->id_receiver = $user_id;
                            $notification->id_badge = $badge->id;
                            $notification->title = "Logro desbloqueado";
                            $notification->body = "Obtuvo el logro de ".$badge->name;
                            $notification->type = 1;
                            $notification->seen = 0;
                            $notification->save();
                        }
                    
                    }
                
                } else {
                    $i = count($badges);
                }
            }
        }
    
        return $badges;
    }

}