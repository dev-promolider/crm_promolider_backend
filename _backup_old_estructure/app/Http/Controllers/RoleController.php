<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:role.index')->only('index');
        $this->middleware('can:action-list-role')->only('show');
        $this->middleware('can:action-remove-role')->only('removeRole');
        $this->middleware('can:action-list-permissions')->only('list', 'submodule', 'actions');
        $this->middleware('can:action-add-remove-permission')->only('addPermission', 'removePermission');
    }

    public function index()
    {
        return view('content.roles.index');
    }

    public function show()
    { //listar todos los roles
        $roles = Role::all();
        return $roles;
    }

    public function getSections($role)
    {
        $dataset = Permission::where('section', 'true')
            ->get();
        foreach ($dataset as $data) {
            if ($this->RolePermission($role, $data->id)) {
                $data->check = true;
            } else {
                $data->ckeck = false;
            }
        }
        return $dataset;
    }

    public function getModules(Request $request)
    {
        $dataset = Permission::where('module', $request->section)
            ->get();
        foreach ($dataset as $data) {
            if ($this->RolePermission($request->role, $data->id)) {
                $data->check = true;
            } else {
                $data->ckeck = false;
            }
        }
        return $dataset;
    }

    public function getActions(Request $request)
    {
        $dataset = Permission::where('action', $request->module)
            ->get();
        foreach ($dataset as $data) {
            if ($this->RolePermission($request->role, $data->id)) {
                $data->check = true;
            } else {
                $data->ckeck = false;
            }
        }
        return $dataset;
    }

    public function addPermission(Request $request)
    {
        $role = Role::find($request->role);
        $permission = Permission::find($request->permission);
        $permission->assignRole($role);
        return [
            "message" => "Assigned permission",
            "check" => true
        ];
    }

    public function removePermission(Request $request)
    {
        $role = Role::find($request->role);
        $permission = Permission::find($request->permission);
        $role->revokePermissionTo($permission);
        return [
            "message" => "Permission removed",
            "check" => false
        ];
    }

    public function removeRole(Role $role)
    {
        $role->delete();
        $data = $this->show();
        return $data;
    }

    public function RolePermission($role, $id)
    {
        $response = DB::table('role_has_permissions')->where('role_id', $role)->where('permission_id', $id)->exists();
        return $response;
    }

    public function permissionCreate()
    {
        return view("content.roles.create-permission");
    }

    public function permissionStore(Request $request)
    {
        Permission::create([
            'name' => $request->name,
            'description' => $request->description,
            $request->tipo => true,
        ]);
        $msg = "El permiso se creó satisfactoriamente";
        return redirect(route("role.index"))->withSuccess($msg);;
    }

    public function roleCurrentUser()
    {
        $id_user = auth()->user()->id;
        $user = User::where('id', $id_user)->first();
        $user_role = $user->getRoleNames();
        return response()->json($user_role, 200);
    }

    public function usersDistributor()
    {
        $usuarios = User::role('Distributor')
            ->select('id', 'username')
            ->get();
    
        return response()->json($usuarios, 200);
    }
}
