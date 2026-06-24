<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\CreateNotification;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ParseUrl;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('content.notification.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_generator = $request->id_generator;
        $id_receiver = $request->id_receiver;
        $id_badge = $request->id_badge;
        $title = $request->title;
        $body = $request->body;
        $type = $request->type;

        $notification = new Notifications();
        $notification->id_generator = $id_generator;
        $notification->id_receiver = $id_receiver;
        $notification->id_badge = $id_badge;
        $notification->title = $title;
        $notification->body = $body;
        $notification->type = $type;

        if($notification->save()){
            $response['status'] = true;
        }else{
            $response['status'] = false;
        }   

        echo json_encode($response);
  
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

    public function myNotifications()
    {
        $user_id = auth()->user()->id;
        $notifications = Notifications::join('users','users.id','=','id_generator')->where('id_receiver',$user_id)
        ->select('notifications.id','users.photo','title','body','type','notifications.created_at')->orderBy('notifications.created_at', 'desc')->get();
        
        for($i = 0; $i < sizeof($notifications); $i++){
            $notifications[$i]->photo = ParseUrl::contacAtrrS3($notifications[$i]->photo);
        }
        return $notifications;

    }

    public function notifyDistributors(Request $request)
    {
        $userId = auth()->id();

        $notifications = MasterClassNotification::whereNotNull('receiver_all') // 🔥 solo masivas
            ->whereRaw("FIND_IN_SET(?, receiver_all)", [$userId]) // el user está en la lista
            ->latest()
            ->get();

        return response()->json($notifications);
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

    public function update()
    {
        $user_id = auth()->user()->id;
        $notification = Notifications::where('id_receiver', $user_id)->where('seen', 0)
        ->update(['seen' => 1]);

        echo json_encode($response['status'] = true); 
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

    
}
