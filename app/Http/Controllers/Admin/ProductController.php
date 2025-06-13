<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Product\ProductCollection;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;
use Str;
use App\Helpers\ImageUpload;
use App\Http\Resources\Admin\Product\ProductResource;
use App\Jobs\ActivityHistoryJob;
use Storage;
use UA;

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
            ->with('category')
            ->with('options')
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

        if ($request->has('options')) {
            $request->merge([
                'options' => json_decode($request->input('options'), true)
            ]);
        }

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
            'options' => 'nullable|array',
            'options.*' => 'exists:product_options,id',
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
                'featured' => $request->featured,
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

            if ($request->filled('options')) {
                $product->options()->sync($request->input('options'));
            }

            DB::commit();

            $agent = UA::parse($request->server('HTTP_USER_AGENT'));
            ActivityHistoryJob::dispatch(
                data: [
                    'model' => 'products',
                    'action' => 'create',
                    'data' => ['product' =>  $product],
                    'user_id' => $request->user()->id,
                ],
                platform: $agent->os->family,
                browser: $agent->ua->family,
            );
            return response()->json(['message' => 'Product created successfully', 'product' =>  new ProductResource($product)]);
        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollback();
            return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        debugbar()->log($request->all());
        $this->authorize('product_update');

        $request->validate([
            'id' => 'required|exists:products,id',
            'name' => 'required',
            'description' => 'required|string|min:10|max:255',
            'long_description' => 'required|string|min:10|max:2000',
            'base_price' => 'required|numeric',
            'listing_price' => 'required|numeric',
            'base_quantity' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'thumbnail_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => 'required|boolean',
            'featured' => 'required|boolean',
            'images' => 'required|array|min:1',
        ]);

        $product = Product::findOrFail($request->id);

        // Update product details
        $slug = Str::slug($request->name);
        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'long_description' => $request->long_description,
            'base_price' => $request->base_price,
            'base_quantity' => $request->base_quantity,
            'listing_price' => $request->listing_price,
            'status' => $request->status,
            'featured' => $request->featured,
            'category_id' => $request->category_id,
        ]);

        // Handle thumbnail image update
        if ($request->hasFile('thumbnail_image_path')) {
            $product->thumbnail_image_path = ImageUpload::uploadImage(
                $request->file('thumbnail_image_path'),
                'products/' . $slug
            );
            $product->save();
        }
        $existingImageIds = collect($request->images)
            ->filter(fn($image) => isset($image['id'])) // Keep only objects with 'id'
            ->pluck('id')
            ->toArray();

        $product->images()
            ->whereNotIn('id', $existingImageIds)
            ->get()
            ->each(function ($image) {
                $imagePath = public_path($image->url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $image->delete();
            });

        // Process new files and update objects
        foreach ($request->images as $index => $imageData) {
            // Handle new files
            if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = ImageUpload::uploadImage($imageData['file'], 'products/' . $product->slug);
                $product->images()->create([
                    'url' => "/storage/images/products/{$product->slug}/$imagePath",
                    'alt_text' => $imageData['file']->getClientOriginalName() ?? '',
                ]);
            } elseif (isset($imageData['id'])) {
                // Optionally, handle updates for existing images (e.g., alt_text or other metadata)
                $existingImage = $product->images()->find($imageData['id']);
                if ($existingImage && isset($imageData['alt_text'])) {
                    $existingImage->update([
                        'alt_text' => $imageData['alt_text'],
                    ]);
                }
            }
        }

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

        return response()->json(['message' => 'Product updated successfully.', 'product' => new ProductResource($product)]);
    }


    public function getImages(Request $request, $id)
    {
        $this->authorize('product_view');
        $product = Product::findOrFail($id);
        $product->load('images');
        return response()->json(['id' => $product->id, 'images' => $product->images]);
    }

    public function changeStatus(Request $request)
    {
        $this->authorize('product_update');
        $product = Product::findOrFail($request->id);
        $product->status = $request->status;
        $product->save();

        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'products',
                'action' => 'change_status',
                'data' => ['product' =>  $product],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['message' => 'Product status updated successfully.', 'status' => $product->status]);
    }

    public function delete(Request $request, $id)
    {

        // TODO : check if associated variant count is 0
        $this->authorize('product_delete');
        $product = Product::findOrFail($id);

        $product->delete();

        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'products',
                'action' => 'delete',
                'data' => ['product' =>  $product],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function getVariants(Request $request, $id)
    {

        debugbar()->log($request->all());
        debugbar()->log('id is ' .  $id);

        $this->authorize('product_view');
        $product = Product::findOrFail($id);
        $product->load('variants');

        return response()->json(['variants' => $product->variants]);
    }
}
