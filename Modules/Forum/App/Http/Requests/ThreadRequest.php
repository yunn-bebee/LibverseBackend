<?php
namespace Modules\Forum\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThreadRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'post_type' => 'sometimes|string|in:discussion,text,image,video',
            'book_id' => 'nullable|exists:books,id',
        ];
    }
}
