<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\ActivityHistoryJob;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use UA;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
        $user = new User();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        // login user

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        $user->assignRole('User');
        event(new Registered($user));
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 200);
    }

    public function getUser(Request $request)
    {
        $user = $request->user();
        return new UserResource($user);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'email' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $expiration = null;
        if ($request->remember_me) {
            $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        } else {
            $token = $user->createToken($user->name . '-AuthToken', ['*'], now()->addHours(2))->plainTextToken;
            $expiration = now()->addHours(2)->toDateTimeString();
        }
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_at' => $expiration

            ]
        ], 200);
    }

    public function refresh(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $token = $request->user()->createToken($request->user()->name . '-AuthToken')->plainTextToken;
        return response()->json([
            'status' => 'success',
            'user' => $request->user(),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]
        );
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255',
        ]);

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

        return response()->json(['success' => 'User updated successfully', 'user' => $user], 200);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'old_password' => 'required|string|min:8',
            'password' => 'required|string|min:8|confirmed|different:password',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Old password does not match'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['success' => 'Password changed successfully'], 200);
    }
}
