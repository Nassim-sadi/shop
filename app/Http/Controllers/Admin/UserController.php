<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ActivityHistoryJob;
use Illuminate\Support\Facades\Hash;
use UA;

class UserController extends Controller

{


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
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Old password does not match'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['success' => 'Password changed successfully'], 200);
    }
}
