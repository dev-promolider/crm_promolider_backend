<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Traits\ResponseFormat;

class ConfigurationController extends Controller
{
    use ResponseFormat;

    public static function getOptionId($text)
    {
        $id = Configuration::where('option', $text)->get()->first()->id;
        return $id;
    }
}
