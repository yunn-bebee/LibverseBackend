<?php

namespace Modules\Forum\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'content' => 'required|string',
            'parent_post_id' => ['nullable', Rule::exists('posts', 'id')],
            'book_id' => ['nullable', Rule::exists('books', 'id')],
        ];
    }
}
