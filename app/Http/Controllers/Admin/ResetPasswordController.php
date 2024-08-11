<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Ichtrojan\Otp\Otp;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\auth\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $check = User::where('email', $request->email)->exists();

        if (!$check) {
            return response()->json([
                'message' => 'this email is not registered'
            ], 404);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status)
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users',
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $check = User::where('email', $request->email)->exists();

        if (!$check) {
            return response()->json([
                'message' => 'This email does not exist'
            ], 404);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'status' => 'success',
                'message' => 'Password reset successfully',
            ], 200);
        }

        return response([
            'message' => __($status)
        ], 500);
    }
}
