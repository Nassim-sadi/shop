<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{


  public function index()
  {
    $categories = Category::with('children')->whereNull('parent_id')->orderBy('order', 'ASC')->get();
    return CategoryResource::collection($categories);
  }
}
