<?php

namespace App\Http\Controllers;

use App\Http\Resources\Product\ProductCollection;
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
      'breadcrumbs' => $this->getBreadcrumbs($category),
    ]);
  }

  private function getBreadcrumbs($category)
  {
    $breadcrumbs = collect();
    $current = $category;

    while ($current) {
      $breadcrumbs->prepend([
        'name' => $current->name,
        'slug' => $current->slug,
        'url' => '/' . $current->slug
      ]);
      $current = $current->parent;
    }

    // Add products breadcrumb
    $breadcrumbs->push([
      'name' => 'Products',
      'slug' => 'products',
      'url' => '/' . $category->slug . '/products'
    ]);

    return $breadcrumbs;
  }
}
