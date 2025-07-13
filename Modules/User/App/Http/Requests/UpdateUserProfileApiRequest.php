<?php

namespace Modules\User\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = Auth::id();
        
        return [
            'username' => [
                'sometimes',
                'string',
                Rule::unique('users', 'username')->ignore($userId)
            ],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'bio' => 'sometimes|string|max:500',
            'profile_picture' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:100',
            'website' => 'sometimes|url|max:255',
            'reading_preferences' => 'sometimes|array',
            'reading_preferences.*' => 'string|max:50',
        ];
    }
}