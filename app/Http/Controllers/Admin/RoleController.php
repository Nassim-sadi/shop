<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Roles\PermissionResource;
use App\Http\Resources\Roles\RolesResource;
use App\Jobs\ActivityHistoryJob;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use UA;

class RoleController extends Controller
{

    public function getRoles(Request $request)
    {
        // $this->authorize('view', ActivityHistory::class);
        $roles = Role::with('permissions')->withCount('users')->get(); // Eager load 'permissions'
        return RolesResource::collection($roles);
    }

    public function getPermissions(Request $request)
    {
        // $this->authorize('view', ActivityHistory::class);
        return response()->json(['permissions' => PermissionResource::collection(Permission::all())], 200);
    }


    public function create(Request $request)
    {

        // $this->authorize('view', ActivityHistory::class);
        $request->validate([
            'name' => 'required|unique:roles,name,except,id',
            'description' => 'nullable|string|max:255',
            'color' => ['required', 'regex:/^(?:[0-9a-f]{3}){1,2}$/i'],
            'text_color'
            => ['required', 'regex:/^(?:[0-9a-f]{3}){1,2}$/i'],
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // After validation, check if any other role has the same set of permissions
        $roleWithSamePermissions = Role::whereHas('permissions', function ($query) use ($request) {
            $query->whereIn('id', $request->permissions);
        }, '=', count($request->permissions)) // Ensure exact match by counting
            ->whereDoesntHave('permissions', function ($query) use ($request) {
                $query->whereNotIn('id', $request->permissions);
            })
            ->where('id', '!=', $role->id ?? null) // Exclude the current role (if updating)
            ->first();

        if ($roleWithSamePermissions) {
            return response()->json(['permissions' => 'Another role has the same set of permissions.'], 400);
        }


        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'text_color' => $request->text_color,
        ]);


        // Assign permissions
        $role->syncPermissions($request->permissions);
        //load permissions
        $role->load('permissions');

        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'roles',
                'action' => 'create',
                'data' => ['role' => $role],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );


        return response()->json(['success' => 'Role created successfully', 'role' => RolesResource::make($role)], 200);
    }


    public function update(Request $request)
    {
        // $this->authorize('view', ActivityHistory::class);
        $request->validate([
            'id' => 'required|exists:roles',
            'name' => 'required|unique:roles,name,' . $request->id . ',id',
            'description' => 'nullable|string|max:255',
            'color' => ['required', 'regex:/^(?:[0-9a-f]{3}){1,2}$/i'],
            'text_color'
            => ['required', 'regex:/^(?:[0-9a-f]{3}){1,2}$/i'],
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role = Role::find($request->id);

        $roleWithSamePermissions = Role::whereHas('permissions', function ($query) use ($request) {
            $query->whereIn('id', $request->permissions);
        }, '=', count($request->permissions))
            ->whereDoesntHave('permissions', function ($query) use ($request) {
                $query->whereNotIn('id', $request->permissions);
            })
            ->where('id', '!=', $role->id ?? null)
            ->first();

        if ($roleWithSamePermissions) {
            return response()->json(['permissions' => 'Another role has the same set of permissions.'], 400);
        }

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'text_color' => $request->text_color,
        ]);
        // Sync permissions
        $role->syncPermissions($request->permissions);
        //load permissions
        $role->load('permissions');
        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'roles',
                'action' => 'update',
                'data' => ['role' => $role],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );

        return response()->json(['success' => 'Role updated successfully', 'role' => RolesResource::make($role)], 200);
    }



    public function delete(Request $request)
    {
        // $this->authorize('view', ActivityHistory::class);
        $request->validate([
            'id' => 'required|exists:roles',
        ]);
        $role = Role::find($request->id);

        if ($role->name === 'Super Admin' || $role->name === 'user') {
            return response()->json(['error' => 'Super Admin role cannot be deleted'], 400);
        }

        if ($role->users->count() > 0) {
            return response()->json(['error' => 'Role has users'], 400);
        }

        $role->permissions()->detach();
        $role->delete();
        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'roles',
                'action' => 'delete',
                'data' => ['role' => $role],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['success' => 'Role deleted successfully'], 200);
    }
}
