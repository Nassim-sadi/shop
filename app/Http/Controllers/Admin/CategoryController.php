<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Category\CategoryCollection;
use App\Http\Resources\Admin\Category\CategoryResource;
use App\Jobs\ActivityHistoryJob;
use App\Models\Category;
use Illuminate\Http\Request;
use Str;
use UA;

class CategoryController extends Controller
{
    public function getCategories(Request $request)
    {
        $this->authorize('category_view');
        // $categories = Category::with('children')->whereNull('parent_id')->whereDate('created_at', '>=', $request->start_date)
        //     ->whereDate('created_at', '<=', $request->end_date)
        //     ->where(function ($q) use ($request) {
        //         $q->where('name', 'Like', '%' . $request->keyword . '%')
        //             ->orWhere('slug', 'Like', '%' . $request->keyword . '%');
        //     })
        //     ->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
        //         $q->where('status', $request->status);
        //     })
        //     ->orderBy('order', 'ASC')
        //     ->paginate($request->per_page);
        // return new CategoryCollection($categories);

        $categories = Category::with('children')->whereNull('parent_id')->get();
        return  CategoryResource::collection($categories);
    }

    public function create(Request $request)
    {
        $this->authorize('category_create');
        debugbar()->log($request->all());
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slug = Str::slug($validatedData['name']);

        if (Category::where('slug', $slug)->exists()) {
            return response()->json(['error' => 'The generated slug is already in use. Please choose a different name.'], 432);
        }


        // $nextOrder = array_key_exists('parent_id', $validatedData) ? Category::where('parent_id', $validatedData['parent_id'])->max('order') + 1 : Category::whereNull('parent_id')->max('order') + 1;

        $nextOrder = 1;
        $validatedData['slug'] = $slug;

        // Check if we're creating a child category
        if (array_key_exists('parent_id', $validatedData)) {
            // Fetch parent category only once
            $parentCategory = Category::withMax('children', 'order')->findOrFail($validatedData['parent_id']);

            // Determine the order based on the max order of the parent's children
            $nextOrder = ($parentCategory->children_max_order ?? 0) + 1;

            // Create the child category
            $category = $parentCategory->children()->create([
                'image' =>  $this->uploadImage($validatedData['image']),
                'name' => $validatedData['name'],
                'slug' => $validatedData['slug'],
                'description' => $validatedData['description'],
                'status' => $validatedData['status'],
                'order' => $nextOrder,
            ]);
        } else {
            // Determine order for a top-level category
            $nextOrder = Category::whereNull('parent_id')->max('order') + 1;

            // Create a top-level category
            $category = Category::create([
                'image' => $this->uploadImage($validatedData['image']),
                'name' => $validatedData['name'],
                'slug' => $validatedData['slug'],
                'description' => $validatedData['description'],
                'status' => $validatedData['status'],
                'order' => $nextOrder,
            ]);
        }

        // $validatedData['order'] = $nextOrder;

        // $image = $request->file('image');
        // $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        // $extension = $image->extension();
        // $imageName = Str::slug($originalName) . '-' . time() . '.' . $extension;
        // $destinationPath = public_path('/storage/images/categories');
        // $image->move($destinationPath, $imageName);
        // $validatedData['image'] = $imageName;

        // $category = Category::create($validatedData);

        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'categories',
                'action' => 'create',
                'data' => ['category' =>  $category],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['success' => 'Category created successfully', 'category' => CategoryResource::make($category)], 200);
    }

    private function uploadImage($image)
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->extension();
        $imageName = Str::slug($originalName) . '-' . time() . '.' . $extension;
        $destinationPath = public_path('/storage/images/categories');
        $image->move($destinationPath, $imageName);
        return $imageName;
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('category_edit');
        $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'order' => 'required',
            'status' => 'required',

        ]);
        $category->update($request->all());
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'categories',
                'action' => 'delete',
                'data' => ['category' =>  $category],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json($category);
    }

    public function changeStatus(Request $request)
    {
        $this->authorize('category_edit');
        $request->validate([
            'status' => 'required|in:0,1',
            'id' => 'required|exists:categories',
        ]);
        $category = Category::findOrFail($request->id);
        $category->status = $request->status;
        $category->save();
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'categories',
                'action' => 'delete',
                'data' => ['category' =>  $category],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json($category);
    }

    public function delete(Request $request, $id)
    {
        $this->authorize('category_delete');

        $category = Category::findOrFail($id);
        if ($category->children->count() > 0) {
            return response()->json(['error' => 'Category has children'], 422);
        }

        if ($category->products->count() > 0) {
            return response()->json(['error' => 'Category has products'], 422);
        }

        $category->delete();
        if ($category->image) {
            $oldImage = basename(parse_url($category->image, PHP_URL_PATH));
            $oldImagePath = public_path('/storage/images/categories/' . $oldImage);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'categories',
                'action' => 'delete',
                'data' => ['category' =>  $category],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['success' => 'Category deleted successfully'], 200);
    }
}
