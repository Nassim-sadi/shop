<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\SKUGenerator;
use App\Http\Resources\Admin\Product\ProductResource;
use App\Jobs\ActivityHistoryJob;
use UA;

class ProductVariantController extends Controller
{
  public function get(Request $request, $id)
  {
    $this->authorize('product_view');

    $product = Product::findOrFail($id);
    $product->load('variants')->load('variants.optionValues');

    return response()->json(['variants' => $product->variants]);
  }


  public function update(Request $request, $id)
  {
    $this->authorize('product_update');
    debugbar()->log($request->all());

    // Get product and eager load variants + option values
    $product = Product::with('variants.optionValues')->findOrFail($id);

    // Validate request
    $request->validate([
      'newVariants' => 'sometimes|array',
      'newVariants.*.price' => 'required|numeric|min:0',
      'newVariants.*.quantity' => 'required|integer|min:0',
      'newVariants.*.status' => 'required|boolean',
      'newVariants.*.options' => 'required|array',
      'updatedVariants' => 'sometimes|array',
      'updatedVariants.*.id' => 'required|integer|exists:product_variants,id',
      'updatedVariants.*.price' => 'required|numeric|min:0',
      'updatedVariants.*.quantity' => 'required|integer|min:0',
      'updatedVariants.*.status' => 'required|boolean',
      'updatedVariants.*.options' => 'required|array',
      'deletedVariants' => 'sometimes|array',
      'deletedVariants.*' => 'required|integer|exists:product_variants,id',
    ]);

    $createdVariants = [];
    $updatedVariants = [];
    $deletedCount = 0;

    // 🗑️ Delete variants
    if (!empty($request->deletedVariants)) {
      $variantsToDelete = $product->variants->whereIn('id', $request->deletedVariants);

      foreach ($variantsToDelete as $variant) {
        $variant->optionValues()->detach();
        $variant->delete();
        $deletedCount++;
      }
    }

    // ➕ Create new variants
    if (!empty($request->newVariants)) {
      $processedVariants = collect($request->newVariants)->map(function ($variantData) use ($product) {
        return [
          'product_id' => $product->id,
          'price' => $variantData['price'],
          'quantity' => $variantData['quantity'],
          'status' => $variantData['status'],
        ];
      });

      $createdVariants = $product->variants()->createMany($processedVariants->toArray());

      foreach ($createdVariants as $index => $variant) {
        $optionValueIds = array_values($request->newVariants[$index]['options'] ?? []);
        if (!empty($optionValueIds)) {
          $variant->optionValues()->sync($optionValueIds);
        }
      }
    }

    // ✏️ Update variants
    if (!empty($request->updatedVariants)) {
      foreach ($request->updatedVariants as $variantData) {
        $variant = $product->variants->firstWhere('id', $variantData['id']);
        if (!$variant) continue;

        $variant->update([
          'price' => $variantData['price'],
          'quantity' => $variantData['quantity'],
          'status' => $variantData['status'],
        ]);

        $optionValueIds = array_values($variantData['options'] ?? []);
        if (!empty($optionValueIds)) {
          $variant->optionValues()->sync($optionValueIds);
        }

        $updatedVariants[] = $variant;
      }
    }

    // 📊 Recalculate quantity & count from memory
    $product->load('variants.optionValues'); // refresh after changes

    $baseQuantity = $product->variants->sum('quantity');


    $product->update(['base_quantity' => $baseQuantity]);
    $product = $product->withCount('variants')->first();

    // 🧠 Log activity
    $agent = UA::parse($request->server('HTTP_USER_AGENT'));
    ActivityHistoryJob::dispatch(
      data: [
        'model' => 'products',
        'action' => 'update',
        'data' => ['product' => $product],
        'user_id' => $request->user()->id,
      ],
      platform: $agent->os->family,
      browser: $agent->ua->family,
    );

    // ✅ Response
    return response()->json([
      'message' => 'Variants updated successfully',
      'created_count' => count($createdVariants),
      'updated_count' => count($updatedVariants),
      'deleted_count' => $deletedCount,
      'base_quantity' => $baseQuantity,
      'product' => new ProductResource($product),
      'variants' => $product->variants->map(function ($variant) {
        return [
          'id' => $variant->id,
          'price' => $variant->price,
          'quantity' => $variant->quantity,
          'status' => $variant->status,
          'option_values' => $variant->optionValues,
        ];
      }),
    ]);
  }
}
