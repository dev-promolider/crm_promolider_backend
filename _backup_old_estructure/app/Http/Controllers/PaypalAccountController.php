<?php

namespace App\Http\Controllers;

use App\Models\PaypalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaypalAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $accounts = Auth::user()->paypalAccounts()->active()->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'account_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:255',
            'country_code' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'account_type' => 'required|string|in:personal,business',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista ya esta cuenta para el usuario
        $exists = Auth::user()->paypalAccounts()
            ->where('email', $request->email)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes registrada esta cuenta de PayPal'
            ], 422);
        }

        $account = PaypalAccount::create([
            'user_id' => Auth::id(),
            'email' => $request->email,
            'account_name' => $request->account_name,
            'country_code' => $request->country_code,
            'currency' => $request->currency,
            'account_type' => $request->account_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de PayPal agregada exitosamente',
            'data' => $account
        ], 201);
    }

    public function show($id)
    {
        $account = Auth::user()->paypalAccounts()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $account
        ]);
    }

    public function update(Request $request, $id)
    {
        $account = Auth::user()->paypalAccounts()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|required|email|max:255',
            'account_name' => 'sometimes|required|string|regex:/^[a-zA-Z\s]+$/|max:255',
            'country_code' => 'sometimes|required|string|size:2',
            'currency' => 'sometimes|required|string|size:3',
            'account_type' => 'sometimes|required|string|in:personal,business',
            'is_verified' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $account->update($request->only([
            'email', 'account_name', 'country_code', 'currency', 'account_type', 'is_verified'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de PayPal actualizada exitosamente',
            'data' => $account
        ]);
    }

    public function destroy($id)
    {
        $account = Auth::user()->paypalAccounts()->findOrFail($id);
        
        // Soft delete - solo marcamos como inactiva
        $account->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de PayPal eliminada exitosamente'
        ]);
    }
}
