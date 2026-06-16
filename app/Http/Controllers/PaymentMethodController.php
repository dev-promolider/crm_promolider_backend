<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function __construct(){
        $this->middleware('can:payment-method')->only('index');
        $this->middleware('can:action-add-payment-method')->only('Add');
        $this->middleware('can:action-edit-payment-method')->only('Edit');
        $this->middleware('can:action-delete-payment-method')->only('Delete');
        // $this->middleware('can:action-list-payment-method')->only('List');
        $this->middleware('can:action-detail-payment-method')->only('Detail');
    }
    public function index()
    {
        return view('content.config.payment-method');
    }
    //validate name
    public function validateDuplicateName($request){
        $paymentMethod = PaymentMethod::select('id',)->where(['name'=>$request->name])->get();
        if(count($paymentMethod)!=0){
            if($request->id != null){
                return $paymentMethod[0]->id == $request->id ? true: false;//false = ya existe 
            }
            return false;//false = ya existe 
        }
        return true;
    }

    public function Add(Request $request)
    {
        if($this->validateDuplicateName($request)){
            $paymentMethod = new PaymentMethod();
            $paymentMethod->name = $request->name;
            $paymentMethod->status = ($request->status != null)? $request->status: '0';

            if($paymentMethod->save()){
                return response('ok',200);
            }
            return response('error',200);
        }
        return response( 'error_name'  ,200);
    }

    public function Edit(Request $request, $id)
    {
        if($this->validateDuplicateName($request)){
            $paymentMethod = PaymentMethod::findOrFail($id);
            $paymentMethod->name = $request->name;
            $paymentMethod->status = ($request->status != null)? $request->status: '0';

            if($paymentMethod->save()){
                return response('ok',200);
            }
            return response('error',200);
        }
        return response( 'error_name'  ,200);
    }

    public function Delete($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        if($paymentMethod->delete()){
            return response('ok', 200);
        }else{
            return response('error', 200);
        }
        
    }

    public function List(Request $request): AnonymousResourceCollection
    {
        $paymentMethods = PaymentMethod::all();
        return PaymentMethodResource::collection($paymentMethods);
    }

    public function listPaymentMethods(){
        $paymentMethods = PaymentMethod::select('id', 'name')
            ->where('status', 1)
            ->get();
        return response()->json($paymentMethods);
    }

    public function Detail($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $result = new PaymentMethodResource($paymentMethod);

        if ($paymentMethod == null) {
            return ($result)->response()->setStatusCode(404);
        }
        return ($result)->response()->setStatusCode(200);


    }

    // Obtener todos los métodos de pago del usuario
    public function paymentAccounts()
    {
        $paymentMethods = Auth::user()->getAllPaymentMethods();
        
        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    public function getAvailableTypes()
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['id' => 'binance', 'name' => 'Binance'],
                ['id' => 'paypal', 'name' => 'PayPal']
            ]
        ]);
    }
}
