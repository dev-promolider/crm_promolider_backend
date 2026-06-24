<?php

namespace App\Http\Controllers;

use App\Http\Requests\BonusRequest;
use App\Http\Resources\BonusResource;
use App\Models\GrowthBonus;

class GrowthBonusController extends Controller
{
    public function __construct(){
        $this->middleware('can:growth-bonus');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $growthBonuses = GrowthBonus::all();
        return BonusResource::collection($growthBonuses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\BonusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BonusRequest $request)
    {
        $growthBonus = new GrowthBonus( $request->validated() );
        $growthBonus->save();
        return new BonusResource($growthBonus);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GrowthBonus  $growthBonus
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $growthBonus = GrowthBonus::findOrFail($id);
        $growthBonus = new BonusResource($growthBonus);
        return $growthBonus;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\BonusRequest  $request
     * @param  \App\Models\GrowthBonus  $growthBonus
     * @return \Illuminate\Http\Response
     */
    public function update(BonusRequest $request, GrowthBonus $growthBonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GrowthBonus  $growthBonus
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $growthBonus = GrowthBonus::findOrFail($id);
        $growthBonus->delete();
        return new BonusResource($growthBonus);
    }

    public function retornarVista()
    {
        return view('content.config.growth-bonus',['title' => 'Bono de crecimiento']);
    }
}
