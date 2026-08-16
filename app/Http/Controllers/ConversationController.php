<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Course;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with([
            'course:id,title,slug,url_portada',
            'teacher:id,name,last_name,photo',
            'student:id,name,last_name,photo',
            'latestMessage:id,conversation_id,transmitter_id,receiver_id,message,created_at',
        ])
            ->where('student_id', $userId)
            ->orWhere('teacher_id', $userId)
            ->latest()
            ->get();

        $conversations->each(function (Conversation $conversation) {
            if ($conversation->teacher) {
                $conversation->teacher->photo = $this->photoUrl($conversation->teacher->photo);
            }
            if ($conversation->student) {
                $conversation->student->photo = $this->photoUrl($conversation->student->photo);
            }
        });

        return response()->json([
            'data' => $conversations,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id'  => 'required|exists:courses,id',
            'student_id' => 'nullable|exists:users,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $userId = $request->user()->id;

        // Quien abre el chat: si es el dueño del curso (profesor), necesita
        // indicar el alumno; si no, el alumno autenticado es el que inicia.
        if ($course->user_id === $userId) {
            $studentId = $request->student_id;
            if (!$studentId) {
                return response()->json(['message' => 'Indica el alumno con quien deseas conversar.'], 422);
            }
        } else {
            $studentId = $userId;
        }

        // Solo se permite conversar si el alumno está inscrito en el curso.
        $enrolled = \Illuminate\Support\Facades\DB::table('purchased_courses')
            ->where('user_id', $studentId)
            ->where('course_id', $course->id)
            ->exists();

        if (!$enrolled) {
            return response()->json(['message' => 'El alumno debe estar inscrito en el curso para poder conversar.'], 422);
        }

        $conversation = Conversation::firstOrCreate([
            'course_id'  => $course->id,
            'student_id' => $studentId,
            'teacher_id' => $course->user_id,
        ]);

        $conversation->load([
            'course:id,title,slug,url_portada',
            'teacher:id,name,last_name,photo',
            'student:id,name,last_name,photo',
            'latestMessage:id,conversation_id,transmitter_id,receiver_id,message,created_at',
        ]);

        if ($conversation->teacher) {
            $conversation->teacher->photo = $this->photoUrl($conversation->teacher->photo);
        }
        if ($conversation->student) {
            $conversation->student->photo = $this->photoUrl($conversation->student->photo);
        }

        return response()->json([
            'data' => $conversation,
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
}
