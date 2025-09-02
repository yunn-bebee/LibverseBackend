<?php
namespace Modules\Forum\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ThreadRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
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
