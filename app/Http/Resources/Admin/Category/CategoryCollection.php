<?php

namespace App\Http\Resources\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return CategoryResource::collection($this->collection);
    }

    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'current_page' => $paginated['current_page'],
            'per_page' => $paginated['per_page'],
            'total' => $paginated['total'],
            'total_pages' => $paginated['last_page'],
        ];
    }
}
