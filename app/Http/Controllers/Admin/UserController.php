<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller

{
    public function edit(Request $request)
    {
        $user = $request->user();

        return $user;
    }

    public function updateProfilePicture(Request $request)
    {

        $user = $request->user();

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name =
                pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/storage/images/profile');
            $image->move($destinationPath, $name);
            $user->image = $name;
            $user->save();
            $user->refresh();
            return response()->json(['success' => 'Image uploaded successfully', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'No image found + ' . $request->image], 404);
        }
    }
}
