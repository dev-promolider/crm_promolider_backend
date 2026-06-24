<?php

namespace App\Http\Controllers;

use App\Models\RoleRequest;
use App\Models\ToolPermissionRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RoleRequestController extends Controller
{
    public function getRoleRequest(){
        $role_request = RoleRequest::where('id_user', auth()->user()->id)->first();
        if($role_request == null){
            return false;
        }else{
            return $role_request;
        }
    }

    public function getRoleToolsRequest(){
        $role_request = ToolPermissionRequest::where('id_user', auth()->user()->id)->first();
        if($role_request == null){
            return false;
        }else{
            return $role_request;
        }
    }

    public function changeRole(){
        $change_role = new RoleRequest();
        $change_role->id_user = auth()->user()->id;
        $change_role->status = 1;
        $change_role->save();
    }

    public function changeRoleTools(){
        $change_role = new ToolPermissionRequest();
        $change_role->id_user = auth()->user()->id;
        $change_role->status = 1;
        $change_role->save();
    }

    public function confirmChange(Request $request){
        $role_request = RoleRequest::where('id_user', $request->id)->first();
        $role_request->status = 2;
        $role_request->update();
        $user = User::findOrFail($request->id);
        $user->givePermissionTo('courses.create', 'courses.subs', 'masterclass.create');
    }

    public function confirmToolChange(Request $request){
        $role_request = ToolPermissionRequest::where('id_user', $request->id)->first();
        $role_request->status = 2;
        $role_request->update();
        $user = User::findOrFail($request->id);
        $user->givePermissionTo('marketing.create', 'masterclass.create');
    }

    public function rejectChange(Request $request){
        $role_request = RoleRequest::where('id_user', $request->id)->first();
        $role_request->status = 3;
        $role_request->reason = $request->justification;
        $role_request->update();
    }

    public function rejectToolChange(Request $request){
        $role_request = ToolPermissionRequest::where('id_user', $request->id)->first();
        $role_request->status = 3;
        $role_request->reason = $request->justification;
        $role_request->update();
    }
}
