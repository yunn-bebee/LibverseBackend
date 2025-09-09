<?php

namespace Modules\Badge\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BadgeApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
              'icon_url' => $this->icon_url,
            'description' => $this->description,
            'type' => $this->type,
            'awarded_at' => $this->when(isset($this->pivot), function () {
                return $this->pivot->created_at;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
