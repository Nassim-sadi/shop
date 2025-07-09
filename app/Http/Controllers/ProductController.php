<?php

namespace App\Http\Controllers;

use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  public function getProductsByCategory(Request $request, $categorySlug)
  {
    $category = Category::where('slug', $categorySlug)->firstOrFail();

    $products = Product::where('category_id', $category->id)
      ->orWhereHas('category', fn($q) => $q->where('parent_id', $category->id))
      ->with(['category', 'images'])
      ->paginate(10);

    return response()->json([
      'category' => $category,
      'products' => new ProductCollection($products),
    ]);
  }



  public function getProductBySlug(Request $request, $productSlug)
  {
    $product = Product::where('slug', $productSlug)->firstOrFail();
    return response()->json([
      'product' => ProductResource::make($product)->resolve(),
    ]);
  }
}
