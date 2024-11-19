<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Product\ProductCollection;
use App\Models\Product;
use App\Models\ProductVariant;
use DB;
use Illuminate\Http\Request;
use Str;
use App\Helpers\ImageUpload;
use App\Http\Resources\Admin\Product\ProductResource;

class ProductController extends Controller
{
    public function get(Request $request)
    {
        $this->authorize('product_view');
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
        debugbar()->log($request->all());
        $this->authorize('product_create');
        $request->validate([
            'name' => 'required',
            'description' => 'required|string|min:10|max:255',
            'long_description' => 'required|string|min:10|max:2000',
            'base_price' => 'required|numeric',
            'listing_price' => 'required|numeric',
            'base_quantity' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'thumbnail_image_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
            'images' => 'required|array|min:1',
        ]);

        // Start a transaction to ensure data integrity
        DB::beginTransaction();
        try {
            // Create the main product
            $slug = Str::slug($request->name);
            $product = Product::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'long_description' => $request->long_description,
                'base_price' => $request->base_price,
                'base_quantity' => $request->base_quantity,
                'listing_price' => $request->listing_price,
                'status' => $request->status,
                'category_id' => $request->category_id
            ]);

            // Upload the thumbnail image

            $product->thumbnail_image_path = ImageUpload::uploadImage($request->file('thumbnail_image_path'), 'products/' . $slug);
            $product->save();


            foreach ($request->file('images') as $image) {
                $imagePath = ImageUpload::uploadImage($image, 'products/' . $product->slug);
                $product->images()->create([
                    'url' => "/storage/images/products/{$product->slug}/$imagePath",
                    'alt_text' => $image->getClientOriginalName() ?? '',
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Product created successfully', 'product' =>  new ProductResource($product)]);
        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollback();
            return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }
}
