<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
  public function get(Request $request, $id)
  {
    $this->authorize('product_view');

    $product = Product::findOrFail($id);
    $product->load('variants');

    return response()->json(['variants' => $product->variants]);
  }
}
