<?php

namespace Promolider\Infrastructure\Requests\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Promolider\Application\Requests\UseCases\NewUsers\ListNewUserRequestsUseCase;
use Promolider\Application\Requests\UseCases\NewUsers\GetNewUserRequestByIdUseCase;
use Promolider\Application\Requests\UseCases\NewUsers\UpdateNewUserRequestUseCase;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class NewUserRequestController extends Controller
{
    private $listUseCase;
    private $getByIdUseCase;
    private $updateUseCase;

    public function __construct(
        ListNewUserRequestsUseCase $listUseCase,
        GetNewUserRequestByIdUseCase $getByIdUseCase,
        UpdateNewUserRequestUseCase $updateUseCase
    ) {
        $this->listUseCase = $listUseCase;
        $this->getByIdUseCase = $getByIdUseCase;
        $this->updateUseCase = $updateUseCase;
        // The policy should be enforced here or in routes middleware
        // $this->middleware('can:new-users');
    }

    public function index()
    {
        // Assuming user is authenticated and authorized via middleware
        $requests = $this->listUseCase->execute();
        return JsonResource::collection($requests);
    }

    public function getUserById($id)
    {
        $user = $this->getByIdUseCase->execute($id);
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        return response()->json($user);
    }

    public function updateUnverifiedRequest(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|integer',
            'id_referrer_sponsor' => 'required|integer',
        ]);

        try {
            $this->updateUseCase->execute(
                $request->id,
                $request->status,
                $request->id_referrer_sponsor
            );

            return response()->json(['message' => 'Solicitud procesada con éxito'], 200);
        } catch (\Exception $e) {
            Log::error("Error processing NewUserRequest", [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
}
