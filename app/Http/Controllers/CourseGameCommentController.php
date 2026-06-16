<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommentDynamic;
use App\Traits\ResponseFormat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CourseGameCommentController extends Controller
{
    use ResponseFormat;
    public function createDynamicComment(Request $request){
        
        DB::beginTransaction();
        try{
        $comment=new CommentDynamic;
                $comment->id_author=$request->id_user;
                $comment->id_course_games=$request->id_course_games;
                $comment->content=$request->content;
                
                if(!$comment->save()){
                    
                    throw new \Exception('Error al registrar mensaje');
                    
                }
                
                $user = User::findOrFail((int)$comment->id_author);
                
                    $data[] = [
                        'id' => $comment->id,
                        'username' => $user->username,
                        'user_photo' =>  $user->photo,
                        'created_at' => $comment->created_at
                    ];
        DB::commit();
        return $this->responseOk('Comentario enviado', $data);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
        
        

    }
    public function listDynamicComments(Request $request,$id_course_game){
       

        $dynamicComments = CommentDynamic::where('id_course_games', $id_course_game)
        ->with(['author' => function ($query) {
            // If you need to select specific columns from the user, do it here
            $query->select('id', 'username', 'photo'); // Ensure 'id' is included for the relation mapping
        }])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($comment) {
            // Access the properties and the accessor
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'username' => $comment->author->username ?? null, // Assuming username is directly accessible
                'photo' => $comment->author->photo ?? null, // This will trigger the accessor
            ];
        });

        // $dynamicComments=CommentDynamic::where('id_course_games',$id_course_game)
        // ->join('users','users.id','=','comments_dynamic.id_author')
        // ->select('comments_dynamic.id','users.photo','users.username','comments_dynamic.content','comments_dynamic.created_at')
        // ->orderBy('comments_dynamic.created_at','desc')
        // ->get();
Log::info($dynamicComments);
        return $this->responseOk('Comentarios obtenidos',$dynamicComments);

    }
}
