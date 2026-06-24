<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    public function __construct(){
        $this->middleware('can:report-startingBonus')->only('startingBonus');
        $this->middleware('can:report-growthBonus')->only('growthBonus');
    }
    public function startingBonus()
    {
        $qualifieds = Classified::where('id_user_sponsor', auth()->user()->id)->get();
        return view('content.reports.starting_bonus',compact('qualifieds'));

    }
    public function growthBonus()
    {
        $qualifieds = Classified::where('id_user_sponsor', auth()->user()->id)->get();
        return view('content.reports.growth_bonus',compact('qualifieds'));

    }
}
