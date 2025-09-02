<?php

namespace Modules\Post\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'parent_post_id' => 'nullable|exists:posts,id',
            'book_id' => 'nullable|exists:books,id',
        ];
    }

    public function authorize(): bool
    {
        return Auth::check();
    }
}
