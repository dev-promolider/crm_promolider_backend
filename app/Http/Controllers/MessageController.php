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

        // Al abrir la conversación se marcan como leídos los mensajes recibidos.
        $this->markConversationAsRead($conversation, $request->user()->id);

        return response()->json([
            'data' => $messages,
            'unread_count' => 0,
        ]);
    }

    /**
     * Marca como leídos todos los mensajes recibidos en la conversación.
     */
    public function markAsRead(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $userId = $request->user()->id;

        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['message' => 'No tienes acceso a esta conversación.'], 403);
        }

        $marked = $this->markConversationAsRead($conversation, $userId);

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'unread_count'    => 0,
                'marked_messages' => $marked,
            ],
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

        // Notificación de chat (type 99): solo la muestra el Aula Virtual.
        // Se agrupa por remitente: SIEMPRE se reutiliza la última notificación
        // de este remitente para este receptor (leída o no), actualizándola
        // con el mensaje más reciente y marcándola como no leída.
        try {
            $senderName = trim(($message->transmitter->name ?? '') . ' ' . ($message->transmitter->last_name ?? ''));
            // conversation_id va solo en el broadcast: la tabla legada no la tiene.
            $notifPayload = [
                'id_generator' => $userId,
                'id_receiver'  => $receiverId,
                'title'        => 'Nuevo mensaje de ' . $senderName,
                'body'         => \Illuminate\Support\Str::limit($message->message, 120),
                'type'         => \App\Models\Notifications::TYPE_CHAT_MESSAGE,
            ];

            $existingNotif = \App\Models\Notifications::where('type', \App\Models\Notifications::TYPE_CHAT_MESSAGE)
                ->where('id_generator', $userId)
                ->where('id_receiver', $receiverId)
                ->orderByDesc('created_at')
                ->first();

            if ($existingNotif) {
                $existingNotif->update([
                    'title'      => $notifPayload['title'],
                    'body'       => $notifPayload['body'],
                    'seen'       => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                broadcast(new \App\Events\NewNotificationEvent([
                        'id'              => $existingNotif->id,
                        'title'           => $notifPayload['title'],
                        'body'            => $notifPayload['body'],
                        'type'            => $notifPayload['type'],
                        'photo'           => null,
                        'id_generator'    => $userId,
                        'conversation_id' => $conversation->id,
                        'id_receiver'     => $receiverId,
                    ]))->toOthers();
            } else {
                // La creación dispara el broadcast vía NotificationObserver.
                \App\Models\Notifications::create($notifPayload);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear la notificación de chat: ' . $e->getMessage());
        }

        // Preview en vivo para chats NO abiertos: canal del usuario receptor.
        try {
            broadcast(new \App\Events\MessagePreviewEvent($receiverId, [
                'conversation_id' => $conversation->id,
                'transmitter_id'  => $userId,
                'message'         => \Illuminate\Support\Str::limit($message->message, 120),
                'created_at'      => optional($message->created_at)->toISOString() ?? now()->toISOString(),
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo emitir MessagePreviewEvent: ' . $e->getMessage());
        }

        return response()->json([
            'data' => $message,
        ], 201);
    }

    /**
     * Marca como leídos los mensajes recibidos y sincroniza en tiempo real
     * el badge de no leídos del resto de dispositivos.
     */
    private function markConversationAsRead(Conversation $conversation, int $userId): int
    {
        $marked = Message::where('conversation_id', $conversation->id)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Al leer el chat se "leen" también sus notificaciones de chat (type 99):
        // el panel del Aula deja de mostrarlas y el contador baja.
        $generatorIds = [];
        $otherParticipant = $conversation->student_id === $userId
            ? $conversation->teacher_id
            : $conversation->student_id;
        if ($otherParticipant) {
            $generatorIds = [$otherParticipant];
            try {
                \App\Models\Notifications::where('type', \App\Models\Notifications::TYPE_CHAT_MESSAGE)
                    ->where('id_receiver', $userId)
                    ->where('id_generator', $otherParticipant)
                    ->where('seen', 0)
                    ->update(['seen' => 1]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo marcar notificaciones de chat como leídas: ' . $e->getMessage());
            }
        }

        if ($marked > 0) {
            try {
                broadcast(new \App\Events\MessagesReadEvent($conversation->id, $userId, $generatorIds));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo emitir MessagesReadEvent: ' . $e->getMessage());
            }
        } elseif (!empty($generatorIds)) {
            // Aunque no haya mensajes nuevos por marcar, avisar para limpiar notis.
            try {
                broadcast(new \App\Events\MessagesReadEvent($conversation->id, $userId, $generatorIds));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo emitir MessagesReadEvent: ' . $e->getMessage());
            }
        }

        return $marked;
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
