<?php

namespace App\Http\Controllers;

use App\Models\Commnent;
use App\Models\User;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    use ResponseFormat;
    
    public function sendComments(Request $request)
    {
        // Validación de entrada
        $validated = $request->validate([
            'receiving_user_id' => 'required|integer|exists:users,id',
            'class_id' => 'required|integer|exists:class,id', 
            'comments' => 'required|string|max:1000'
        ]);

        // FIJO PRINCIPAL: Usar el usuario autenticado, NO el del request
        $authenticatedUserId = Auth::id();
        
        if (!$authenticatedUserId) {
            return $this->responseError('Usuario no autenticado', [], 401);
        }

        DB::beginTransaction();
        
        try {
            $comment = new Commnent();
            $comment->issuing_user_id = $authenticatedUserId; // ← AQUÍ ESTÁ EL FIX
            $comment->receiving_user_id = $validated['receiving_user_id'];
            $comment->class_id = $validated['class_id'];
            $comment->comments = $validated['comments'];

            if ($comment->save()) {
                $user = User::findOrFail($comment->issuing_user_id);
                $data[] = [
                    'comment_id' => $comment->id,
                    'username' => $user->username,
                    'user_photo' => $user->photo,
                    'created_at' => $comment->created_at
                ];
                
                DB::commit();
                return $this->responseOk('Comentario enviado', $data);
            }
            
            DB::rollback();
            return $this->responseError('Error al guardar comentario', [], 500);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->responseError('Error interno del servidor', [], 500);
        }
    }

    public function showComments(Request $request)
    {
        $comments_history = Commnent::join('class', 'commentary.class_id', '=', 'class.id')
            ->where('class.id', '=', $request->class_id)
            ->select('commentary.comments', 'commentary.issuing_user_id','commentary.created_at',DB::raw("date_format(date(commentary.created_at), '%d-%m-%Y') as fecha"))
            ->orderByDesc('commentary.created_at')
            ->limit(15)
            ->get();
        if ($comments_history->count() > 0) {
            foreach ($comments_history as $comment_history) {
                $user = User::findOrFail($comment_history->issuing_user_id);
                $data[] = [
                    'comments' => $comment_history->comments,
                    'username' => $user->username,
                    'fecha' => $comment_history->fecha,
                    'user_photo' =>  $user->photo,
                    'created_at' => $comment_history->created_at
                ];
            }
            return $data;
        }

        $data = "No hay comentarios";
        return $this->responseOk('', $data);
    }
}