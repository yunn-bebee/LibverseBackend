<?php
namespace Modules\Forum\App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ForumRequest extends FormRequest
{
   

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'is_public' => 'boolean',
            'book_id' => 'nullable|exists:books,id',
        ];
    }
}
