<?php

namespace Modules\User\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'sometimes|string|max:255|unique:users,username,' ,
            'email' => 'sometimes|email|unique:users,email,',
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:admin,moderator,member',
            'date_of_birth' => 'nullable|date',
            'approval_status' => 'sometimes|string|in:pending,approved,rejected',
        ];
    }
}
