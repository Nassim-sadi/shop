<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Category\CategoryCollection;
use App\Jobs\ActivityHistoryJob;
use App\Models\Category;
use Illuminate\Http\Request;
use UA;

class CategoryController extends Controller
{
    public function getCategories(Request $request)
    {
        $this->authorize('category_view');
        $categories = Category::with('children')->whereNull('parent_id')->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->where(function ($q) use ($request) {
                $q->where('name', 'Like', '%' . $request->keyword . '%')
                    ->orWhere('slug', 'Like', '%' . $request->keyword . '%');
            })
            ->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('order', 'ASC')
            ->paginate($request->per_page);
        return new CategoryCollection($categories);
    }

    public function create(Request $request)
    {
        $this->authorize('category_create');
        $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'order' => 'required',
            'status' => 'required',
            'parent_id' => 'sometimes|nullable|exists:categories,id',
        ]);
        $category = Category::create($request->all());

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
        return response()->json($category);
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
