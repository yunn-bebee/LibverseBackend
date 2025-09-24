<?php

namespace Modules\User\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserProfileApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
                return [
            'bio' => $this->bio,

            'website' => $this->website,
            'location' => $this->location,
            'profilePicture' => $this->profile_picture
                ? url('storage/' . $this->profile_picture)
                : "",
            'lastActive' => $this->last_active,
        ];
    }
}
