<?php

namespace App\Http\Resources\Roles;

use App\Http\Resources\Roles\RolesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RolesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return ['roles' => RolesResource::collection($this->collection)];
    }

    // public function paginationInformation($request, $paginated, $default): array
    // {
    //     return [
    //         'current_page' => $paginated['current_page'],
    //         'per_page' => $paginated['per_page'],
    //         'total' => $paginated['total'],
    //         'total_pages' => $paginated['last_page'],
    //     ];
    // }
}
