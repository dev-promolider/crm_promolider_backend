<?php

namespace Promolider\Infrastructure\RoleRequests\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notifications;

class UserRoleRequestController extends Controller
{
    public function applyForCourseRole(Request $request)
    {
        $userId = $request->user()->id;

        // Check if a request already exists
        $existing = DB::table('role_requests')
            ->where('id_user', $userId)
            ->where('status', 1)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Ya tienes una solicitud en revisión.'], 400);
        }

        // Insert new request
        DB::table('role_requests')->insert([
            'id_user' => $userId,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Trigger notification for admins
        $notification = new Notifications();
        $notification->id_generator = $userId;
        $notification->id_receiver = 1; // Admin user ID
        $notification->title = 'Nueva solicitud de Creador';
        $notification->body = 'El usuario ' . $request->user()->name . ' ha solicitado ser creador de cursos.';
        $notification->type = 1;
        $notification->save();

        return response()->json(['message' => 'Solicitud enviada correctamente. Próximamente evaluaremos tu perfil.'], 200);
    }
}
