<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSentEvent;
use App\Models\User;
use App\Models\Message;
use App\Traits\ResponseFormat;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use App\Models\PurchasedCourse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use ResponseFormat;

    /**
     * Show message of users
     * @param $email {string} -> email to user
     * @return \Illuminate\Http\Response
     */
    public function show($email)
    {
        $idUser = auth()->user()->id;
        if ($idUser2 = User::where('email', $email)->first()) {
            $idUser2 = $idUser2->id;
            $data = Message::select('users.name', 'messages.message', 'messages.created_at')
                ->join('users', 'users.id', '=', 'messages.transmitter_id')
                ->where([
                    ['messages.transmitter_id', '=', $idUser],
                    ['messages.receiver_id', '=', $idUser2]
                ])
                ->orWhere([
                    ['messages.transmitter_id', '=', $idUser2],
                    ['messages.receiver_id', '=', $idUser]
                ])
                ->orderBy('messages.created_at', 'ASC')
                ->get();
            if (isset($data[0])) {
                return $this->responseOk('', $data);
            } else {
                return ["error" => "No conversations"];
            }
        } else {
            return ["error" => "No conversations"];
        }
    }

    /**
     * Show user's list with messages
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        if ($msj = Message::MessageSelect()->MessageOrder()) {
            $json = [];
            foreach ($msj as $key => $value) {
                if (count($json) >= 5) {
                    break;
                } else {
                    $json[] = $msj[$key]->first();
                }
            }
            return $this->responseOk('', $json);
        } else {
            return ['error' => "no conversations"];
        }
    }

    /**
     * Store new messages of users
     * @param $request {Request}
     *   ->$id -> id of the user receive
     *   ->$message -> the message
     * @return \Illuminate\Http\Response
     */
    public function addMessage(Request $request)
    {
        $msj = new Message();
        $msj->transmitter_id = auth()->user()->id;
        $msj->receiver_id = $request->receiver_id;
        $msj->message = $request->message;
        $msj->save();

        $user = auth()->user()->id;

        if ($user > $msj->receiver_id) {
            $chatroom = $msj->receiver_id . $user;
        } else {
            $chatroom = $user . $msj->receiver_id;
        }

        // Intentar hacer broadcast, pero no fallar si hay error
        try {
            broadcast(new MessageSentEvent($msj, $chatroom));
        } catch (\Exception $e) {
            // Log el error pero no fallar la operación
            \Log::warning('Broadcasting failed: ' . $e->getMessage());
        }

        return $this->responseOk('', 'message success');
    }

    /**
     * Show all messages
     * @return \Illuminate\Http\Response
     */
    public function listAll()
    {
        if ($msj = Message::MessageSelect()->MessageOrder()) {
            $json = [];
            foreach ($msj as $key => $value) {
                $json[] = $msj[$key]->first();
            }
            return $this->responseOk('', $json);
        } else {
            return ['error' => "no conversations"];
        }
    }

    /**
     * CORREGIDO: Solo mostrar contactos del usuario actual
     * @return \Illuminate\Http\Response
     */
    public function listContacts()
    {
        $currentUserId = auth()->user()->id;
        
        // Obtener IDs de usuarios con los que ha tenido conversaciones
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
            return response()->json(['error' => 'No tienes contactos'], 404);
        }
        
        $contacts = [];
        
        foreach ($contactIds as $contactId) {
            $contact = User::select('id', 'name', 'last_name', 'photo', 'email')
                          ->where('id', $contactId)
                          ->first();
            
            if (!$contact) continue;
            
            // Obtener el último mensaje entre el usuario actual y este contacto
            $lastMessage = Message::where(function($query) use ($currentUserId, $contactId) {
                    $query->where([
                        ['transmitter_id', $currentUserId],
                        ['receiver_id', $contactId]
                    ])->orWhere([
                        ['transmitter_id', $contactId],
                        ['receiver_id', $currentUserId]
                    ]);
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastMessage) {
                $contacts[] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'lastname' => $contact->last_name,
                    'photo' => $contact->photo,
                    'email' => $contact->email,
                    'last_message' => $lastMessage->message,
                    'last_message_time' => $lastMessage->created_at,
                    'is_sender' => $lastMessage->transmitter_id === $currentUserId
                ];
            }
        }
        
        // Ordenar por último mensaje más reciente
        usort($contacts, function($a, $b) {
            return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
        });
        
        Log::info('User accessed their contacts', [
            'user_id' => $currentUserId,
            'contacts_count' => count($contacts)
        ]);
        
        return response()->json([
            'data' => $contacts,
            'total' => count($contacts)
        ]);
    }

    public function listNewContacts($id)
    {
        $transmitter_collection = PurchasedCourse::join('courses', 'courses.id', '=', 'purchased_courses.course_id')->where('purchased_courses.user_id', $id)->distinct()->pluck('courses.user_id')->toArray();
        $merged = array_unique($transmitter_collection);
        $user_contacts = User::whereIn('id', array_unique($merged))->whereNotIn('id', array($id))->pluck('id')->toArray();


        $data = [];

        foreach ($user_contacts as $user_id) {
            $contact = User::where('id', $user_id)->get()->first();
            $first = Message::where('transmitter_id', $user_id)->where('receiver_id', $id)->get()->first();
            $second = Message::where('receiver_id', $user_id)->where('transmitter_id', $id)->get()->first();

            if ($first == null && $second == null) {
                $data[] = array(
                    'id'        => $contact->id,
                    'name'     => $contact->name,
                    'photo' => $contact->photo,
                    'lastname'     => $contact->last_name,
                );
            }
        }

        return $data;
    }

    public function sendNewMessage(Request $request)
    {
        $message1 = new Message();
        $message1->transmitter_id = $request->id;
        $message1->receiver_id = $request->id2;
        $message1->message = 'Hola';
        $message1->save();
    }


    /**
     * CORREGIDO: Obtener contenido de mensajes solo si el usuario actual es parte de la conversación
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getContent(Request $request)
    {
        $currentUserId = auth()->id();
    
        // Loguear el request recibido y el usuario actual
        \Log::info('Intento de acceso a mensajes', [
            'current_user_id' => $currentUserId,
            'request_transmitter_id' => $request->transmitter_id,
            'request_receiver_id' => $request->receiver_id,
        ]);
    
        // Validar que el usuario autenticado esté en la conversación
        if ($request->transmitter_id != $currentUserId && $request->receiver_id != $currentUserId) {
            \Log::warning('Acceso denegado a mensajes', [
                'current_user_id' => $currentUserId,
                'request_transmitter_id' => $request->transmitter_id,
                'request_receiver_id' => $request->receiver_id,
            ]);
        
            return response()->json(['error' => 'No tienes permiso para ver estos mensajes'], 403);
        }
    
        // Traer los mensajes de la conversación entre los dos usuarios
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
        
        \Log::info('Mensajes recuperados correctamente', [
            'current_user_id' => $currentUserId,
            'messages_count' => $messages->count(),
        ]);
    
        return response()->json([
            'data' => $messages
        ]);
    }
}