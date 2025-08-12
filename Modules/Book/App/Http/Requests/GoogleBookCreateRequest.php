<?php

namespace Modules\Book\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleBookCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'google_books_id' => 'required|string',
          
        ];
    }
}
