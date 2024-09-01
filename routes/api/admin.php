<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmailVerificationController;

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
    });

    Route::controller(UserController::class)->group(function () {
        Route::post('update', 'update');
        Route::post('change-password', 'changePassword');
    });
    // todo : add email verification
    // Route::controller(EmailVerificationController::class)->group(function () {
    //     Route::get('/email/verify', 'VerificationController@verify')->name('verification.notice');
    //     Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
    // });
});



// Route::middleware(["auth:sanctum", "admin"])->group(function () {
//     Route::controller(AuthController::class)->group(function () {
//         Route::get('get', 'getUser');
//         Route::get('permissions', 'permissions');
//         Route::put('update', 'update');
//         Route::post('refresh', 'refresh');
//         Route::post('logout', 'logout');
//         Route::post('change-password', 'changePassword');
//         Route::patch('change-language', 'changeLanguage');
//     });

//     Route::prefix('activity-histories')->controller(ActivityHistoryController::class)->group(function () {
//         Route::get('/', 'get');
//     });

//     Route::prefix('roles')->controller(RoleController::class)->group(function () {
//         Route::get('/', 'get');
//         Route::get('/permissions', 'getPermissions');
//         Route::post('add', 'add');
//         Route::put('{id}/update', 'update');
//         Route::patch('{id}/change-status', 'changeStatus');
//         Route::delete('{id}/delete', 'delete');
//     });

//     Route::prefix('users')->controller(UserController::class)->group(function () {
//         Route::get('/', 'get');
//         Route::get('/roles', 'getRoles');
//         Route::post('add', 'add');
//         Route::put('{id}/update', 'update');
//         Route::patch('{id}/change-status', 'changeStatus');
//     });

//     Route::prefix('clients')->controller(ClientController::class)->group(function () {
//         Route::get('/', 'get');
//         Route::post('add', 'add');
//         Route::put('{id}/update', 'update');
//         Route::patch('{id}/change-status', 'changeStatus');
//     });

//     Route::prefix('offers')->controller(OfferController::class)->group(function () {
//         Route::get('/', 'get');
//         Route::get('/archived', 'archived');
//         Route::post('add', 'add');
//         Route::put('{id}/update', 'update');
//         Route::patch('{id}/change-status', 'changeStatus');
//         Route::patch('{id}/archive', 'archive');
//         Route::patch('{id}/recover', 'recover');
//         Route::delete('{id}/delete', 'delete');
//     });

//     Route::prefix('purchases')->controller(PurchaseController::class)->group(function () {
//         Route::get('/', 'get');
//         Route::get('/archived', 'archived');
//         Route::get('/clients', 'getClients');
//         Route::get('/offers', 'getOffers');
//         Route::post('add', 'add');
//         Route::put('{id}/update', 'update');
//         Route::patch('{id}/change-status', 'changeStatus');
//         Route::patch('{id}/archive', 'archive');
//         Route::patch('{id}/recover', 'recover');
//         Route::delete('{id}/delete', 'delete');
//     });
// });
