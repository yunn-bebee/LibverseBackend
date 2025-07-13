<?php

namespace Modules\Auth\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required_without:member_id|email|nullable',
            'member_id' => 'required_without:email|string|nullable',
            'password' => 'required',
            'remember_me' => 'sometimes|boolean'
        ];
    }
}