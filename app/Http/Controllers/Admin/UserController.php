<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Users\UserCollection;
use App\Http\Resources\Admin\Users\UserResource;
use App\Jobs\ActivityHistoryJob;
use App\Models\User;
use Str;
use UA;

class UserController extends Controller

{
    public function getUsers(Request $request)
    {

        $this->authorize('user_view');
        $user = $request->user();
        $users = User::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->where(function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->whereRaw("LOWER(firstname) LIKE ?", ['%' . strtolower($request->keyword) . '%'])
                        ->orWhereRaw("LOWER(lastname) LIKE ?", ['%' . strtolower($request->keyword) . '%']);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereRaw("LOWER(CONCAT(firstname, ' ', lastname)) LIKE ?", ['%' . strtolower($request->keyword) . '%'])
                            ->orWhereRaw("LOWER(CONCAT(lastname, ' ', firstname)) LIKE ?", ['%' . strtolower($request->keyword) . '%']);
                    })
                    ->orWhereRaw("LOWER(email) LIKE ?", ['%' . strtolower($request->keyword) . '%']);
            })->when(isset($request->role) && $request->role !== '', function ($query) use ($request) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('id', $request->role);
                });
            })
            ->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('created_at', 'DESC')
            ->when($user->hasRole("Super Admin"), function ($q) use ($request) {
                $q->when(isset($request->deleted) && $request->deleted !== '', function ($q) use ($request) {
                    if ($request->deleted === 'with') {
                        return $q->withTrashed();
                    } elseif ($request->deleted === 'only') {
                        return $q->onlyTrashed();
                    }
                });
            })
            ->paginate($request->per_page);

        return new UserCollection($users);
    }

    public function changeStatus(Request $request)
    {
        $this->authorize('user_changeStatus');
        $request->validate([
            'status' => 'required|in:0,1',
            'id' => 'required|exists:users,id|not_in:' . $request->user()->id,
        ]);
        $user = User::find($request->id);
        $user->status = $request->status;
        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'users',
                'action' => 'update',
                'data' => ['changes' => ['status' => $request->status], 'user' => $user],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        //TODO : push a notification to user that he is inactive by admin
        $user->save();
        return response()->json(['success' => 'Status changed successfully', 'status' => $user->status], 200);
    }

    public function delete($id, Request $request)
    {
        $this->authorize('user_delete');

        $user = User::findOrFail($id);
        if ($user->id == $request->user()->id) {
            return response()->json(['error' => 'You cannot delete yourself'], 400);
        }

        if ($user->hasRole('Super Admin')) {
            return response()->json(['error' => 'Super Admin cannot be deleted'], 400);
        }

        $user->delete();
        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'users',
                'action' => 'delete',
                'data' => ['user' => $user],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['success' => 'User deleted successfully', 'deleted_at' => $user->deleted_at], 200);
    }

    public function forceDelete($id,  Request $request)
    {
        $this->authorize('user_permaDelete');


        $user = User::onlyTrashed()->findOrFail($id);
        if ($user->id == $request->user()->id) {
            return response()->json(['error' => 'You cannot delete yourself'], 400);
        }

        if ($user->hasRole('Super Admin')) {
            return response()->json(['error' => 'Super Admin cannot be deleted'], 400);
        }


        // delete user profile image
        if ($user->image) {
            $oldImage = basename(parse_url($user->image, PHP_URL_PATH));
            $oldImagePath = public_path('/storage/images/profile/' . $oldImage);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $user->forceDelete();

        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'users',
                'action' => 'forceDelete',
                'data' => ['user' => $user],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['success' => 'User deleted successfully'], 200);
    }



    public function restore($id, Request $request)
    {
        $this->authorize('user_restore');


        $user = User::withTrashed()->findOrFail($request->id);

        if ($user->id == $request->user()->id) {
            return response()->json(['error' => 'You cannot restore yourself'], 400);
        }

        if ($user->hasRole('Super Admin')) {
            return response()->json(['error' => 'Super Admin cannot be restored'], 400);
        }

        $user->restore();

        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'users',
                'action' => 'restore',
                'data' => ['user' => $user],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );

        return response()->json(['success' => 'User restored successfully', 'user' => $user], 200);
    }

    public function update(Request $request)
    {
        $this->authorize('user_edit');

        $request->validate([
            'id' => 'required|exists:users',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255',
        ]);

        $user = User::find($request->id);
        $oldUser = clone $user;

        if ($request->hasFile('image')) {
            if ($user->image) {
                $oldImage = basename(parse_url($user->image, PHP_URL_PATH));
                $oldImagePath = public_path('/storage/images/profile/' . $oldImage);

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Handle new image upload
            $image = $request->file('image');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->extension();
            $imageName = Str::slug($originalName) . '-' . time() . '.' . $extension;
            $destinationPath = public_path('/storage/images/profile');
            $image->move($destinationPath, $imageName);
            $user->image = $imageName;
        }

        // Update other user details
        if ($request->has('firstname')) {
            $user->firstname = $request->firstname;
        }

        if ($request->has('lastname')) {
            $user->lastname = $request->lastname;
        }
        $changes = $user->getDirty();
        $user->save();
        $user->refresh();
        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'users',
                'action' => 'update',
                'data' => ['changes' => $changes, 'user' => $oldUser],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,

        );

        return response()->json(['success' => 'User updated successfully', 'user' => new UserResource($user)], 200);
    }

    public function changeRole(Request $request)
    {

        $this->authorize('user_changeRole');
        $request->validate([
            'user_id' => 'required|exists:users,id|not_in:' . $request->user()->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles($request->role_id);

        // log activity
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'users',
                'action' => 'changeRole',
                'data' => ['user' => $user],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );

        $user->refresh();
        $user->load('roles');
        return response()->json(['success' => 'User role changed successfully', 'user' => new UserResource($user)], 200);
    }
}
