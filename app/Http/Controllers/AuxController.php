<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class AuxController extends Controller
{
    public function allCountries()
    {
        if (Storage::disk('public')->exists('json/countries.json')) {
            return Storage::disk('public')->get('json/countries.json');
        }
        
        throw new FileNotFoundException(sprintf('File not found: %s', 'json/countries.json'), 404);
    }

    public function getStatesByCountry($name)
    {
        if (Storage::disk('public')->exists('json/countries_states.json')) {
            $countries = Storage::disk('public')->get('json/countries_states.json');
            $countries = json_decode($countries, true);

            $states = [];
            foreach ($countries['countries'] as $v) {
                if($v['country'] == $name){
                    foreach ($v['states'] as $s){
                        array_push($states, $s);
                    }
                }
            }

            return $states;
        }
        
        throw new FileNotFoundException(sprintf('File not found: %s', 'json/countries_states.json'), 404);
    }
}
