<?php

namespace App\Http\Resources\API\V1;


use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'memberId' => $this->member_id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'dateOfBirth' => $this->date_of_birth,
            'approvedAt' => $this->approved_at,
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}