<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FrequentQuestion;

class FrequentQuestionsController extends Controller
{
    public function index()
    {
        return view('content.config.frequent-questions');
    }

    public function showAll()
    {
        $frequentQuestion = FrequentQuestion::get();
        return JsonResource::collection($frequentQuestion);
    }
    public function store(Request $request)
    {
        $frequentQuestion = new FrequentQuestion;
        $frequentQuestion->question = $request->question;
        $frequentQuestion->answer = $request->answer;

        if ($frequentQuestion->save()) {
            return response('ok', 200);
        } else {
            return response('error', 200);
        }
    }

    public function update(Request $request)
    {

        $frequentQuestion = FrequentQuestion::find($request->id);
        $frequentQuestion->question = $request->question;
        $frequentQuestion->answer = $request->answer;
        if ($frequentQuestion->save()) {
            return response('ok', 200);
        } else {
            return response('error', 200);
        }
    }

    public function changeStatus(Request $request)
    {
        $frequentQuestion = FrequentQuestion::find($request->id);
        $frequentQuestion->status = !$request->status;
        if ($frequentQuestion->save()) {
            return response('ok', 200);
        } else {
            return response('error', 200);
        }
    }

    public function destroy($id)
    {
        $frequentQuestion = FrequentQuestion::find($id);
        if ($frequentQuestion->delete()) {
            return response('ok', 200);
        } else {
            return response('error', 200);
        }
    }
    public function getFrequentQuestion(){
        $plantillaProductor = FrequentQuestion::select('id','question','answer',)
                        ->where( [ 'status' => 1 ] )
                        ->get();
        return response($plantillaProductor , 200);
    }
}
