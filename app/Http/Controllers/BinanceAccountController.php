<?php

namespace App\Http\Controllers;

use App\Models\BinanceAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BinanceAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $accounts = Auth::user()->binanceAccounts()->active()->latest()->get();
        
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
            'binance_id' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'network' => 'required|string|in:BSC,ETH,TRX,BTC',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista ya esta cuenta para el usuario
        $exists = Auth::user()->binanceAccounts()
            ->where('binance_id', $request->binance_id)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes registrada esta cuenta de Binance'
            ], 422);
        }

        $account = BinanceAccount::create([
            'user_id' => Auth::id(),
            'email' => $request->email,
            'account_name' => $request->account_name,
            'binance_id' => $request->binance_id,
            'phone' => $request->phone,
            'network' => $request->network,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de Binance agregada exitosamente',
            'data' => $account
        ], 201);
    }

    public function show($id)
    {
        $account = Auth::user()->binanceAccounts()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $account
        ]);
    }

    public function update(Request $request, $id)
    {
        $account = Auth::user()->binanceAccounts()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|required|email|max:255',
            'account_name' => 'sometimes|required|string|regex:/^[a-zA-Z\s]+$/|max:255',
            'binance_id' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'network' => 'sometimes|required|string|in:BSC,ETH,TRX,BTC',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $account->update($request->only([
            'email', 'account_name', 'binance_id', 'phone', 'network'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de Binance actualizada exitosamente',
            'data' => $account
        ]);
    }

    public function destroy($id)
    {
        $account = Auth::user()->binanceAccounts()->findOrFail($id);
        
        // Soft delete - solo marcamos como inactiva
        $account->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de Binance eliminada exitosamente'
        ]);
    }
}
