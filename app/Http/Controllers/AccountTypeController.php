<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\AccountTypePointsMoney;
use Illuminate\Http\Request;
use App\Http\Requests\AccountTypeRequest;
use App\Http\Resources\AccountTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountTypeController extends Controller
{
    public function __construct(){
        $this->middleware('can:account-type');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accountTypes = AccountType::join('account_type_points_money', 'account_type_points_money.account_type_id', '=', 'account_type.id')
            ->leftJoin('product', 'product.account_type_id', '=', 'account_type.id')
            ->select(
                'account_type.*',
                'account_type_points_money.points',
                'product.id as product_id',
                'product.name as product_name',
                'product.price as product_price'
            )
            ->get();

        return JsonResource::collection($accountTypes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AccountTypeRequest $request)
    {
        $accountType = new AccountType( $request->validated() );
        $accountType->enrollment_duration = $request->enrollment_duration;
        $accountType->save();
        $accountTypePoints = new AccountTypePointsMoney();
        $accountTypePoints->account_type_id = $accountType->id;
        $accountTypePoints->points = $request->points;
        $accountTypePoints->money = 0;
        $accountTypePoints->save();
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
        $points = $request->points;
        $productPrice = $request->product_price; // nuevo campo que debe venir en la request
    
        // Guardamos el precio anterior
        $oldProduct = \App\Models\Product::where('account_type_id', $accountType->id)->first();
        $oldPrice = $oldProduct ? $oldProduct->price : null;
    
        // Actualizar accountType
        $accountType->fill($request->validated());
        $accountType->enrollment_duration = $request->enrollment_duration;
        $accountType->save();
    
        // Actualizar points
        $accountTypePoints = AccountTypePointsMoney::where('account_type_id', $accountType->id)->first();
        if ($accountTypePoints) {
            $accountTypePoints->points = $points;
            $accountTypePoints->save();
        }
    
        // Actualizar product.price
        $product = $oldProduct;
        if ($product && $productPrice !== null) {
            $product->price = $productPrice;
            $product->save();
        
            // Reglas de actualización de expiration_date
            if ($product->price == 0) {
                // Caso 1: pasa a ser gratis -> nunca expira
                \App\Models\User::where('id_account_type', $accountType->id)
                    ->update(['expiration_date' => '9999-12-31 23:59:59']);
            } elseif ($oldPrice == 0 && $product->price > 0) {
                // Caso 2: antes era gratis y ahora tiene precio -> expira hoy
                \App\Models\User::where('id_account_type', $accountType->id)
                    ->update(['expiration_date' => now()]);
            }
        }
    
        return new AccountTypeResource($accountType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,AccountType $accountType)
    {
        $result = new AccountTypeResource($accountType);
        $accountType->status = $request->status ?? $accountType->status;
        $accountType->save();
        if ($accountType->save()) {
            return ($result)->response()->setStatusCode(200);
        }
        return $result->response()->setStatusCode(400);
    }

    //Api Resource
    public function getDataBytId($id)
    {
        //
        $data = AccountType::find($id);
        return response()->json($data);
    }

    public function retornarVista()
    {
        return view('content.config.account-type');
    }
}
