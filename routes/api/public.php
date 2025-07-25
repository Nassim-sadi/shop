<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')->controller(CategoryController::class)->group(function () {
  Route::get('/', 'getRootCategories');
  Route::get('/leaf', 'getLeafCategories');

  Route::get('/homeCategories', 'getHomepageCategories');
  Route::get('/{categorySlug}', 'getCategoryBySlug')
    ->where('categorySlug', '[a-zA-Z0-9\-_]+');
  Route::get('/{categorySlug}/products', [ProductController::class, 'getProductsByCategory']);
});

Route::prefix('products')->controller(ProductController::class)->group(function () {

  Route::get('/featured', 'getFeatured');
  Route::get('/similar/{categoryId}', 'getSimilar');
  Route::get('/search', 'search');
  Route::get('/{productSlug}', 'getProductBySlug');
});
