<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Course;
use App\Models\Message;
use App\Models\PurchasedCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class VcrMessageController extends Controller
{
    /**
     * GET /api/v1/messages/listContacts
     */
    public function listContacts()
    {
        $currentUserId = auth()->user()->id;

        $transmitterIds = Message::where('transmitter_id', $currentUserId)
            ->distinct()
            ->pluck('receiver_id')
            ->toArray();

        $receiverIds = Message::where('receiver_id', $currentUserId)
            ->distinct()
            ->pluck('transmitter_id')
            ->toArray();

        $contactIds = array_unique(array_merge($transmitterIds, $receiverIds));

        if (empty($contactIds)) {
            return response()->json(['data' => [], 'total' => 0]);
        }

        $contacts = [];

        foreach ($contactIds as $contactId) {
            $contact = User::select('id', 'name', 'last_name', 'photo', 'email')
                ->where('id', $contactId)
                ->first();

            if (!$contact) {
                continue;
            }

            $lastMessage = Message::where(function ($query) use ($currentUserId, $contactId) {
                $query->where([
                    ['transmitter_id', $currentUserId],
                    ['receiver_id', $contactId],
                ])->orWhere([
                    ['transmitter_id', $contactId],
                    ['receiver_id', $currentUserId],
                ]);
            })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastMessage) {
                $contacts[] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'lastname' => $contact->last_name,
                    'photo' => ParseUrl::contacAtrrS3($contact->photo),
                    'email' => $contact->email,
                    'last_message' => $lastMessage->message,
                    'last_message_time' => $lastMessage->created_at,
                    'is_sender' => $lastMessage->transmitter_id === $currentUserId,
                ];
            }
        }

        usort($contacts, function ($a, $b) {
            return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
        });

        return response()->json([
            'data' => $contacts,
            'total' => count($contacts),
        ]);
    }

    /**
     * POST /api/v1/messages/content
     */
    public function getContent(Request $request)
    {
        $currentUserId = auth()->id();

        // CRM-09: Asegurar que el usuario autenticado solo vea sus propios mensajes
        if ($request->transmitter_id != $currentUserId && $request->receiver_id != $currentUserId) {
            return response()->json(['error' => 'No tienes permiso para ver estos mensajes'], 403);
        }

        $messages = Message::where(function ($query) use ($request) {
            $query->where('transmitter_id', $request->transmitter_id)
                ->where('receiver_id', $request->receiver_id);
        })
            ->orWhere(function ($query) use ($request) {
                $query->where('transmitter_id', $request->receiver_id)
                    ->where('receiver_id', $request->transmitter_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'data' => $messages,
        ]);
    }

    /**
     * POST /api/v1/messages/add
     */
    public function addMessage(Request $request)
    {
        $msj = new Message();
        $msj->transmitter_id = auth()->user()->id;
        $msj->receiver_id = $request->receiver_id;
        $msj->message = $request->message;
        $msj->save();

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => 'message success',
        ]);
    }

    /**
     * GET /api/v1/messages/with/{email}
     */
    public function show($email)
    {
        $idUser = auth()->user()->id;
        $user2 = User::where('email', $email)->first();

        if ($user2) {
            $idUser2 = $user2->id;
            $data = Message::select('users.name', 'messages.message', 'messages.created_at')
                ->join('users', 'users.id', '=', 'messages.transmitter_id')
                ->where([
                    ['messages.transmitter_id', '=', $idUser],
                    ['messages.receiver_id', '=', $idUser2],
                ])
                ->orWhere([
                    ['messages.transmitter_id', '=', $idUser2],
                    ['messages.receiver_id', '=', $idUser],
                ])
                ->orderBy('messages.created_at', 'ASC')
                ->get();

            return response()->json([
                'status' => 'ok',
                'message' => '',
                'data' => $data,
            ]);
        }

        return response()->json(['error' => 'No conversations'], 404);
    }

    /**
     * GET /api/v1/messages/list
     */
    public function list()
    {
        $currentUserId = auth()->user()->id;

        $messages = Message::join('users', 'users.id', '=', 'messages.transmitter_id')
            ->where('messages.receiver_id', $currentUserId)
            ->select('messages.*', 'users.name as fullname', 'users.photo')
            ->orderBy('messages.created_at', 'DESC')
            ->take(5)
            ->get();

        foreach ($messages as $msg) {
            $msg->photo = ParseUrl::contacAtrrS3($msg->photo);
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $messages,
        ]);
    }

    /**
     * GET /api/v1/messages/listAll
     */
    public function listAll()
    {
        $currentUserId = auth()->user()->id;

        $messages = Message::join('users', 'users.id', '=', 'messages.transmitter_id')
            ->where('messages.receiver_id', $currentUserId)
            ->select('messages.*', 'users.name as fullname', 'users.photo')
            ->orderBy('messages.created_at', 'DESC')
            ->get();

        foreach ($messages as $msg) {
            $msg->photo = ParseUrl::contacAtrrS3($msg->photo);
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $messages,
        ]);
    }

    /**
     * GET /api/v1/messages/listNewContacts/{id}
     */
    public function listNewContacts($id)
    {
        $currentUserId = auth()->id();
        $user = auth()->user();
        $isAdmin = $user && (
            (method_exists($user, 'hasRole') && $user->hasRole('Admin'))
            || ($user->id_account_type ?? null) == 1
        );

        // CRM-09: Forzar id del usuario autenticado si no es admin
        if (!$isAdmin) {
            $id = $currentUserId;
        }

        $transmitterCollection = PurchasedCourse::join('courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->where('purchased_courses.user_id', $id)
            ->distinct()
            ->pluck('courses.user_id')
            ->toArray();

        $merged = array_unique($transmitterCollection);
        $userContacts = User::whereIn('id', $merged)
            ->where('id', '!=', $id)
            ->pluck('id')
            ->toArray();

        $data = [];

        foreach ($userContacts as $userId) {
            $contact = User::find($userId);
            if (!$contact) {
                continue;
            }

            $first = Message::where('transmitter_id', $userId)->where('receiver_id', $id)->first();
            $second = Message::where('receiver_id', $userId)->where('transmitter_id', $id)->first();

            if ($first == null && $second == null) {
                $data[] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'photo' => ParseUrl::contacAtrrS3($contact->photo),
                    'lastname' => $contact->last_name,
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * POST /api/v1/messages/sendNewMessage
     */
    public function sendNewMessage(Request $request)
    {
        $message1 = new Message();
        // CRM-09: Forzar transmitter_id como el usuario autenticado para evitar spoofing
        $message1->transmitter_id = auth()->id();
        $message1->receiver_id = $request->id2 ?? $request->receiver_id;
        $message1->message = $request->get('message', 'Hola');
        $message1->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Message created',
            'data' => $message1,
        ]);
    }
}
