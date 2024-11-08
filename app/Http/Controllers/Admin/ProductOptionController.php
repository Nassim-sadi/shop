<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductOption\ProductOptionCollection;
use App\Http\Resources\Admin\ProductOption\ProductOptionResource;
use App\Models\ProductOption;
use Illuminate\Http\Request;

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

        $request->validate([
            'name' => 'required|unique:product_options,name',
            'values' => 'required|array|min:1',
        ]);

        // validate values array to be each value unique
        $values = $request->values;
        $uniqueValues = array_unique($values);
        if (count($values) !== count($uniqueValues)) {
            return response()->json(['message' => 'All values must be unique'], 400);
        }

        $productOption = ProductOption::create([
            'name' => $request->name,
        ]);

        foreach ($request->values as $value) {
            $productOption->values()->create([
                'value' => $value,
            ]);
        }
        return response()->json(ProductOptionResource::make($productOption), 200);
    }
}
