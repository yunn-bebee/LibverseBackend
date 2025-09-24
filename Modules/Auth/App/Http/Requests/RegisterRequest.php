<?php

namespace Modules\Auth\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'member_id' => [
                'required',
                'string',
                'unique:users',
                'max:20',
                'regex:/^AYA\w{6}$/'
            ],
            'username' => 'required|string|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before:-13 years',
        ];
    }

    public function messages()
    {
        return [
            'member_id.regex' => 'Member ID must be in AYA-XXXXXXX format',
            'date_of_birth.before' => 'You must be at least 13 years old'
        ];
    }
}
