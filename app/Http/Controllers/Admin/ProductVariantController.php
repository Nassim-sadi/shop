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

    // Validate the product exists
    $product = Product::findOrFail($id);

    // Validate the request data
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

    // Handle deleted variants first
    if ($request->has('deletedVariants') && !empty($request->deletedVariants)) {
      $deletedIds = $request->deletedVariants;

      // Make sure the variants belong to this product
      $variantsToDelete = $product->variants()->whereIn('id', $deletedIds)->get();

      foreach ($variantsToDelete as $variant) {
        // Delete associated option values first
        $variant->optionValues()->detach();
        // Delete the variant
        $variant->delete();
        $deletedCount++;
      }
    }

    // Handle new variants creation
    if ($request->has('newVariants') && !empty($request->newVariants)) {
      $processedVariants = [];
      foreach ($request->newVariants as $variantData) {
        $processedVariant = [
          'product_id' => $product->id,
          'price' => $variantData['price'],
          'quantity' => $variantData['quantity'],
          'status' => $variantData['status'],
        ];
        $processedVariants[] = $processedVariant;
      }

      // Create new variants
      $createdVariants = $product->variants()->createMany($processedVariants);

      // Handle option values for each new variant
      foreach ($createdVariants as $index => $variant) {
        $optionValues = $request->newVariants[$index]['options'] ?? [];
        if (!empty($optionValues)) {
          $optionValueIds = array_values($optionValues);
          $variant->optionValues()->sync($optionValueIds);
        }
      }
    }

    // Handle existing variants updates
    if ($request->has('updatedVariants') && !empty($request->updatedVariants)) {
      foreach ($request->updatedVariants as $variantData) {
        // Find the variant that belongs to this product
        $variant = $product->variants()->findOrFail($variantData['id']);

        // Update variant data
        $variant->update([
          'price' => $variantData['price'],
          'quantity' => $variantData['quantity'],
          'status' => $variantData['status'],
        ]);

        // Update option values
        $optionValues = $variantData['options'] ?? [];
        if (!empty($optionValues)) {
          $optionValueIds = array_values($optionValues);
          $variant->optionValues()->sync($optionValueIds);
        }
        $updatedVariants[] = $variant;
      }
    }

    // Calculate and update base_quantity
    $baseQuantity = $product->variants()->sum('quantity');
    $product->update(['base_quantity' => $baseQuantity]);

    //get product details
    $variantCount = $product->variants()->count();

    // log activity 
    $agent = UA::parse($request->server('HTTP_USER_AGENT'));
    ActivityHistoryJob::dispatch(
      data: [
        'model' => 'products',
        'action' => 'update',
        'data' => ['product' =>  $product],
        'user_id' => $request->user()->id,
      ],
      platform: $agent->os->family,
      browser: $agent->ua->family,
    );

    // Return response with created, updated, and deleted counts
    return response()->json([
      'message' => 'Variants updated successfully',
      'created_count' => count($createdVariants),
      'updated_count' => count($updatedVariants),
      'deleted_count' => $deletedCount,
      'base_quantity' => $baseQuantity,
      'product' => new ProductResource($product),
      'variants' => $product->variants()->with('optionValues')->get([
        'id',
        'price',
        'quantity',
        'status',
      ])
    ]);
  }
}
