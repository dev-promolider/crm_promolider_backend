<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\WebSocketTestEvent;

class WebSocketTestController extends Controller
{
    public function trigger()
    {
        broadcast(new WebSocketTestEvent());
        return response()->json(['status' => 'Event broadcasted']);
    }
}
