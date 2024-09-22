<?php

namespace App\Http\Resources\ActivityHistory;

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
            'role' => [
                'id' => $this->roles[0]->id,
                'name' => $this->roles[0]->name,
                'description' => $this->roles[0]->description,
                'color' => $this->roles[0]->color,
                'text_color' => $this->roles[0]->text_color
            ],
        ];
    }
}
