<?php

namespace Modules\Post\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostActionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'action' => 'required|in:like,unlike,save,unsave',
        ];
    }

    public function authorize(): bool
    {
        return auth()->check();
    }
}
