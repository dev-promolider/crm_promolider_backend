<?php namespace App\Services;

use App\Helpers\ParseUrl;
use App\Models\Notifications;

class NotificationService 
{
    protected $cachedMyNotification = null;
    protected $cachedCountNotification = null;
    protected $cachedNotificationSeen = null;

    public function myNotification()
    {
        if ($this->cachedMyNotification !== null) return $this->cachedMyNotification;

        $user_id = auth()->user()->id;
        $notifications = Notifications::join('users','users.id','=','id_generator')->where('id_receiver',$user_id)
        ->where('seen',0)->select('users.photo','title','body')->limit(3)->get();
        
        for($i = 0; $i < sizeof($notifications); $i++){
            $notifications[$i]->photo = ParseUrl::contacAtrrS3($notifications[$i]->photo);
        }

        $this->cachedMyNotification = $notifications;
        return $this->cachedMyNotification;
    }

    public function countNotification(){
        if ($this->cachedCountNotification !== null) return $this->cachedCountNotification;

        $user_id = auth()->user()->id;
        $number = Notifications::where('id_receiver',$user_id)
        ->where('seen',0)->count();

        $this->cachedCountNotification = $number;
        return $this->cachedCountNotification;
    }

    public function notificationSeen(){
        if ($this->cachedNotificationSeen !== null) return $this->cachedNotificationSeen;

        $user_id = auth()->user()->id;
        $notifications = Notifications::join('users','users.id','=','id_generator')->where('id_receiver',$user_id)
        ->where('seen',1)->select('users.photo','title','body')->limit(3)->get();
        for($i = 0; $i < sizeof($notifications); $i++){
            $notifications[$i]->photo = ParseUrl::contacAtrrS3($notifications[$i]->photo);
        }

        $this->cachedNotificationSeen = $notifications;
        return $this->cachedNotificationSeen;
    }
}
