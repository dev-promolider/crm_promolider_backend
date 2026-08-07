<?php

namespace Promolider\Application\RoleRequests\UseCases;

use App\Models\User;
use App\Models\RoleRequest;
use App\Models\ToolPermissionRequest;

class ListRoleRequestsUseCase
{
    public function executeCourseRequests()
    {
        return User::join('role_requests', 'users.id', '=', 'role_requests.id_user')
            ->where('role_requests.status', 1)
            ->select('users.*', 'role_requests.*', 'role_requests.id as request_id')
            ->get();
    }

    public function executeToolRequests()
    {
        return User::join('tool_permission_requests', 'users.id', '=', 'tool_permission_requests.id_user')
            ->where('tool_permission_requests.status', 1)
            ->select('users.*', 'tool_permission_requests.*', 'tool_permission_requests.id as request_id')
            ->get();
    }
}
