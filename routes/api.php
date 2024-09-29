<?php

use Illuminate\Support\Facades\Route;



Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')->group(__DIR__ . '/api/admin.php');
