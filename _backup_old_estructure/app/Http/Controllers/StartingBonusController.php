<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StartingBonus;
use App\Http\Requests\BonusRequest;
use App\Http\Resources\BonusResource;

class StartingBonusController extends Controller
{
    public function __construct(){
        $this->middleware('can:starting-bonus');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $startingBonuses = StartingBonus::all();
        return BonusResource::collection($startingBonuses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\BonusRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BonusRequest $request)
    {
        $startingBonus = new StartingBonus($request->validated());
        $startingBonus->save();
        return new BonusResource($startingBonus);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StartingBonus  $startingBonus
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $startingBonus = StartingBonus::findOrFail($id);
        $startingBonus = new BonusResource($startingBonus);
        return $startingBonus;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\BonusRequest  $request
     * @param  \App\Models\StartingBonus  $startingBonus
     * @return \Illuminate\Http\Response
     */
    public function update(BonusRequest $request, StartingBonus $startingBonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StartingBonus  $startingBonus
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $startingBonus = StartingBonus::findOrFail($id);
        $startingBonus->delete();
        return new BonusResource($startingBonus);
    }

    public function retornarVista()
    {
        return view('content.config.starting-bonus',['title' => __('locale.starting_bonus')]);
    }
}
