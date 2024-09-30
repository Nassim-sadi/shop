<?php

use App\Http\Controllers\Admin\ActivityHistoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| API Admin Routes
|--------------------------------------------------------------------------
*/




Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
});


Route::controller(ResetPasswordController::class)->group(function () {
    Route::post('password/send-link', 'sendLink')->middleware('password_reset_link',  'guest');
    Route::post('password/reset', 'reset')->Middleware('guest');
});

Route::middleware(["auth:sanctum"])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('refresh', 'refresh');
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
        Route::post('update', 'update');
        Route::post('change-password', 'changePassword');
    });

    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/', 'getUsers');
        Route::patch('change-status', 'changeStatus');
        Route::delete('delete/{id}', 'delete');
        Route::delete('delete-permanently/{id}', 'forceDelete');
        Route::post('restore/{id}', 'restore');
        Route::post('update/{id}', 'update');
        Route::patch('change-role', 'changeRole');
    });

    Route::prefix('activity-histories')->controller(ActivityHistoryController::class)->group(function () {
        Route::get('/', 'get');
    });

    Route::prefix('roles')->controller(RoleController::class)->group(function () {
        Route::get('/', 'getRoles');
        Route::get('/permissions', 'getPermissions');
        Route::post('/create', 'create');
        Route::put('/update', 'update');
        Route::delete('/delete/{id}', 'delete');
    });


    // todo : add email verification
    // Route::controller(EmailVerificationController::class)->group(function () {
    //     Route::get('/email/verify', 'VerificationController@verify')->name('verification.notice');
    //     Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
    // });
});
