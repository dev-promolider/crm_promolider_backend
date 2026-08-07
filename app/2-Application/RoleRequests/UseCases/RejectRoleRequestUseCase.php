<?php

namespace Promolider\Application\RoleRequests\UseCases;

use App\Models\RoleRequest;
use App\Models\ToolPermissionRequest;

class RejectRoleRequestUseCase
{
    public function executeCourseRequest($userId, $justification)
    {
        $role_request = RoleRequest::where('id_user', $userId)->first();
        if ($role_request) {
            $role_request->status = 3;
            $role_request->reason = $justification;
            $role_request->update();
            return true;
        }
        throw new \Exception("Request not found for user");
    }

    public function executeToolRequest($userId, $justification)
    {
        $role_request = ToolPermissionRequest::where('id_user', $userId)->first();
        if ($role_request) {
            $role_request->status = 3;
            $role_request->reason = $justification;
            $role_request->update();
            return true;
        }
        throw new \Exception("Request not found for user");
    }
}
