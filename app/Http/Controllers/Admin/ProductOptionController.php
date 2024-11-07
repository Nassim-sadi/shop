<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductOption\ProductOptionCollection;
use App\Models\ProductOption;
use Illuminate\Http\Request;

class ProductOptionController extends Controller
{

    public function get(Request $request)
    {
        $this->authorize('user_view');
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
}
