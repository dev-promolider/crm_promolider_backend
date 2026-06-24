<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Http\Requests\AccountTypeRequest;
use App\Http\Resources\AccountTypeResource;

class AccountTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accountTypes  = AccountType::all();
        return AccountTypeResource::collection($accountTypes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * not used
     */
    public function store(AccountTypeRequest $request)
    {
        $accountType = new AccountType($request->validated());
        $accountType->save();
        return response()->json(['data' => $accountType]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show(AccountType $accountType)
    {
        $accountType = new AccountTypeResource($accountType);
        return $accountType;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function update(AccountTypeRequest $request, AccountType $accountType)
    {
        $accountType->fill($request->validated());
        $accountType->update();
        $accountType = new AccountTypeResource($accountType);
        return $accountType;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountType  $accountType
     * @param $request {Request}
     *   ->$status ->account type status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, AccountType $accountType)
    {
        $result = new AccountTypeResource($accountType);
        $accountType->status = $request->status ?? $accountType->status;
        $accountType->save();
        if ($accountType->save()) {
            return ($result)->response()->setStatusCode(200);
        }
        return $result->response()->setStatusCode(400);
    }

    /**
     * Account type's information
     * @param $id -> Account type's id
     * @return \Illuminate\Http\Response
     */
    public function getDataBytId($id)
    {
        $data = AccountType::find($id);
        return response()->json($data);
    }

    /**
     * Account type view
     */
    public function retornarVista()
    {
        return view('content.config.account-type');
    }

    public function certificateDiscount()
    {
        $user = auth()->user();
        $discount = AccountType::where('id', $user->id_account_type)->get()->first()->disc_purchases_certificates;
        return $discount;
    }

    public function courseDiscount()
    {
        $user = auth()->user();
        $discount = AccountType::where('id', $user->id_account_type)->get()->first()->disc_purchases_course;
        return $discount;
    }
}
