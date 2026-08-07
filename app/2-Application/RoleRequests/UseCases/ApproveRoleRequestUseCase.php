<?php

namespace Promolider\Application\RoleRequests\UseCases;

use App\Models\User;
use App\Models\Notifications;
use App\Events\NewNotificationEvent;
use Illuminate\Support\Facades\DB;

class ApproveRoleRequestUseCase
{
    public function executeCourseRequest($requestId)
    {
        $role_request = DB::table('role_requests')->where('id', $requestId)->where('status', 1)->first();
        if ($role_request) {
            DB::table('role_requests')->where('id', $role_request->id)->update(['status' => 2]);
            
            $userId = $role_request->id_user;
            $user = User::findOrFail($userId);
            
            // Assign the Producer role (and remove Distributor if syncRoles is used, but we can just add it)
            // It's safer to syncRoles if they should strictly be a Producer now.
            $user->syncRoles(['Producer']);
            $user->givePermissionTo('courses.create', 'courses.subs', 'masterclass.create');
            
            // Send notification
            $notification = new Notifications();
            $notification->id_generator = 1; // Admin
            $notification->id_receiver = $userId;
            $notification->title = '¡Felicidades, ahora eres Creador de Cursos!';
            $notification->body = 'Tu solicitud ha sido aprobada. Por favor, cierra sesión y vuelve a ingresar para activar tus nuevas herramientas de monetización.';
            $notification->type = 1; // 1 = info/success
            $notification->save();
            
            // Broadcast the event
            broadcast(new NewNotificationEvent([
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'type' => 'producer_approved', // Special type for frontend modal
                'id_receiver' => $userId
            ]));
            
            return true;
        }
        throw new \Exception("Request not found for user");
    }

    public function executeToolRequest($requestId)
    {
        $role_request = DB::table('tool_permission_requests')->where('id', $requestId)->where('status', 1)->first();
        if ($role_request) {
            DB::table('tool_permission_requests')->where('id', $role_request->id)->update(['status' => 2]);
            
            $userId = $role_request->id_user;
            $user = User::findOrFail($userId);
            $user->givePermissionTo('marketing.create', 'masterclass.create');
            
            return true;
        }
        throw new \Exception("Request not found for user");
    }
}
