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
  public function getFeatured()
  {
    $products = Product::where('status', true)
      ->where('featured', true)
      ->with(['category', 'images'])
      ->orderBy('created_at', 'DESC')
      ->limit(8)
      ->get();

    return new ProductCollection($products);
  }

  public function getSimilar(Request $request, $categoryId)
  {
    $products = Product::where('status', 'active')
      ->where('category_id', $categoryId)
      ->with(['category', 'images'])
      ->orderBy('created_at', 'DESC')
      ->limit(4)
      ->get();

    return new ProductCollection($products);
  }

  public function search(Request $request)
  {
    $query = Product::where('status', 'active')
      ->with(['category', 'images']);

    if ($request->has('q') && $request->q) {
      $query->where('name', 'LIKE', '%' . $request->q . '%')
        ->orWhere('description', 'LIKE', '%' . $request->q . '%');
    }

    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'DESC');
    $query->orderBy($sortBy, $sortOrder);

    $perPage = $request->get('per_page', 20);
    $products = $query->paginate($perPage);

    return new ProductCollection($products);
  }
}
