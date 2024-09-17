<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\Users\UserCollection;
use App\Jobs\ActivityHistoryJob;
use App\Models\User;
use Debugbar;
use UA;

class UserController extends Controller

{



    public function getUsers(Request $request)
    {
        // $this->authorize('view', ActivityHistory::class);

        $users = User::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->where(function ($q) use ($request) {
                $q->where('firstname', 'Like', '%' . $request->keyword . '%')
                    ->orWhere('lastname', 'Like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'Like', '%' . $request->keyword . '%');
            })->when(isset($request->role) && $request->role !== '', function ($query) use ($request) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('id', $request->role);
                });
            })
            ->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate($request->per_page);

        return new UserCollection($users);
    }

    public function changeStatus(Request $request)
    {
        // $this->authorize('changeStatus', User::class);
        debugbar()->info($request->status);
        // add validation to id not same as current user
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

    public function delete(Request $request)
    {

        $request->validate([
            'id' => 'required|exists:users,id|not_in:' . $request->user()->id,
        ]);

        $user = User::find($request->id);
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
        return response()->json(['success' => 'User deleted successfully'], 200);
    }
}
