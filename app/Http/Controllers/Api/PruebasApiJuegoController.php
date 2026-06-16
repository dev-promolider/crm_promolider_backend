<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\PruebasApiJuegos;
use Illuminate\Http\Request;

class PruebasApiJuegoController extends Controller
{
    public function list()
    {
        $obj  = PruebasApiJuegos::get();
        $obj->toJson();
        //return $obj->toJson();
        $output= ['personas' => json_decode($obj)];
        return $output;
    }
}
