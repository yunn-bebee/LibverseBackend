<?php

namespace Modules\Badge\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BadgeApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // Add other resource fields
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}