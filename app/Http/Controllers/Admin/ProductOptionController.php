<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductOption\ProductOptionCollection;
use App\Http\Resources\Admin\ProductOption\ProductOptionResource;
use App\Jobs\ActivityHistoryJob;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use UA;

class ProductOptionController extends Controller
{

    public function get(Request $request)
    {
        $this->authorize('productOption_view');
        $productOptions = ProductOption::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($request->keyword) . '%'])
            ->when(isset($request->status) && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->with(['values' => function ($query) {
                $query->withCount('variants');
            }])
            ->withCount('products')
            ->orderBy('created_at', 'DESC')
            ->paginate($request->per_page);
        return new ProductOptionCollection($productOptions);
    }


    public function getAll(Request $request)
    {
        $this->authorize('productOption_view');
        $productOptions = ProductOption::with('values')->orderBy('created_at', 'DESC')->get();
        return new ProductOptionCollection($productOptions);
    }

    public function create(Request $request)
    {

        $this->authorize('productOption_create');
        debugbar()->log($request->all());
        $request->validate([
            'name' => 'required|unique:product_options,name',
            'status' => 'required|boolean',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string',
        ]);

        $uniqueValues = array_unique($request->values);
        if (count($request->values) !== count($uniqueValues)) {
            return response()->json(['message' => 'All values must be unique'], 400);
        }
        $productOption = ProductOption::create([
            'name' => $request->name,
            'status' => $request->status
        ]);

        foreach ($request->values as $value) {
            $productOption->values()->create([
                'value' => $value,
            ]);
        }

        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'product_options',
                'action' => 'create',
                'data' => ['product-option' =>  $productOption],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );


        return response()->json(ProductOptionResource::make($productOption), 200);
    }

    public function changeStatus(Request $request)
    {
        $this->authorize('productOption_update');
        $productOption = ProductOption::findOrFail($request->id);
        $productOption->status = $request->status;
        $productOption->save();
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'product_options',
                'action' => 'update',
                'data' => ['product-option' =>  $productOption],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        return response()->json(['status' => $productOption->status, 'message' => 'Product option status changed successfully'], 200);
    }

    public function update(Request $request)
    {
        $this->authorize('productOption_update');

        // Validate the request
        $request->validate([
            'id' => 'required|exists:product_options,id',
            'name' => 'required|unique:product_options,name,' . $request->id,
            'status' => 'required|boolean',
            'values' => 'required|array|min:1',
            'values.*.id' => 'nullable|exists:product_option_values,id',
            'values.*.value' => 'required|string',
        ]);

        $uniqueValues = array_unique(array_column($request->values, 'value'));
        if (count($request->values) !== count($uniqueValues)) {
            return response()->json(['message' => 'All values must be unique'], 400);
        }

        // Find the product option
        $productOption = ProductOption::findOrFail($request->id);
        $productOption->name = $request->name;
        $productOption->status = $request->status;
        $productOption->save();

        // fetch count 

        // Retrieve existing values as a collection for easy comparison
        $existingValues = $productOption->values()->get()->keyBy('id');

        // Prepare to collect the new and updated values
        $incomingValues = collect($request->values);

        // Delete values that are not in the incoming request
        $existingValues->whereNotIn('id', $incomingValues->pluck('id')->filter())->each->delete();

        // Process incoming values
        foreach ($incomingValues as $valueData) {
            if (isset($valueData['id']) && $existingValues->has($valueData['id'])) {
                // Update the existing value
                $existingValues[$valueData['id']]->update(['value' => $valueData['value']]);
            } else {
                // Create a new value
                $productOption->values()->create(['value' => $valueData['value']]);
            }
        }
        $productOption->loadCount('products')->load('values');
        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'product_options',
                'action' => 'update',
                'data' => ['product-option' =>  $productOption],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        // Return the updated product option resource
        return response()->json(ProductOptionResource::make($productOption), 200);
    }

    public function delete(Request $request, $id)
    {
        $this->authorize('productOption_delete');
        $productOption = ProductOption::findOrFail($id);
        // check if associated variant count is 0
        if ($productOption->products()->count() != 0) {
            return response()->json(['message' => 'Product option is associated with variants'], 400);
        }

        $agent = UA::parse($request->server('HTTP_USER_AGENT'));
        ActivityHistoryJob::dispatch(
            data: [
                'model' => 'product_options',
                'action' => 'delete',
                'data' => ['product-option' =>  $productOption],
                'user_id' => $request->user()->id,
            ],
            platform: $agent->os->family,
            browser: $agent->ua->family,
        );
        $productOption->delete();

        return response()->json(['message' => 'Product option deleted successfully'], 200);
    }
}
