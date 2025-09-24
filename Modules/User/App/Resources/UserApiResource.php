<?php

namespace Modules\User\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\App\Resources\UserProfileApiResource;

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
            'forum_status' => $this->whenPivotLoaded('forum_user', fn() => [
                'status' => $this->pivot->status,
                'approved_at' => $this->pivot->approved_at ? $this->pivot->approved_at->toDateTimeString() : null,
            ]),
           'is_followed' => Auth::check() ? $this->followers()->where('follower_id', Auth::id())->exists() : false,
        ];
    }
}
