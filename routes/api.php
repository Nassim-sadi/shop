<?php

use Illuminate\Support\Facades\Route;



Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')->group(__DIR__ . '/api/admin.php');


Route::prefix('categories')->controller('App\Http\Controllers\CategoryController')->group(function () {
  Route::get('/', 'index');
});
