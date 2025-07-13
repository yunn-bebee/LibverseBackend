<?php

namespace Modules\Mention\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MentionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Validation rules
        ];
    }
}