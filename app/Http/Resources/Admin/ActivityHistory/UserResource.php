<?php

namespace App\Http\Resources\Admin\ActivityHistory;

use App\Http\Resources\Admin\Users\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = 'user';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'status' => $this->status,
            'image' => $this->image,
            'role' => RoleResource::make($this->roles[0]),
        ];
    }
}
