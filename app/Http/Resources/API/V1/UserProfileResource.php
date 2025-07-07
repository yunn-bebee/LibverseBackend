<?php
// app/Http/Resources/Api/V1/UserProfileResource.php
namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request)
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