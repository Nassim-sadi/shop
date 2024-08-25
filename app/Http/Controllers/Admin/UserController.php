<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller

{
    public function edit(Request $request)
    {
        $user = $request->user();

        return $user;
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            // Handle old image removal
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

        $user->save();
        $user->refresh();

        return response()->json(['success' => 'User updated successfully', 'user' => $user], 200);
    }
}
