<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Product\ProductCollection;
use App\Models\Product;
use App\Models\ProductVariant;
use DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function get(Request $request)
    {
        $this->authorize('user_view');
        $products = Product::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->where(function ($q) use ($request) {
                $q->where('name', 'Like', '%' . $request->keyword . '%');
            })
            // ->when(isset($request->role) && $request->role !== '', function ($query) use ($request) {
            //     $query->whereHas('roles', function ($q) use ($request) {
            //         $q->where('id', $request->role);
            //     });
            // })
            ->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('created_at', 'DESC')
            // ->when($user->hasRole("Super Admin"), function ($q) use ($request) {
            //     $q->when(isset($request->deleted) && $request->deleted !== '', function ($q) use ($request) {
            //         if ($request->deleted === 'with') {
            //             return $q->withTrashed();
            //         } elseif ($request->deleted === 'only') {
            //             return $q->onlyTrashed();
            //         }
            //     });
            // })
            ->paginate($request->per_page);

        return new ProductCollection($products);
    }

    public function create(Request $request)
    {
        // Start a transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Create the main product
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'base_quantity' => $request->base_quantity,
            ]);

            // Attach images to the product
            foreach ($request->product_images as $image) {
                $product->images()->create([
                    'url' => $image['url'],
                    'alt_text' => $image['alt_text'] ?? '',
                ]);
            }

            // Process each variation
            foreach ($request->variations as $variation) {
                // Create the variant with its specific SKU, price, quantity, etc.
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variation['sku'],
                    'price' => $variation['price'],
                    'quantity' => $variation['quantity'],
                    'status' => $variation['status'] ?? 'active',
                ]);

                // Attach images to the variant (optional)
                if (isset($variation['images'])) {
                    foreach ($variation['images'] as $variantImage) {
                        $variant->images()->create([
                            'url' => $variantImage['url'],
                            'alt_text' => $variantImage['alt_text'] ?? '',
                        ]);
                    }
                }

                // Attach global option values (e.g., size, color) to the variant
                foreach ($variation['option_values'] as $valueId) {
                    $variant->optionValues()->attach($valueId);
                }
            }

            // Commit transaction if everything went well
            DB::commit();

            return response()->json(['message' => 'Product and variants with options and images created successfully']);
        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollback();
            return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }
}
