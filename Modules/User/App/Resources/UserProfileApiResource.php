<?php

namespace Modules\User\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'profilePicture' => $this->profile_picture,
            'website' => $this->website,
            'location' => $this->location,
            'readingPreferences' => $this->reading_preferences,
            'lastActive' => $this->last_active,
        ];
    }
}
