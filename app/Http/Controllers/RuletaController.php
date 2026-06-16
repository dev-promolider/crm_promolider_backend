<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\RuletaSpinEvent;

class RuletaController extends Controller
{
    public function spin()
    {
        $angle = rand(0, 359);
        broadcast(new RuletaSpinEvent($angle));
        return response()->json(['status' => 'Ruleta girada', 'angle' => $angle]);
    }
}
