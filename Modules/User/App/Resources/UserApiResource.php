<?php

namespace Modules\User\App\Resources;

use Modules\User\App\Resources\UserProfileApiResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'memberId' => $this->member_id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'dateOfBirth' => $this->date_of_birth,
            'approvalStatus' => $this->approval_status,
            'approvedAt' => $this->approved_at,
            'profile' => new UserProfileApiResource($this->whenLoaded('profile')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
