<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;

use App\Models\WalletMovements;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ApiWalletMovementsController extends Controller
{

    public function getAllMovementsWallet($user_id)
    {

        $requestedUser = User::where('id', $user_id)->firstOrFail();

        // --- ¡AQUÍ ESTÁ LA LÓGICA DE AUTORIZACIÓN! ---
        // Verifica si el ID del usuario autenticado es el mismo que el ID del usuario solicitado.
        if (Auth::user()->id !== $requestedUser->id) {
            // Si no coinciden, significa que un usuario está intentando ver el perfil de otro.
            // Se deniega el acceso con un error 403 Forbidden (Prohibido).
            abort(403, 'Acción no autorizada.');
        }

        $myWallet = Wallet::where('user_id', $user_id)->first();
        $user = User::find($user_id);
        $myMovements = WalletMovements::where('wallet_id', $myWallet->id)->orWhere('id_receiver', $user->id)->get();
        return JsonResource::collection($myMovements);
    }
}
