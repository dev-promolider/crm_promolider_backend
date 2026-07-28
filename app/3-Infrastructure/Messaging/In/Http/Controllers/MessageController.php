<?php

namespace Promolider\Infrastructure\Messaging\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Messaging\UseCases\ListMessagesUseCase;
use Promolider\Application\Messaging\UseCases\SendMessageUseCase;

class MessageController extends Controller
{
    public function __construct(
        private ListMessagesUseCase $listMessagesUseCase,
        private SendMessageUseCase $sendMessageUseCase,
    ) {}

    /**
     * GET /messages/list
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->listMessagesUseCase->execute($userId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error listing messages: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar mensajes'], 500);
        }
    }

    /**
     * POST /messages/add
     */
    public function send(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|integer|min:1',
                'message' => 'required|string|max:1000',
            ]);

            $transmitterId = $request->user()->id;
            $result = $this->sendMessageUseCase->execute(
                $transmitterId,
                (int) $validated['receiver_id'],
                $validated['message']
            );

            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al enviar mensaje'], 500);
        }
    }

    /**
     * Alias legacy: POST /messages/sendNewMessage
     */
    public function sendNewMessage(Request $request): JsonResponse
    {
        return $this->send($request);
    }
}
