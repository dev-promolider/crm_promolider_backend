<?php

namespace Promolider\Application\RoleRequests\UseCases;

use App\Models\RoleRequest;
use App\Models\ToolPermissionRequest;
use App\Models\User;

class ApproveRoleRequestUseCase
{
    public function executeCourseRequest($userId)
    {
        $role_request = RoleRequest::where('id_user', $userId)->first();
        if ($role_request) {
            $role_request->status = 2;
            $role_request->update();
            $user = User::findOrFail($userId);
            $user->givePermissionTo('courses.create', 'courses.subs', 'masterclass.create');
            return true;
        }
        throw new \Exception("Request not found for user");
    }

    public function executeToolRequest($userId)
    {
        $role_request = ToolPermissionRequest::where('id_user', $userId)->first();
        if ($role_request) {
            $role_request->status = 2;
            $role_request->update();
            $user = User::findOrFail($userId);
            $user->givePermissionTo('marketing.create', 'masterclass.create');
            return true;
        }
        throw new \Exception("Request not found for user");
    }
}
