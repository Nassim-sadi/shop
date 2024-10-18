<?php

namespace App\Http\Resources\Admin\ActivityHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityHistoryResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'action' => $this->action,
            'data' => $this->data,
            'platform' => $this->platform,
            'browser' => $this->browser,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->user),
        ];
    }
}
