<?php

namespace App\Http\Controllers;

use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
  public function getRootCategories()
  {
    $categories = Category::with('children')->whereNull('parent_id')->orderBy('order', 'ASC')->get();
    return CategoryResource::collection($categories);
  }


  public function getLeafCategories()
  {
    $categories = Category::whereDoesntHave('children')->orderBy('order', 'ASC')->get();
    return CategoryResource::collection($categories);
  }

  public function getCategoryBySlug($categorySlug)
  {
    $category = Category::where('slug', $categorySlug)->firstOrFail();

    return response()->json([
      'category' => $category,
    ]);
  }
}
