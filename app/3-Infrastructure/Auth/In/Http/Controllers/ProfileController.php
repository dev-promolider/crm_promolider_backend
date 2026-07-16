<?php

namespace Promolider\Infrastructure\Auth\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'biography' => 'nullable|string',
            'date_birth' => 'nullable|date',
            'id_country' => 'nullable|integer',
            'city' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'] ?? $user->last_name,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'biography' => $validated['biography'] ?? $user->biography,
            'date_birth' => $validated['date_birth'] ?? $user->date_birth,
            'id_country' => $validated['id_country'] ?? $user->id_country,
            'city' => $validated['city'] ?? $user->city,
        ]);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }

    public function getMembershipHistory(Request $request)
    {
        $user = $request->user();

        $history = \Illuminate\Support\Facades\DB::table('account_type_details')
            ->where('user_id', $user->id)
            ->join('account_type_detail_histories', 'account_type_details.id', '=', 'account_type_detail_histories.account_type_detail_id')
            ->join('account_type', 'account_type_detail_histories.account_type_id', '=', 'account_type.id')
            ->select('account_type_detail_histories.*', 'account_type.account as account_type_name')
            ->orderBy('account_type_detail_histories.created_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
