<?php

namespace App\Http\Controllers;

use App\Http\Resources\BankResource;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BankController extends Controller
{
    public function __construct(){
        $this->middleware('can:bank')->only('index');
        $this->middleware('can:action-add-bank')->only('Add');
        $this->middleware('can:action-edit-bank')->only('Edit');
        $this->middleware('can:action-delete-bank')->only('Delete');
        $this->middleware('can:action-list-bank')->only('List');
        $this->middleware('can:action-detail-bank')->only('Detail');
    }
    public function index()
    {
        return view('content.config.bank');
    }
    public function validateDuplicateName($request){
        $bankDuplicateName = Bank::select('id',)->where(['name'=>$request->name])->get();
        if(count($bankDuplicateName)!=0){
            if($request->id != null){
                return $bankDuplicateName[0]->id == $request->id ? true: false;//false = ya existe 
            }
            return false;//false = ya existe 
        }
        return true;
    }
    public function Add(Request $request)
    {
        $bank = new Bank();
        if($this->validateDuplicateName($request)){
            $bank->name = $request->name;
            if($bank->save()){
                return response('ok',200);
            }
            return response('error',200);
        }
        return response('error_name',200);
    }

    public function Edit(Request $request)
    {
        $bank = Bank::findOrFail($request->id);
        
        if($this->validateDuplicateName($request)){
            $bank->name = $request->name;
            if($bank->save()){
                return response('ok',200);
            }
            return response('error',200);
        }
        return response('error_name',200);
    }

    public function Delete($id)
    {
        $bank = Bank::findOrFail($id);
        $result = new BankResource($bank);

        if ($bank->delete()) {
            return ($result)->response()->setStatusCode(200);
        }

        return $result->response()->setStatusCode(400);
    }

    public function List(Request $request): AnonymousResourceCollection
    {
        $banks = Bank::all();
        return BankResource::collection($banks);
    }

    public function Detail($id)
    {
        $bank = Bank::findOrFail($id);
        $result = new BankResource($bank);

        if ($bank == null) {
            return ($result)->response()->setStatusCode(404);
        }

        return ($result)->response()->setStatusCode(200);

    }

}
