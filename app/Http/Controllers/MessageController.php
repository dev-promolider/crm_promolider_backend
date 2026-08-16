<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        if (!$this->isParticipant($conversation, $request->user()->id)) {
            return response()->json(['message' => 'No tienes acceso a esta conversación.'], 403);
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->with('transmitter:id,name,last_name,photo')
            ->orderBy('created_at')
            ->get();

        $messages->each(function (Message $message) {
            if ($message->transmitter) {
                $message->transmitter->photo = $this->photoUrl($message->transmitter->photo);
            }
        });

        return response()->json([
            'data' => $messages,
        ]);
    }

    public function store(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        $userId = $request->user()->id;

        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['message' => 'No tienes acceso a esta conversación.'], 403);
        }

        $receiverId = $conversation->teacher_id === $userId
            ? $conversation->student_id
            : $conversation->teacher_id;

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'transmitter_id'  => $userId,
            'receiver_id'     => $receiverId,
            'message'         => $request->message,
        ]);

        $message->load('transmitter:id,name,last_name,photo');

        if ($message->transmitter) {
            $message->transmitter->photo = $this->photoUrl($message->transmitter->photo);
        }

        try {
            broadcast(new \App\Events\MessageSentEvent($message))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo emitir MessageSentEvent: ' . $e->getMessage());
        }

        return response()->json([
            'data' => $message,
        ], 201);
    }

    /**
     * Normaliza la foto de un usuario a una URL absoluta (S3).
     */
    private function photoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return str_starts_with($path, 'http')
            ? $path
            : "https://promolider-storage-user.s3.sa-east-1.amazonaws.com/{$path}";
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->student_id === $userId || $conversation->teacher_id === $userId;
    }
}
