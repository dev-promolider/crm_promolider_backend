<?php

namespace Promolider\Application\RoleRequests\UseCases;

use Illuminate\Support\Facades\DB;

class RejectRoleRequestUseCase
{
    public function executeCourseRequest($requestId, $justification)
    {
        $role_request = DB::table('role_requests')->where('id', $requestId)->where('status', 1)->first();
        if ($role_request) {
            DB::table('role_requests')->where('id', $role_request->id)->update([
                'status' => 3,
                'reason' => $justification
            ]);
            return true;
        }
        throw new \Exception("Request not found for user");
    }

    public function executeToolRequest($requestId, $justification)
    {
        $role_request = DB::table('tool_permission_requests')->where('id', $requestId)->where('status', 1)->first();
        if ($role_request) {
            DB::table('tool_permission_requests')->where('id', $role_request->id)->update([
                'status' => 3,
                'reason' => $justification
            ]);
            return true;
        }
        throw new \Exception("Request not found for user");
    }
}
